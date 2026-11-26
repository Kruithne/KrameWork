<?php
	use KrameWork\Database\ConnectionString;
	use KrameWork\Database\Database;
	use KrameWork\Database\Schema\SQLiteMetaTable;
	use KrameWork\Security\Password;
	use KrameWork\Users\IUserMailer;
	use KrameWork\Users\OAuth\OAuthManager;
	use KrameWork\Users\OAuth\OAuthProvider;
	use KrameWork\Users\OAuth\OAuthResult;
	use KrameWork\Users\OAuth\OAuthStatus;
	use KrameWork\Users\Schema\OAuthAccountsTable;
	use KrameWork\Users\Schema\OAuthStatesTable;
	use KrameWork\Users\Schema\DatabaseType;
	use KrameWork\Users\Schema\UserAccountsTable;
	use KrameWork\Users\Schema\UserVerificationCodesTable;
	use KrameWork\Users\UserAccountFlags;
	use KrameWork\Users\UserManager;

	require_once(__DIR__ . '/../src/Database/Database.php');
	require_once(__DIR__ . '/../src/Database/Schema/SQLiteMetaTable.php');
	require_once(__DIR__ . '/../src/Users/UserManager.php');
	require_once(__DIR__ . '/../src/Users/OAuth/OAuthManager.php');
	require_once(__DIR__ . '/../src/Users/Schema/OAuthAccountsTable.php');
	require_once(__DIR__ . '/../src/Users/Schema/OAuthStatesTable.php');
	require_once(__DIR__ . '/../src/Users/Schema/UserAccountsTable.php');
	require_once(__DIR__ . '/../src/Users/Schema/UserVerificationCodesTable.php');

	class OAuthManagerTest extends PHPUnit\Framework\TestCase
	{
		/**
		 * Test that a new identity creates an account, and that the next login with that identity uses the same account.
		 */
		public function testNewIdentityCreatesAccount() {
			$oauth = $this->createManager();

			$first = $this->login($oauth, 'google', $this->createClaims());
			$this->assertSame(OAuthStatus::SUCCESS, $first->status);
			$this->assertTrue($first->registered);

			$second = $this->login($oauth, 'google', $this->createClaims());
			$this->assertSame(OAuthStatus::SUCCESS, $second->status);
			$this->assertFalse($second->registered);
			$this->assertSame($first->userID, $second->userID);
		}

		/**
		 * Test that a state token can be used one time only.
		 */
		public function testStateIsSingleUse() {
			$oauth = $this->createManager();
			$state = $this->getState($oauth, 'google');
			$oauth->tokens = (object) ['id_token' => $this->createToken($this->createClaims())];

			$this->assertSame(OAuthStatus::SUCCESS, $oauth->callback($state, 'code')->status);
			$this->assertSame(OAuthStatus::INVALID_STATE, $oauth->callback($state, 'code')->status);
		}

		/**
		 * Test that an ID token for a different client is rejected.
		 */
		public function testWrongAudienceIsRejected() {
			$oauth = $this->createManager();
			$claims = $this->createClaims(['aud' => 'other-client']);

			$this->assertSame(OAuthStatus::INVALID_ID_TOKEN, $this->login($oauth, 'google', $claims)->status);
		}

		/**
		 * Test that an expired ID token is rejected.
		 */
		public function testExpiredTokenIsRejected() {
			$oauth = $this->createManager();
			$claims = $this->createClaims(['exp' => \time() - 1]);

			$this->assertSame(OAuthStatus::INVALID_ID_TOKEN, $this->login($oauth, 'google', $claims)->status);
		}

		/**
		 * Test that a Microsoft ID token is accepted when its issuer contains the tenant ID of the token.
		 */
		public function testMicrosoftTenantIssuerIsAccepted() {
			$oauth = $this->createManager();
			$tenant = '9188040d-6c67-4c5b-b112-36a304b66dad';
			$claims = $this->createClaims(['iss' => 'https://login.microsoftonline.com/' . $tenant . '/v2.0', 'tid' => $tenant]);

			$this->assertSame(OAuthStatus::SUCCESS, $this->login($oauth, 'microsoft', $claims)->status);
		}

		/**
		 * Test that a Microsoft ID token is rejected when its issuer contains a different tenant ID than the token.
		 */
		public function testMicrosoftOtherTenantIssuerIsRejected() {
			$oauth = $this->createManager();
			$claims = $this->createClaims(['iss' => 'https://login.microsoftonline.com/other/v2.0', 'tid' => '9188040d-6c67-4c5b-b112-36a304b66dad']);

			$this->assertSame(OAuthStatus::INVALID_ID_TOKEN, $this->login($oauth, 'microsoft', $claims)->status);
		}

		/**
		 * Test that a Microsoft ID token is rejected when its tenant ID is not a GUID.
		 */
		public function testMicrosoftNonGUIDTenantIsRejected() {
			$oauth = $this->createManager();
			$claims = $this->createClaims(['iss' => 'https://login.microsoftonline.com/other/v2.0', 'tid' => 'other']);

			$this->assertSame(OAuthStatus::INVALID_ID_TOKEN, $this->login($oauth, 'microsoft', $claims)->status);
		}

		/**
		 * Test that an identity with the e-mail address of a password account does not log in to that account.
		 */
		public function testEmailOfPasswordAccountIsRejected() {
			$oauth = $this->createManager();
			$this->users->register('foo@bar.net', new Password('hunter2'));

			$this->assertSame(OAuthStatus::EMAIL_IN_USE, $this->login($oauth, 'google', $this->createClaims())->status);
		}

		/**
		 * Test that an identity with an unverified e-mail address is rejected.
		 */
		public function testUnverifiedEmailIsRejected() {
			$oauth = $this->createManager();
			$claims = $this->createClaims(['email_verified' => false]);

			$this->assertSame(OAuthStatus::EMAIL_NOT_VERIFIED, $this->login($oauth, 'google', $claims)->status);
		}

		/**
		 * Test that the e-mail address is read from the UserInfo endpoint when the ID token has no e-mail address.
		 */
		public function testEmailIsReadFromUserInfo() {
			$oauth = $this->createManager();
			$claims = $this->createClaims();
			unset($claims['email'], $claims['email_verified']);
			$oauth->userInfo = (object) ['sub' => 'subject', 'email' => 'foo@bar.net'];

			$result = $this->login($oauth, 'google', $claims);
			$this->assertSame(OAuthStatus::SUCCESS, $result->status);
			$this->assertTrue($this->users->emailInUse('foo@bar.net'));
		}

		/**
		 * Test that UserInfo claims for a different subject are not used.
		 */
		public function testUserInfoForOtherSubjectIsIgnored() {
			$oauth = $this->createManager();
			$claims = $this->createClaims();
			unset($claims['email'], $claims['email_verified']);
			$oauth->userInfo = (object) ['sub' => 'other', 'email' => 'foo@bar.net'];

			$this->assertSame(OAuthStatus::MISSING_EMAIL, $this->login($oauth, 'google', $claims)->status);
		}

		/**
		 * Test that an ID token with claims that do not match the signature is rejected.
		 */
		public function testChangedClaimsAreRejected() {
			$oauth = $this->createManager();
			$parts = \explode('.', $this->createToken($this->createClaims()));
			$parts[1] = self::encode(\json_encode($this->createClaims(['sub' => 'other'])));

			$this->assertSame(OAuthStatus::INVALID_ID_TOKEN, $this->callbackWithToken($oauth, \implode('.', $parts))->status);
		}

		/**
		 * Test that an unsigned ID token is rejected.
		 */
		public function testUnsignedTokenIsRejected() {
			$oauth = $this->createManager();
			$token = self::encode(\json_encode(['alg' => 'none'])) . '.' . self::encode(\json_encode($this->createClaims())) . '.';

			$this->assertSame(OAuthStatus::INVALID_ID_TOKEN, $this->callbackWithToken($oauth, $token)->status);
		}

		/**
		 * Test that an ID token signed with a key that is not in the JWKS is rejected.
		 */
		public function testUnknownKeyIsRejected() {
			$oauth = $this->createManager();
			$token = $this->createToken($this->createClaims(), ['alg' => 'RS256', 'kid' => 'other']);

			$this->assertSame(OAuthStatus::INVALID_ID_TOKEN, $this->callbackWithToken($oauth, $token)->status);
		}

		/**
		 * Test that a login logs in to the account that a parallel login made for the same new identity.
		 */
		public function testParallelNewIdentityLogsInToSameAccount() {
			$userID = null;
			$oauth = $this->createManager(function (Database $db) use (&$userID) {
				$db->execute('INSERT INTO user_accounts (email, password, flags, registered) VALUES (?, ?, ?, ?)', ['foo@bar.net', '', UserAccountFlags::OAUTH_ACCOUNT, \time()]);
				$userID = (int) $db->getLastInsertID();
				$db->execute('INSERT INTO oauth_accounts (provider, provider_user_id, user_id, linked) VALUES (?, ?, ?, ?)', ['google', 'subject', $userID, \time()]);
			});

			$result = $this->login($oauth, 'google', $this->createClaims());
			$this->assertSame(OAuthStatus::SUCCESS, $result->status);
			$this->assertSame($userID, $result->userID);
			$this->assertFalse($result->registered);
		}

		/**
		 * Test that a login is rejected if a parallel request takes the e-mail address after the check.
		 */
		public function testParallelEmailUseIsRejected() {
			$oauth = $this->createManager(function () {
				$this->users->register('foo@bar.net', new Password('hunter2'));
			});

			$this->assertSame(OAuthStatus::EMAIL_IN_USE, $this->login($oauth, 'google', $this->createClaims())->status);
		}

		/**
		 * Test that the signing keys are requested one time for many logins.
		 */
		public function testKeysAreCached() {
			$oauth = $this->createManager();
			$this->login($oauth, 'google', $this->createClaims());
			$this->login($oauth, 'google', $this->createClaims());

			$this->assertSame(1, $oauth->keyRequests);
		}

		/**
		 * Start a Google login, then complete it with the given ID token.
		 * @param OAuthManager $oauth OAuth manager.
		 * @param string $token ID token.
		 * @return OAuthResult
		 */
		private function callbackWithToken(OAuthManager $oauth, string $token): OAuthResult {
			$state = $this->getState($oauth, 'google');
			$oauth->tokens = (object) ['id_token' => $token];
			return $oauth->callback($state, 'code');
		}

		/**
		 * Start a login, then complete it with an ID token that has the given claims.
		 * @param OAuthManager $oauth OAuth manager.
		 * @param string $provider Name of the provider.
		 * @param array<string, mixed> $claims Claims of the ID token.
		 * @return OAuthResult
		 */
		private function login(OAuthManager $oauth, string $provider, array $claims): OAuthResult {
			$state = $this->getState($oauth, $provider);
			$oauth->tokens = (object) ['id_token' => $this->createToken($claims), 'access_token' => 'access'];
			return $oauth->callback($state, 'code');
		}

		/**
		 * Start a login and return the state parameter of the authorization URL.
		 * @param OAuthManager $oauth OAuth manager.
		 * @param string $provider Name of the provider.
		 * @return string
		 */
		private function getState(OAuthManager $oauth, string $provider): string {
			\parse_str((string) \parse_url($oauth->getAuthorizationURL($provider), \PHP_URL_QUERY), $query);
			return $query['state'];
		}

		/**
		 * Create valid Google ID token claims, with some claims replaced.
		 * @param array<string, mixed> $replace Claims to replace.
		 * @return array<string, mixed>
		 */
		private function createClaims(array $replace = []): array {
			return \array_merge([
				'iss' => 'https://accounts.google.com',
				'aud' => 'client',
				'sub' => 'subject',
				'exp' => \time() + 3600,
				'email' => 'Foo@Bar.net',
				'email_verified' => true
			], $replace);
		}

		/**
		 * Encode claims as an ID token signed with the test key.
		 * @param array<string, mixed> $claims Claims of the token.
		 * @param array<string, mixed> $header Header of the token.
		 * @return string
		 */
		private function createToken(array $claims, array $header = ['alg' => 'RS256', 'kid' => 'test']): string {
			$input = self::encode(\json_encode($header)) . '.' . self::encode(\json_encode($claims));
			\openssl_sign($input, $signature, self::getKey(), \OPENSSL_ALGO_SHA256);
			return $input . '.' . self::encode($signature);
		}

		/**
		 * Encode a string as base64url without padding.
		 * @param string $data Data to encode.
		 * @return string
		 */
		private static function encode(string $data): string {
			return \rtrim(\strtr(\base64_encode($data), '+/', '-_'), '=');
		}

		/**
		 * Load the private test key.
		 * @return \OpenSSLAsymmetricKey
		 */
		private static function getKey(): \OpenSSLAsymmetricKey {
			return \openssl_pkey_get_private(\file_get_contents(__DIR__ . '/resources/OAuth/test_key.pem'));
		}

		/**
		 * Create an OAuth manager on a new in-memory database. The manager returns set responses instead of network requests.
		 * The JWKS response contains the public test key with the key ID "test".
		 * @param \Closure|null $race Runs one time before the first transaction, to simulate a parallel request.
		 * @return OAuthManager
		 */
		private function createManager(?\Closure $race = null): OAuthManager {
			$db = new class(new ConnectionString('sqlite::memory:'), Database::DB_DRIVER_PDO) extends Database {
				public ?\Closure $race = null;

				function beginTransaction(): bool {
					$race = $this->race;
					$this->race = null;
					$race?->__invoke($this);
					return parent::beginTransaction();
				}
			};
			$meta = new SQLiteMetaTable($db);
			(new UserAccountsTable($db, $meta, DatabaseType::SQLITE))->create();
			(new UserVerificationCodesTable($db, $meta))->create();
			(new OAuthAccountsTable($db, $meta))->create();
			(new OAuthStatesTable($db, $meta))->create();

			$mailer = new class implements IUserMailer {
				public function sendVerificationCode(int $userID, string $email, string $token, string $code): void {}
				public function sendPasswordReset(int $userID, string $email, string $token): void {}
			};
			$this->users = new UserManager($db, $mailer);

			$rsa = \openssl_pkey_get_details(self::getKey())['rsa'];
			$jwks = (object) ['keys' => [(object) ['kty' => 'RSA', 'use' => 'sig', 'kid' => 'test', 'n' => self::encode($rsa['n']), 'e' => self::encode($rsa['e'])]]];

			$providers = [OAuthProvider::google('client', 'secret'), OAuthProvider::microsoft('client', 'secret')];
			$oauth = new class($db, 'https://bar.net/callback', $providers) extends OAuthManager {
				public ?\stdClass $tokens = null;
				public ?\stdClass $userInfo = null;
				public ?\stdClass $jwks = null;
				public int $keyRequests = 0;

				protected function requestTokens(OAuthProvider $provider, string $code, string $redirectURI): ?\stdClass {
					return $this->tokens;
				}

				protected function requestUserInfo(string $endpoint, string $accessToken): ?\stdClass {
					return $this->userInfo;
				}

				protected function requestKeys(string $uri): ?\stdClass {
					$this->keyRequests++;
					return $this->jwks;
				}
			};

			$oauth->jwks = $jwks;
			$db->race = $race;
			return $oauth;
		}

		private UserManager $users;
	}
