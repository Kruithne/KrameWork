<?php
	abstract class KW_UserSystem extends KW_CRUD implements IUserSystem, IStartup, IErrorHint
	{
		public function getName() { return 'users'; }

		/**
		 * KW_UserSystem constructor.
		 * @param ISchemaManager $schema
		 * @param IErrorHandler|null $error
		 */
		public function __construct(ISchemaManager $schema, IAccessControl $acl, IManyInject $kernel, $error)
		{
			$this->acl = $acl;
			$this->kernel = $kernel;
			parent::__construct($schema, $error);
		}

		public function getErrorHintLabel()
		{
			return 'Current user';
		}

		public function getErrorHint()
		{
			$user = $this->getCurrent();
			if($user)
				return $user->username;
			return '(none)';
		}

		public function prepare()
		{
			parent::prepare();
			$table = $this->getName();
			$this->getByUsername = $this->db->prepare('SELECT * FROM '. $table. ' WHERE username LIKE :username');
			$this->getByEmail = $this->db->prepare('SELECT * FROM '. $table. ' WHERE email LIKE :email');
			$this->setUserSessionSalt = $this->db->prepare('UPDATE '. $table. ' SET session_salt=:salt WHERE id=:id');
			$this->setUserPassphrase = $this->db->prepare('UPDATE '. $table. ' SET passphrase=:passphrase, pp_changed=current_timestamp WHERE id=:id');
			$this->setUserSecret = $this->db->prepare('UPDATE '. $table. ' SET secret=:secret WHERE id=:id');
			$this->loginFailed = $this->db->prepare('UPDATE '. $table. ' SET failed_logins = failed_logins + 1 WHERE id = :id');
			$this->setUserLockout = $this->db->prepare('UPDATE '. $table. ' SET pp_locked = current_timestamp WHERE id = :id');
			$this->setLoginSuccess = $this->db->prepare('UPDATE '. $table. ' SET failed_logins = 0, last_login = current_timestamp WHERE id = :id');
		}

		public function getNewObject($data)
		{
			return new KW_User($this->acl, $data->getAsArray());
		}

		public function setLoginSuccess($id)
		{
			$this->setLoginSuccess->id = $id;
			$this->setLoginSuccess->execute();
		}

		public function setLoginFailed($id)
		{
			$this->loginFailed->id = $id;
			$this->loginFailed->execute();
		}

		public function lockout($id)
		{
			$this->setUserLockout->id = $id;
			$this->setUserLockout->execute();
		}

		public function setSessionSalt($id, $salt)
		{
			$this->setUserSessionSalt->salt = $salt;
			$this->setUserSessionSalt->id = $id;
			$this->setUserSessionSalt->execute();
		}

		public function setSecret($id, $secret)
		{
			$this->setUserSecret->id = $id;
			$this->setUserSecret->secret = $secret;
			$this->setUserSecret->execute();
		}

		public function setPassphrase($id, $plaintext)
		{
			$this->setUserPassphrase->id = $id;
			$this->setUserPassphrase->passphrase = $this->encode($plaintext);
			$this->setUserPassphrase->execute();
		}

		public function getUser($username = null, $email = null, $id = null)
		{
			if ($id !== null)
			{
				return $this->read($id);
			}
			if ($username !== null)
			{
				$this->getByUsername->username = $username;
				$result = $this->getByUsername->execute()->getRows();
			}
			if ($email !== null)
			{
				$this->getByEmail->email = $email;
				$result = $this->getByEmail->execute()->getRows();
			}
			if ($result && count($result) == 1)
				return $this->getNewObject($result[0]);
		}

		public function getUsers()
		{
			$result = array();
			foreach($this->getAll->execute()->getRows() as $data)
				$result[] = $this->getNewObject($data);
			return $result;
		}

		public function addUser($user)
		{
			return $this->create($user);
		}

		public function saveUser($user)
		{
			$this->update($user);
		}

		public function start()
		{
			$user = $this->getCurrent();
			$services = $this->kernel->getComponents('ICRUDService');
			if($services)
				foreach($services as $service)
					$service->authorize($user);
		}

		public function getVersion()
		{
			return 4;
		}

		public function getKey() { return 'id'; }
		public function hasAutoKey() { return true; }
		public function getValues()
		{
			return [ 
				'username',
				'name',
				'passphrase',
				'secret',
				'lastcode',
				'email',
				'created',
				'pp_changed',
				'pp_locked',
				'active',
				'session_salt',
				'failed_logins',
				'last_login'
			];
		}


		public function getQueries()
		{
			$table = $this->getName();
			switch($this->db->getType())
			{
				case 'pgsql':
					return array(
						1 => array('
							CREATE TABLE '. $table. ' (
								id SERIAL NOT NULL,
								username VARCHAR(50) NOT NULL,
								name VARCHAR(50) NOT NULL,
								passphrase VARCHAR(100) NOT NULL,
								secret VARCHAR(32),
								lastcode INTEGER,
								email VARCHAR(100) NOT NULL,
								created TIMESTAMP NOT NULL,
								pp_changed TIMESTAMP NOT NULL,
								pp_locked TIMESTAMP,
								active BOOLEAN NOT NULL,
								PRIMARY KEY (id),
								UNIQUE (username),
								UNIQUE (email)
							)'
						),
						2 => array('
							ALTER TABLE '. $table. '
								ADD COLUMN session_salt VARCHAR(32),
								ADD COLUMN failed_logins SMALLINT
							'),
						3 => array('UPDATE '. $table. ' SET failed_logins = 0'),
						4 => array('
							ALTER TABLE '. $table. '
								ADD COLUMN last_login TIMESTAMP
							')
					);

				case 'dblib':
					return [
						1 => ['
CREATE TABLE ['.$table.'] (
	id BIGINT NOT NULL IDENTITY(1,1),
	username VARCHAR(50) NOT NULL,
	name VARCHAR(50) NOT NULL,
	passphrase VARCHAR(100) NOT NULL,
	secret VARCHAR(32),
	lastcode INT,
	email VARCHAR(100) NOT NULL,
	created DATETIME NOT NULL,
	pp_changed DATETIME NOT NULL,
	pp_locked DATETIME,
	active BIT NOT NULL,
	session_salt VARCHAR(32),
	failed_logins SMALLINT DEFAULT 0,
	last_login DATETIME,
	CONSTRAINT PK_'.$table.' PRIMARY KEY NONCLUSTERED (id)
)',
'CREATE UNIQUE INDEX AK_'.$table.'_username ON '.$table.' (username)',
'CREATE UNIQUE INDEX AK_'.$table.'_email ON '.$table.' (email)'
						],
						2 => [], 3 => [], 4 => []
					];
			}
			trigger_error('The database driver "'.$this->db->getType().'" is not yet supported by SchemaManager, sorry!', E_USER_ERROR);
		}

		public function authenticate($username, $passphrase)
		{
			$user = $this->getUser($username);
			if (!$user || !$user->active)
			{
				error_log('Unknown username');
				return AUTH_ERR_UNKNOWN;
			}

			if (!$this->verify($passphrase, $user->passphrase))
			{
				if ($user->failed_logins >= 4)
					$this->lockout($user->id);

				$this->setLoginFailed($user->id);
				error_log('Wrong passphrase');
				return AUTH_ERR_UNKNOWN;
			}
			return $this->getState($user);
		}

		public function getState($user)
		{
			if ($user->pp_locked && strtotime($user->pp_locked) + 900 > time())
				return AUTH_ERR_LOCKOUT;

			$this->setLoginSuccess($user->id);
			if (!$user->secret)
				return AUTH_ERR_NOSECRET;

			if ($this->isExpired($user))
				return AUTH_OK_OLD;

			return AUTH_OK;
		}

		public function isExpired(IDataContainer $user)
		{
			return time() - strtotime($user->pp_changed) > 3600 * 24 * 90;
		}

		public function encode($plaintext)
		{
			if (function_exists('random_bytes'))
				return $this->hash($plaintext, base64_encode(random_bytes(21)));

			return $this->hash($plaintext, base64_encode(mcrypt_create_iv(21)));
		}

		private function verify($passphrase, $hash)
		{
			$ph = explode('$', $hash);
			$ch = $this->hash($passphrase, $ph[0], $ph[1], $ph[2]);
			return $hash == $ch;
		}

		private function hash($passphrase, $salt, $iterations = 5, $algorithm = 1)
		{
			$algo = $this->getAlgorithm($algorithm);

			for ($i = 0; $i < $iterations; ++$i)
				$passphrase = hash($algo, $salt . $passphrase);

			return $salt . '$'. $iterations . '$' . $algorithm . '$' . $passphrase;
		}

		private function getAlgorithm($index)
		{
			switch($index)
			{
				case 1:
				default:
					return 'sha256';
			}
		}

		private $acl;
		private $kernel;
	}
