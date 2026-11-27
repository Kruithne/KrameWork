<?php
	namespace KrameWork\Users\OAuth;

	use KrameWork\Caching\IDataCache;
	use KrameWork\Database\Driver\Generic;
	use KrameWork\HTTP\HTTPContext;
	use KrameWork\HTTP\Request\JSONRequest;
	use KrameWork\HTTP\Request\WebRequest;
	use KrameWork\Users\UserAccountFlags;

	require_once(__DIR__ . '/../../Caching/IDataCache.php');
	require_once(__DIR__ . '/../../Database/Driver/Generic.php');
	require_once(__DIR__ . '/../../HTTP/HTTPContext.php');
	require_once(__DIR__ . '/../../HTTP/Request/JSONRequest.php');
	require_once(__DIR__ . '/../UserAccountFlags.php');
	require_once(__DIR__ . '/OAuthProvider.php');
	require_once(__DIR__ . '/OAuthResult.php');

	/**
	 * Class OAuthManager
	 * Logs users in with OpenID Connect providers, such as Google and Microsoft.
	 * A new OAuth identity creates a new user account with the OAUTH_ACCOUNT flag.
	 * A logged in user can link more OAuth identities to their account.
	 *
	 * @package KrameWork\Users\OAuth
	 */
	class OAuthManager
	{
		const STATE_LIFETIME = 600;
		const STATE_COOKIE_NAME = 'oauth_state';
		const KEYS_LIFETIME = 3600;
		const KEYS_REFETCH_COOLDOWN = 300;
		const REQUEST_TIMEOUT = 10;
		const CLOCK_SKEW = 60;
		const GUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

		/**
		 * OAuthManager constructor.
		 *
		 * @api __construct
		 * @param Generic $db Database that contains the user tables.
		 * @param string $callbackURL URL that the provider sends the user back to. It must match the URL registered with the provider.
		 * @param OAuthProvider[] $providers Providers that users can log in with.
		 * @param IDataCache|null $cache Cache for the signing keys of the providers. Null to get the keys again for each request.
		 */
		public function __construct(private Generic $db, private string $callbackURL, array $providers, private ?IDataCache $cache = null) {
			foreach ($providers as $provider)
				$this->providers[$provider->name] = $provider;
		}

		/**
		 * Get the names of the providers.
		 *
		 * @api getProviders
		 * @return string[]
		 */
		public function getProviders(): array {
			return \array_keys($this->providers);
		}

		/**
		 * Start an OAuth login. Send the user to the returned URL.
		 *
		 * @api getAuthorizationURL
		 * @param string $provider Name of the provider.
		 * @return string|null Null if the provider is unknown.
		 */
		public function getAuthorizationURL(string $provider): ?string {
			return $this->createAuthorizationURL($provider, null);
		}

		/**
		 * Start a link of an OAuth identity to a user account. Send the user to the returned URL.
		 * Give the same user ID to callback() to complete the link.
		 *
		 * @api getLinkURL
		 * @param string $provider Name of the provider.
		 * @param int $userID ID of the logged in user account.
		 * @return string|null Null if the provider is unknown.
		 */
		public function getLinkURL(string $provider, int $userID): ?string {
			return $this->createAuthorizationURL($provider, $userID);
		}

		/**
		 * Get the names of the providers that have an identity linked to a user account.
		 *
		 * @api getLinkedProviders
		 * @param int $userID ID of the user account.
		 * @return string[]
		 */
		public function getLinkedProviders(int $userID): array {
			$providers = $this->db->getColumn('SELECT DISTINCT provider FROM oauth_accounts WHERE user_id = ? ORDER BY provider', [$userID]);
			return \array_values(\array_filter($providers, 'is_string'));
		}

		/**
		 * Remove the identities of a provider from a user account.
		 * An account with the OAUTH_ACCOUNT flag has no password, so its last identity is not removed.
		 *
		 * @api unlink
		 * @param int $userID ID of the user account.
		 * @param string $provider Name of the provider.
		 * @return bool False if no identity was removed.
		 */
		public function unlink(int $userID, string $provider): bool {
			$flags = $this->db->getValue('SELECT flags FROM user_accounts WHERE id = ?', [$userID]);
			if (!\is_numeric($flags))
				return false;

			if (((int) $flags & UserAccountFlags::OAUTH_ACCOUNT) !== 0) {
				$others = $this->db->getValue('SELECT COUNT(*) FROM oauth_accounts WHERE user_id = ? AND provider <> ?', [$userID, $provider]);
				if (!\is_numeric($others) || (int) $others === 0)
					return false;
			}

			return $this->db->execute('DELETE FROM oauth_accounts WHERE user_id = ? AND provider = ?', [$userID, $provider]) > 0;
		}

		/**
		 * Complete an OAuth login or link with the state and code that the provider sent to the callback URL.
		 * The state must match the state cookie of the browser. This does not start a session.
		 *
		 * @api callback
		 * @param string $state State parameter of the callback.
		 * @param string $code Code parameter of the callback.
		 * @param int|null $userID ID of the logged in user account. A link completes only if this is the user ID given to getLinkURL().
		 * @return OAuthResult
		 * @throws \Throwable
		 */
		public function callback(string $state, string $code, ?int $userID = null): OAuthResult {
			$cookie = $_COOKIE[self::STATE_COOKIE_NAME] ?? null;
			$this->sendStateCookie('', 1);
			if (!\is_string($cookie) || !\hash_equals($cookie, $state))
				return new OAuthResult(OAuthStatus::INVALID_STATE);

			$row = $this->db->getRow('SELECT provider, redirect_uri, code_verifier, user_id, created FROM oauth_states WHERE state = ?', [$state]);
			if ($row === null || $this->db->execute('DELETE FROM oauth_states WHERE state = ?', [$state]) !== 1)
				return new OAuthResult(OAuthStatus::INVALID_STATE);

			if (!\is_numeric($row->created) || (int) $row->created < \time() - self::STATE_LIFETIME || !\is_string($row->code_verifier))
				return new OAuthResult(OAuthStatus::INVALID_STATE);

			$linkUserID = \is_numeric($row->user_id) ? (int) $row->user_id : null;
			if ($linkUserID !== null && $linkUserID !== $userID)
				return new OAuthResult(OAuthStatus::INVALID_STATE);

			$provider = $this->providers[\is_string($row->provider) ? $row->provider : ''] ?? null;
			if ($provider === null)
				return new OAuthResult(OAuthStatus::UNKNOWN_PROVIDER);

			$tokens = $this->requestTokens($provider, $code, \is_string($row->redirect_uri) ? $row->redirect_uri : '', $row->code_verifier);
			$idToken = $tokens?->id_token ?? null;
			if ($tokens === null || !\is_string($idToken))
				return new OAuthResult(OAuthStatus::TOKEN_EXCHANGE_FAILED);

			$claims = $this->decodeIDToken($provider, $idToken);
			$subject = $claims->sub ?? null;
			if ($claims === null || !self::isValidClaims($provider, $claims, self::createNonce($state)) || !\is_string($subject) || $subject === '')
				return new OAuthResult(OAuthStatus::INVALID_ID_TOKEN);

			if ($linkUserID !== null)
				return $this->link($provider->name, $subject, $linkUserID, $claims);

			$source = $claims;
			$accessToken = $tokens->access_token ?? null;
			if (!\is_string($claims->email ?? null) && $provider->userInfoEndpoint !== null && \is_string($accessToken)) {
				$info = $this->requestUserInfo($provider->userInfoEndpoint, $accessToken);
				if ($info !== null && ($info->sub ?? null) === $subject)
					$source = $info;
			}

			$email = $source->email ?? null;
			if (!\is_string($email) || $email === '')
				return new OAuthResult(OAuthStatus::MISSING_EMAIL);

			if (!self::isEmailVerified($provider, $source))
				return new OAuthResult(OAuthStatus::EMAIL_NOT_VERIFIED);

			return $this->login($provider->name, $subject, \strtolower(\trim($email)), $claims);
		}

		/**
		 * Send the code to the token endpoint of the provider and return the token response.
		 *
		 * @param OAuthProvider $provider Provider of the login.
		 * @param string $code Authorization code.
		 * @param string $redirectURI Redirect URI used in the authorization request.
		 * @param string $codeVerifier PKCE code verifier of the authorization request.
		 * @return \stdClass|null Null if the request failed.
		 */
		protected function requestTokens(OAuthProvider $provider, string $code, string $redirectURI, string $codeVerifier): ?\stdClass {
			$request = new JSONRequest($provider->tokenEndpoint, WebRequest::METHOD_POST);
			$request->client_id = $provider->clientID;
			$request->client_secret = $provider->clientSecret;
			$request->code = $code;
			$request->redirect_uri = $redirectURI;
			$request->grant_type = 'authorization_code';
			$request->code_verifier = $codeVerifier;
			$request->setTimeout(self::REQUEST_TIMEOUT);

			if (!$request->send())
				return null;

			$response = $request->getResponse();
			return $response instanceof \stdClass ? $response : null;
		}

		/**
		 * Send the state cookie to the browser. The cookie uses SameSite=Lax, because the provider sends the user back with a cross-site redirect.
		 *
		 * @param string $state State token. An empty string with an expiry in the past deletes the cookie.
		 * @param int $expires Expiry time of the cookie as a Unix timestamp.
		 */
		protected function sendStateCookie(string $state, int $expires): void {
			\setcookie(self::STATE_COOKIE_NAME, $state, [
				'expires' => $expires,
				'path' => '/',
				'secure' => HTTPContext::isSecure(),
				'httponly' => true,
				'samesite' => 'Lax'
			]);
		}

		/**
		 * Request the claims of the user from a UserInfo endpoint.
		 *
		 * @param string $endpoint UserInfo endpoint URL.
		 * @param string $accessToken Access token from the token response.
		 * @return \stdClass|null Null if the request failed.
		 */
		protected function requestUserInfo(string $endpoint, string $accessToken): ?\stdClass {
			$request = new JSONRequest($endpoint);
			$request->setHeader('Authorization', 'Bearer ' . $accessToken);
			$request->setTimeout(self::REQUEST_TIMEOUT);

			if (!$request->send())
				return null;

			$response = $request->getResponse();
			return $response instanceof \stdClass ? $response : null;
		}

		/**
		 * Request the signing keys (JWKS) of a provider.
		 *
		 * @param string $uri JWKS URL.
		 * @return \stdClass|null Null if the request failed.
		 */
		protected function requestKeys(string $uri): ?\stdClass {
			$request = new JSONRequest($uri);
			$request->setTimeout(self::REQUEST_TIMEOUT);

			if (!$request->send())
				return null;

			$response = $request->getResponse();
			return $response instanceof \stdClass ? $response : null;
		}

		/**
		 * Store a new state token, send it in the state cookie and return the authorization URL of a provider.
		 *
		 * @param string $provider Name of the provider.
		 * @param int|null $userID ID of the user account to link the identity to. Null for a login.
		 * @return string|null Null if the provider is unknown.
		 */
		private function createAuthorizationURL(string $provider, ?int $userID): ?string {
			$config = $this->providers[$provider] ?? null;
			if ($config === null)
				return null;

			$time = \time();
			$state = \bin2hex(\random_bytes(32));
			$codeVerifier = \bin2hex(\random_bytes(32));

			$this->db->execute('DELETE FROM oauth_states WHERE created < ?', [$time - self::STATE_LIFETIME]);
			$this->db->execute(
				'INSERT INTO oauth_states (state, provider, redirect_uri, code_verifier, user_id, created) VALUES (?, ?, ?, ?, ?, ?)',
				[$state, $config->name, $this->callbackURL, $codeVerifier, $userID, $time]
			);
			$this->sendStateCookie($state, $time + self::STATE_LIFETIME);

			return $config->authEndpoint . '?' . \http_build_query([
				'client_id' => $config->clientID,
				'redirect_uri' => $this->callbackURL,
				'response_type' => 'code',
				'scope' => \implode(' ', $config->scopes),
				'state' => $state,
				'nonce' => self::createNonce($state),
				'code_challenge' => self::encodeBase64URL(\hash('sha256', $codeVerifier, true)),
				'code_challenge_method' => 'S256'
			], '', '&', \PHP_QUERY_RFC3986);
		}

		/**
		 * Link an OAuth identity to a user account.
		 *
		 * @param string $provider Name of the provider.
		 * @param string $subject Subject claim of the ID token.
		 * @param int $userID ID of the user account.
		 * @param \stdClass $claims Claims of the ID token.
		 * @return OAuthResult
		 * @throws \PDOException
		 */
		private function link(string $provider, string $subject, int $userID, \stdClass $claims): OAuthResult {
			$flags = $this->db->getValue('SELECT flags FROM user_accounts WHERE id = ?', [$userID]);
			if (!\is_numeric($flags))
				return new OAuthResult(OAuthStatus::ACCOUNT_NOT_FOUND);

			if (((int) $flags & UserAccountFlags::ACCOUNT_DISABLED) !== 0)
				return new OAuthResult(OAuthStatus::ACCOUNT_DISABLED);

			$owner = $this->getIdentityAccount($provider, $subject);
			if ($owner === null) {
				try {
					$this->db->execute(
						'INSERT INTO oauth_accounts (provider, provider_user_id, user_id, linked) VALUES (?, ?, ?, ?)',
						[$provider, $subject, $userID, \time()]
					);
				} catch (\PDOException $e) {
					if (!\str_starts_with((string) $e->getCode(), '23'))
						throw $e;

					$owner = $this->getIdentityAccount($provider, $subject);
					if ($owner === null)
						return new OAuthResult(OAuthStatus::IDENTITY_IN_USE);
				}
			}

			if ($owner !== null && (int) $owner->id !== $userID)
				return new OAuthResult(OAuthStatus::IDENTITY_IN_USE);

			return new OAuthResult(OAuthStatus::SUCCESS, $userID, $claims, false, true);
		}

		/**
		 * Log in with an OAuth identity. Create a user account if the identity has no account.
		 *
		 * @param string $provider Name of the provider.
		 * @param string $subject Subject claim of the ID token.
		 * @param string $email E-mail address of the identity.
		 * @param \stdClass $claims Claims of the ID token.
		 * @return OAuthResult
		 * @throws \Throwable
		 */
		private function login(string $provider, string $subject, string $email, \stdClass $claims): OAuthResult {
			$result = $this->findAccount($provider, $subject, $claims);
			if ($result !== null)
				return $result;

			if ($this->db->getValue('SELECT 1 FROM user_accounts WHERE email = ?', [$email]) !== null)
				return new OAuthResult(OAuthStatus::EMAIL_IN_USE);

			$time = \time();

			$this->db->beginTransaction();
			try {
				$this->db->execute(
					'INSERT INTO user_accounts (email, password, flags, registered) VALUES (?, ?, ?, ?)',
					[$email, '', UserAccountFlags::OAUTH_ACCOUNT, $time]
				);

				$userID = (int) $this->db->getLastInsertID();
				$this->db->execute(
					'INSERT INTO oauth_accounts (provider, provider_user_id, user_id, linked) VALUES (?, ?, ?, ?)',
					[$provider, $subject, $userID, $time]
				);
				$this->db->commitTransaction();
			} catch (\Throwable $e) {
				$this->db->rollbackTransaction();
				if (!$e instanceof \PDOException || !\str_starts_with((string) $e->getCode(), '23'))
					throw $e;

				return $this->findAccount($provider, $subject, $claims) ?? new OAuthResult(OAuthStatus::EMAIL_IN_USE);
			}

			return new OAuthResult(OAuthStatus::SUCCESS, $userID, $claims, true);
		}

		/**
		 * Get the login result for the user account of an OAuth identity.
		 *
		 * @param string $provider Name of the provider.
		 * @param string $subject Subject claim of the ID token.
		 * @param \stdClass $claims Claims of the ID token.
		 * @return OAuthResult|null Null if the identity has no account.
		 */
		private function findAccount(string $provider, string $subject, \stdClass $claims): ?OAuthResult {
			$account = $this->getIdentityAccount($provider, $subject);
			if ($account === null)
				return null;

			if (((int) $account->flags & UserAccountFlags::ACCOUNT_DISABLED) !== 0)
				return new OAuthResult(OAuthStatus::ACCOUNT_DISABLED);

			return new OAuthResult(OAuthStatus::SUCCESS, (int) $account->id, $claims);
		}

		/**
		 * Get the ID and flags of the user account that an OAuth identity is linked to.
		 * If the account does not exist, delete the link.
		 *
		 * @param string $provider Name of the provider.
		 * @param string $subject Subject claim of the ID token.
		 * @return \stdClass|null Null if the identity has no account.
		 */
		private function getIdentityAccount(string $provider, string $subject): ?\stdClass {
			$account = $this->db->getRow(
				'SELECT a.id, a.flags FROM oauth_accounts o LEFT JOIN user_accounts a ON a.id = o.user_id WHERE o.provider = ? AND o.provider_user_id = ?',
				[$provider, $subject]
			);

			if ($account === null)
				return null;

			if (!\is_numeric($account->id) || !\is_numeric($account->flags)) {
				$this->db->execute('DELETE FROM oauth_accounts WHERE provider = ? AND provider_user_id = ?', [$provider, $subject]);
				return null;
			}

			return $account;
		}

		/**
		 * Check the signature of an ID token and decode its claims.
		 * The token must be signed with RS256 by a key in the JWKS of the provider.
		 *
		 * @param OAuthProvider $provider Provider of the login.
		 * @param string $token ID token.
		 * @return \stdClass|null Null if the token is malformed or the signature is not valid.
		 */
		private function decodeIDToken(OAuthProvider $provider, string $token): ?\stdClass {
			$parts = \explode('.', $token);
			if (\count($parts) !== 3)
				return null;

			$header = self::decodeSegment($parts[0]);
			$claims = self::decodeSegment($parts[1]);
			$signature = self::decodeBase64URL($parts[2]);
			$keyID = $header->kid ?? null;
			if ($header === null || $claims === null || $signature === null || ($header->alg ?? null) !== 'RS256' || !\is_string($keyID))
				return null;

			$key = $this->getKeys($provider, false)[$keyID] ?? $this->getKeys($provider, true)[$keyID] ?? null;
			if ($key === null)
				return null;

			if (\openssl_verify($parts[0] . '.' . $parts[1], $signature, $key, \OPENSSL_ALGO_SHA256) !== 1)
				return null;

			return $claims;
		}

		/**
		 * Get the signing keys of a provider from the cache, or request them.
		 * When refresh is true, the keys are requested again if the last request is older than KEYS_REFETCH_COOLDOWN seconds.
		 *
		 * @param OAuthProvider $provider Provider of the login.
		 * @param bool $refresh Request the keys again, for example after a key rotation.
		 * @return array<string, string> PEM public keys by key ID.
		 */
		private function getKeys(OAuthProvider $provider, bool $refresh): array {
			$cacheKey = 'kramework_oauth_jwks_' . \md5($provider->jwksURI);
			$entry = $this->keys[$cacheKey] ?? self::toKeyEntry($this->cache?->__get($cacheKey));
			$time = \time();

			if ($entry !== null && $time - $entry['fetched'] < ($refresh ? self::KEYS_REFETCH_COOLDOWN : self::KEYS_LIFETIME))
				return $entry['keys'];

			$response = $this->requestKeys($provider->jwksURI);
			if ($response === null)
				return $entry['keys'] ?? [];

			$entry = ['fetched' => $time, 'keys' => self::parseKeys($response)];
			$this->keys[$cacheKey] = $entry;
			$this->cache?->store($cacheKey, $entry, self::KEYS_LIFETIME);

			return $entry['keys'];
		}

		/**
		 * Convert a cached value to a key entry.
		 *
		 * @param mixed $value Cached value.
		 * @return array{fetched: int, keys: array<string, string>}|null Null if the value is not a key entry.
		 */
		private static function toKeyEntry(mixed $value): ?array {
			if (!\is_array($value))
				return null;

			$fetched = $value['fetched'] ?? null;
			$cached = $value['keys'] ?? null;
			if (!\is_int($fetched) || !\is_array($cached))
				return null;

			$keys = [];
			foreach ($cached as $keyID => $pem)
				if (\is_string($pem))
					$keys[(string) $keyID] = $pem;

			return ['fetched' => $fetched, 'keys' => $keys];
		}

		/**
		 * Convert the RSA signing keys of a JWKS to PEM public keys.
		 *
		 * @param \stdClass $jwks JWKS response.
		 * @return array<string, string> PEM public keys by key ID.
		 */
		private static function parseKeys(\stdClass $jwks): array {
			$keys = [];
			$list = $jwks->keys ?? null;
			if (!\is_array($list))
				return $keys;

			foreach ($list as $jwk) {
				if (!$jwk instanceof \stdClass || ($jwk->kty ?? null) !== 'RSA' || ($jwk->use ?? 'sig') !== 'sig')
					continue;

				$keyID = $jwk->kid ?? null;
				$modulus = $jwk->n ?? null;
				$exponent = $jwk->e ?? null;
				if (!\is_string($keyID) || !\is_string($modulus) || !\is_string($exponent))
					continue;

				$pem = self::createPublicKey($modulus, $exponent);
				if ($pem !== null)
					$keys[$keyID] = $pem;
			}

			return $keys;
		}

		/**
		 * Create a PEM public key from the modulus and exponent of an RSA JWK.
		 *
		 * @param string $modulus Base64url encoded modulus (n).
		 * @param string $exponent Base64url encoded exponent (e).
		 * @return string|null Null if the values do not make a valid key.
		 */
		private static function createPublicKey(string $modulus, string $exponent): ?string {
			$n = self::decodeBase64URL($modulus);
			$e = self::decodeBase64URL($exponent);
			if ($n === null || $e === null || $n === '' || $e === '')
				return null;

			$rsaKey = self::encodeDER(0x30, self::encodeDERInteger($n) . self::encodeDERInteger($e));
			$algorithm = "\x30\x0d\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01\x05\x00";
			$publicKey = self::encodeDER(0x30, $algorithm . self::encodeDER(0x03, "\x00" . $rsaKey));

			$pem = "-----BEGIN PUBLIC KEY-----\n" . \chunk_split(\base64_encode($publicKey), 64, "\n") . "-----END PUBLIC KEY-----\n";
			return \openssl_pkey_get_public($pem) === false ? null : $pem;
		}

		/**
		 * Encode an unsigned big-endian integer as a DER INTEGER.
		 *
		 * @param string $value Bytes of the integer.
		 * @return string
		 */
		private static function encodeDERInteger(string $value): string {
			if (\ord($value[0]) >= 0x80)
				$value = "\x00" . $value;

			return self::encodeDER(0x02, $value);
		}

		/**
		 * Encode a DER element with a tag, a length and a value.
		 *
		 * @param int $tag DER tag.
		 * @param string $value Encoded value.
		 * @return string
		 */
		private static function encodeDER(int $tag, string $value): string {
			$length = \strlen($value);
			if ($length < 0x80)
				return \chr($tag) . \chr($length) . $value;

			$lengthBytes = \ltrim(\pack('N', $length), "\x00");
			return \chr($tag) . \chr(0x80 | \strlen($lengthBytes)) . $lengthBytes . $value;
		}

		/**
		 * Encode a string as base64url without padding.
		 *
		 * @param string $value Value to encode.
		 * @return string
		 */
		private static function encodeBase64URL(string $value): string {
			return \rtrim(\strtr(\base64_encode($value), '+/', '-_'), '=');
		}

		/**
		 * Create the nonce of a login from its state token.
		 *
		 * @param string $state State token of the login.
		 * @return string
		 */
		private static function createNonce(string $state): string {
			return \hash('sha256', $state);
		}

		/**
		 * Check that the provider marks the e-mail address as verified.
		 * The verification claim must be true. If the provider has no verification claim, the e-mail address is trusted.
		 *
		 * @param OAuthProvider $provider Provider of the login.
		 * @param \stdClass $claims Claims that contain the e-mail address.
		 * @return bool
		 */
		private static function isEmailVerified(OAuthProvider $provider, \stdClass $claims): bool {
			$claim = $provider->emailVerifiedClaim;
			if ($claim === null)
				return true;

			$verified = $claims->$claim ?? null;
			return $verified === true || $verified === 'true';
		}

		/**
		 * Decode a base64url encoded JSON object.
		 *
		 * @param string $segment Base64url encoded JSON.
		 * @return \stdClass|null Null if the segment is not a JSON object.
		 */
		private static function decodeSegment(string $segment): ?\stdClass {
			$json = self::decodeBase64URL($segment);
			if ($json === null)
				return null;

			$value = \json_decode($json);
			return $value instanceof \stdClass ? $value : null;
		}

		/**
		 * Decode a base64url encoded string.
		 *
		 * @param string $value Base64url encoded string, with or without padding.
		 * @return string|null Null if the value is not valid base64url.
		 */
		private static function decodeBase64URL(string $value): ?string {
			$base64 = \strtr($value, '-_', '+/');
			$decoded = \base64_decode(\str_pad($base64, \strlen($base64) + (4 - \strlen($base64) % 4) % 4, '='), true);
			return $decoded === false ? null : $decoded;
		}

		/**
		 * Check the nonce, time, issuer and audience claims of an ID token.
		 * The time claims (exp, iat and nbf) can differ from the server clock by CLOCK_SKEW seconds.
		 * An issuer with "{tenantid}" only matches if the tid claim is a GUID.
		 *
		 * @param OAuthProvider $provider Provider of the login.
		 * @param \stdClass $claims Claims of the ID token.
		 * @param string $nonce Nonce of the login.
		 * @return bool
		 */
		private static function isValidClaims(OAuthProvider $provider, \stdClass $claims, string $nonce): bool {
			$tokenNonce = $claims->nonce ?? null;
			if (!\is_string($tokenNonce) || !\hash_equals($nonce, $tokenNonce))
				return false;

			$time = \time();
			$expires = $claims->exp ?? null;
			if (!\is_numeric($expires) || (int) $expires + self::CLOCK_SKEW <= $time)
				return false;

			$issued = $claims->iat ?? null;
			if (!\is_numeric($issued) || (int) $issued - self::CLOCK_SKEW > $time)
				return false;

			$notBefore = $claims->nbf ?? null;
			if ($notBefore !== null && (!\is_numeric($notBefore) || (int) $notBefore - self::CLOCK_SKEW > $time))
				return false;

			$audience = $claims->aud ?? null;
			if (!\is_array($audience))
				$audience = [$audience];

			if (!\in_array($provider->clientID, $audience, true))
				return false;

			if (\count($audience) > 1 && ($claims->azp ?? null) !== $provider->clientID)
				return false;

			$issuer = $claims->iss ?? null;
			if (!\is_string($issuer))
				return false;

			$tenant = $claims->tid ?? null;
			$isTenantID = \is_string($tenant) && \preg_match(self::GUID_PATTERN, $tenant) === 1;

			foreach ($provider->issuers as $accepted) {
				if (\str_contains($accepted, '{tenantid}')) {
					if (!$isTenantID)
						continue;

					$accepted = \str_replace('{tenantid}', $tenant, $accepted);
				}

				if ($accepted === $issuer)
					return true;
			}

			return false;
		}

		/**
		 * @var array<string, OAuthProvider>
		 */
		private array $providers = [];

		/**
		 * @var array<string, array{fetched: int, keys: array<string, string>}>
		 */
		private array $keys = [];
	}
