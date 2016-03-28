### User system

To use the Kramework user system, there is nothing to enable, simply extend the requisite components and inject them, then expose the services and call them as needed.

#### Services

You need to extend the KW_UserSystem class

```php
class UserService extends KW_UserSystem
{
	public function __construct(ISchemaManager $schema, IAccessControl $acl, IManyInject $kernel, IErrorHandler $error)
	{
		parent::__construct($schema, $acl, $kernel, $error);
	}

	public function getCurrent()
	{
		if(!isset($_SESSION['userid']))
			return false;
		return $this->read($_SESSION['userid']);
	}
}
```

The getCurrent method works in concert with your authentication service to provide the current logged in user.

To add multifactor authentication, you need to extend KW_MultifactorService

```php
class MultiFactorService extends KW_MultifactorService
{
	public function __construct(KW_UserSystem $users, IAuthenticator $auth)
	{
		parent::__construct($users, $auth, 'https://example.com');
	}
}
```

To handle authentication, you extend KW_AuthenticationService

```php
class AuthService extends KW_AuthenticationService
{
	public function __construct(UserStore $users)
	{
		parent::__construct($users, 'https://example.com', true);
	}

	protected function grant_token($user, $token)
	{
		setcookie('auth_token',$token,strtotime('+1 year'),'/auth.php','example.com',true,true);
	}
}
```

#### Logging in

```js
angular.module('AuthService', ['ngResource']).factory('Auth',
[
	'$resource',
	function($resource)
	{
		return $resource(
			'https://example.com/auth.php/:method',
			{ method: '' },
			{
				login: { method:'POST', withCredentials: true, params: { method: 'login' } },
				recover: { method:'POST', withCredentials: true, params: { method: 'recover' } },
				logout: { method:'GET', withCredentials: true, params: { method: 'logout' } },
				get_session: { method:'GET', withCredentials: true },
			}
		);
	}
]);
angularApp.controller('LoginController',
[
	'$state', 'Auth',
	function($state, Auth)
	{
		var login = this;
		login.state = 0;
		login.username = '';
		login.passphrase = '';
		login.remember = false;
		login.message = '';
		login.try = function()
		{
			Auth.login(
				{
					username: login.username,
					passphrase: login.passphrase,
					remember: login.remember
				},
				function(r)
				{
					if(r && 'error' in r)
						alert(r.error.message);
					else
					{
						login.state = r.state;
						login.passphrase = '';
						if(login.state == -2 || login.state == 3)
							$state.go('Authenticator.Setup');
						if(login.state == 3)
							$state.go('Authenticator');
						if(login.state == 1)
							$state.go('main');
						}
				}
			);
		};
	}
]);
```
