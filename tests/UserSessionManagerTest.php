<?php
	use KrameWork\Database\ConnectionString;
	use KrameWork\Database\Database;
	use KrameWork\Database\Schema\SQLiteMetaTable;
	use KrameWork\Security\Password;
	use KrameWork\Users\IUserMailer;
	use KrameWork\Users\Schema\DatabaseType;
	use KrameWork\Users\Schema\UserAccountsTable;
	use KrameWork\Users\Schema\UserSessionsTable;
	use KrameWork\Users\Schema\UserVerificationCodesTable;
	use KrameWork\Users\UnknownUserException;
	use KrameWork\Users\UserAccountFlags;
	use KrameWork\Users\UserManager;
	use KrameWork\Users\UserSessionManager;
	use PHPUnit\Framework\Attributes\RunInSeparateProcess;

	require_once(__DIR__ . '/../src/Database/Database.php');
	require_once(__DIR__ . '/../src/Database/Schema/SQLiteMetaTable.php');
	require_once(__DIR__ . '/../src/Users/UserManager.php');
	require_once(__DIR__ . '/../src/Users/UserSessionManager.php');
	require_once(__DIR__ . '/../src/Users/Schema/UserAccountsTable.php');
	require_once(__DIR__ . '/../src/Users/Schema/UserSessionsTable.php');
	require_once(__DIR__ . '/../src/Users/Schema/UserVerificationCodesTable.php');

	class UserSessionManagerTest extends PHPUnit\Framework\TestCase
	{
		/**
		 * Test that a created session is found by ID with the user ID and flags of the account.
		 */
		public function testCreatedSessionIsFound() {
			$sessions = $this->createManager();
			$created = $sessions->create($this->userID);

			$session = $sessions->get($created->sessionID);
			$this->assertNotNull($session);
			$this->assertSame($this->userID, $session->userID);
			$this->assertTrue($session->hasFlag(UserAccountFlags::REQUIRES_VERIFICATION));
		}

		/**
		 * Test that an ended session is not found.
		 */
		public function testEndedSessionIsNotFound() {
			$sessions = $this->createManager();
			$session = $sessions->create($this->userID);

			$sessions->end($session->sessionID);
			$this->assertNull($sessions->get($session->sessionID));
		}

		/**
		 * Test that a session of a disabled account is not found, so it does not keep access.
		 */
		public function testDisabledAccountSessionIsNotFound() {
			$sessions = $this->createManager();
			$session = $sessions->create($this->userID);

			$this->users->setFlag($this->userID, UserAccountFlags::ACCOUNT_DISABLED, true);
			$this->assertNull($sessions->get($session->sessionID));
		}

		/**
		 * Test that ending all sessions of an account ends each session of that account.
		 */
		public function testEndAllEndsEverySession() {
			$sessions = $this->createManager();
			$first = $sessions->create($this->userID);
			$second = $sessions->create($this->userID);

			$sessions->endAll($this->userID);
			$this->assertNull($sessions->get($first->sessionID));
			$this->assertNull($sessions->get($second->sessionID));
		}

		/**
		 * Test that the current session is read from the session cookie.
		 */
		public function testCurrentSessionIsReadFromCookie() {
			$sessions = $this->createManager();
			$session = $sessions->create($this->userID);

			$_COOKIE = [];
			$this->assertNull($sessions->getCurrent());

			$_COOKIE[UserSessionManager::DEFAULT_COOKIE_NAME] = $session->sessionID;
			$this->assertSame($session->sessionID, $sessions->getCurrent()?->sessionID);
			$_COOKIE = [];
		}

		/**
		 * Test that the database does not contain the session ID that is given to the client.
		 */
		public function testSessionIDIsNotStored() {
			$sessions = $this->createManager();
			$session = $sessions->create($this->userID);

			$this->assertNull($this->db->getValue('SELECT 1 FROM user_sessions WHERE session_id = ?', [$session->sessionID]));
		}

		/**
		 * Test that starting a session ends the session of the current request.
		 */
		#[RunInSeparateProcess]
		public function testStartEndsPreviousSession() {
			$sessions = $this->createManager();
			$first = $sessions->start($this->userID);
			$second = $sessions->start($this->userID);

			$this->assertNull($sessions->get($first->sessionID));
			$this->assertNotNull($sessions->get($second->sessionID));
		}

		/**
		 * Test that a session cannot be created for an account that does not exist.
		 */
		public function testCreateRejectsUnknownUser() {
			$sessions = $this->createManager();

			$this->expectException(UnknownUserException::class);
			$sessions->create(-1);
		}

		/**
		 * Create a session manager on a new in-memory database with one user account.
		 * @return UserSessionManager
		 */
		private function createManager(): UserSessionManager {
			$db = $this->db = new Database(new ConnectionString('sqlite::memory:'), Database::DB_DRIVER_PDO);
			$meta = new SQLiteMetaTable($db);
			(new UserAccountsTable($db, $meta, DatabaseType::SQLITE))->create();
			(new UserVerificationCodesTable($db, $meta))->create();
			(new UserSessionsTable($db, $meta))->create();

			$mailer = new class implements IUserMailer {
				public function sendVerificationCode(int $userID, string $email, string $token, string $code): void {}
				public function sendPasswordReset(int $userID, string $email, string $token): void {}
			};

			$this->users = new UserManager($db, $mailer);
			$this->userID = $this->users->register('foo@bar.net', new Password('hunter2'));
			return new UserSessionManager($db);
		}

		private int $userID;
		private UserManager $users;
		private Database $db;
	}
