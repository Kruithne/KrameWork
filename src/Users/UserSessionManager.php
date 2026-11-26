<?php
	namespace KrameWork\Users;

	use KrameWork\Database\Driver\Generic;
	use KrameWork\HTTP\HTTPContext;

	require_once(__DIR__ . '/../Database/Driver/Generic.php');
	require_once(__DIR__ . '/../HTTP/HTTPContext.php');
	require_once(__DIR__ . '/UserSession.php');
	require_once(__DIR__ . '/UnknownUserException.php');

	/**
	 * Class UserSessionManager
	 * Creates, finds and ends user sessions. A cookie identifies the session of the current request.
	 *
	 * @package KrameWork\Users
	 */
	class UserSessionManager
	{
		const DEFAULT_COOKIE_NAME = 'session_id';
		const DEFAULT_LIFETIME = 31536000;

		/**
		 * UserSessionManager constructor.
		 *
		 * @api __construct
		 * @param Generic $db Database that contains the user tables.
		 * @param string $cookieName Name of the session cookie.
		 * @param int $lifetime Lifetime of the session cookie in seconds.
		 */
		public function __construct(private Generic $db, private string $cookieName = self::DEFAULT_COOKIE_NAME, private int $lifetime = self::DEFAULT_LIFETIME) {}

		/**
		 * Create a session for a user account. This does not set a cookie.
		 *
		 * @api create
		 * @param int $userID ID of the user account.
		 * @return UserSession
		 * @throws UnknownUserException
		 */
		public function create(int $userID): UserSession {
			$flags = $this->db->getValue('SELECT flags FROM user_accounts WHERE id = ?', [$userID]);
			if (!\is_numeric($flags))
				throw new UnknownUserException('No user account with ID ' . $userID);

			$sessionID = \bin2hex(\random_bytes(32));
			$time = \time();

			$this->db->execute('INSERT INTO user_sessions (session_id, user_id, created, updated) VALUES (?, ?, ?, ?)', [$sessionID, $userID, $time, $time]);
			return new UserSession($sessionID, $userID, (int) $flags, $time);
		}

		/**
		 * Create a session for a user account and set the session cookie.
		 *
		 * @api start
		 * @param int $userID ID of the user account.
		 * @return UserSession
		 * @throws UnknownUserException
		 */
		public function start(int $userID): UserSession {
			$session = $this->create($userID);

			\setcookie($this->cookieName, $session->sessionID, $this->getCookieOptions(\time() + $this->lifetime));
			$_COOKIE[$this->cookieName] = $session->sessionID;

			return $session;
		}

		/**
		 * Get a session by ID.
		 *
		 * @api get
		 * @param string $sessionID ID of the session.
		 * @return UserSession|null Null if the session does not exist.
		 */
		public function get(string $sessionID): ?UserSession {
			$row = $this->db->getRow(
				'SELECT s.user_id, s.updated, a.flags FROM user_sessions s INNER JOIN user_accounts a ON a.id = s.user_id WHERE s.session_id = ?',
				[$sessionID]
			);

			if ($row === null || !\is_numeric($row->user_id) || !\is_numeric($row->updated) || !\is_numeric($row->flags))
				return null;

			return new UserSession($sessionID, (int) $row->user_id, (int) $row->flags, (int) $row->updated);
		}

		/**
		 * Get the session of the current request from the session cookie.
		 *
		 * @api getCurrent
		 * @return UserSession|null Null if the request has no valid session.
		 */
		public function getCurrent(): ?UserSession {
			$sessionID = $_COOKIE[$this->cookieName] ?? null;
			if (!\is_string($sessionID))
				return null;

			return $this->get($sessionID);
		}

		/**
		 * End a session. This does not remove a cookie.
		 *
		 * @api end
		 * @param string $sessionID ID of the session.
		 */
		public function end(string $sessionID): void {
			$this->db->execute('DELETE FROM user_sessions WHERE session_id = ?', [$sessionID]);
		}

		/**
		 * End the session of the current request and remove the session cookie.
		 *
		 * @api endCurrent
		 */
		public function endCurrent(): void {
			$sessionID = $_COOKIE[$this->cookieName] ?? null;
			if (!\is_string($sessionID))
				return;

			$this->end($sessionID);
			\setcookie($this->cookieName, '', $this->getCookieOptions(1));
			unset($_COOKIE[$this->cookieName]);
		}

		/**
		 * End all sessions of a user account.
		 *
		 * @api endAll
		 * @param int $userID ID of the user account.
		 */
		public function endAll(int $userID): void {
			$this->db->execute('DELETE FROM user_sessions WHERE user_id = ?', [$userID]);
		}

		/**
		 * Set the updated time of all sessions of the given user accounts to now.
		 * Use this to tell clients that the account data changed.
		 *
		 * @api refresh
		 * @param int ...$userIDs IDs of the user accounts.
		 */
		public function refresh(int ...$userIDs): void {
			if (\count($userIDs) === 0)
				return;

			$placeholders = \implode(', ', \array_fill(0, \count($userIDs), '?'));
			$this->db->execute('UPDATE user_sessions SET updated = ? WHERE user_id IN (' . $placeholders . ')', [\time(), ...$userIDs]);
		}

		/**
		 * Get the options for the session cookie.
		 *
		 * @param int $expires Unix time when the cookie expires.
		 * @return array{expires: int, path: string, secure: bool, httponly: bool, samesite: string}
		 */
		private function getCookieOptions(int $expires): array {
			return [
				'expires' => $expires,
				'path' => '/',
				'secure' => HTTPContext::isSecure(),
				'httponly' => true,
				'samesite' => 'Lax'
			];
		}
	}
