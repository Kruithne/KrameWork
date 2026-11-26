## KrameWork\Users\OAuth\OAuthManager

***Table of Contents***
* **Overview** - Information about the class.
* **Examples** - Usage examples.
* **Functions** - Comprehensive list of all functions in the class.

___
### Overview
`OAuthManager` logs users in with OpenID Connect providers, such as Google and Microsoft. It uses the authorization code flow.

The first login with an identity creates a user account with the `OAUTH_ACCOUNT` flag. This account has no password. If a different account already uses the e-mail address, the login fails with `EMAIL_IN_USE`. `OAuthManager` does not link an identity to an existing account by e-mail address.

A logged in user can link an identity to their account with `getLinkURL()`. After the link, the identity logs in to that account. An identity can be linked to one account only. Give the user ID of the session to `callback()`. A link completes only if this user ID is the user ID given to `getLinkURL()`. This stops a different user from completing the link.

`unlink()` removes the identities of a provider from an account. An account with the `OAUTH_ACCOUNT` flag has no password, so `unlink()` does not remove its last identity.

If the account of an identity does not exist, the next login deletes the link and creates a new account. To delete an account, use `UserManager::deleteAccount()`. See [UserManager](UserManager.md).

`OAuthManager` does not start sessions. Use [UserSessionManager](UserSessionManager.md) to start a session after a successful login.

A state token protects each login against cross-site request forgery. A state token can be used one time, and is valid for `STATE_LIFETIME` (600) seconds.

Each login uses PKCE (`S256`) and a nonce. The token request sends the code verifier of the login. The ID token must contain the nonce of the login. This stops a stolen authorization code from being used in a different login.

`OAuthManager` checks the signature, nonce, issuer, audience, expiry (`exp`), issue time (`iat`) and, if present, the `nbf` claim of the ID token. The token must be signed with RS256 by a key in the JWKS of the provider. The time claims can differ from the server clock by `CLOCK_SKEW` (60) seconds.

`OAuthManager` keeps the signing keys for `KEYS_LIFETIME` (3600) seconds. Give an `IDataCache` (for example `APCu`) to keep the keys between requests. Without a cache, each request gets the keys again. If a token has an unknown key ID, `OAuthManager` gets the keys again, at most one time in `KEYS_REFETCH_COOLDOWN` (300) seconds. This handles key rotation by the provider.

If the ID token has no e-mail address, `OAuthManager` requests it from the UserInfo endpoint. The login fails if the provider does not mark the e-mail address as verified. See `emailVerifiedClaim` in [OAuthProvider](OAuthProvider.md).
___
### Examples
Start a login:
```php
$providers = [OAuthProvider::google($id, $secret), OAuthProvider::microsoft($id, $secret)];
$oauth = new OAuthManager($db, 'https://bar.net/auth/callback', $providers, new APCu());

$url = $oauth->getAuthorizationURL('google');
if ($url !== null)
	header('Location: ' . $url);
```

Complete the login on the callback URL:
```php
$state = HTTPContext::getQueryDataValue('state');
$code = HTTPContext::getQueryDataValue('code');

if (!\is_string($state) || !\is_string($code))
	return redirect('/login?error=oauth');

$result = $oauth->callback($state, $code);
if ($result->status !== OAuthStatus::SUCCESS)
	return redirect('/login?error=oauth');

if ($result->registered)
	saveProfile($result->userID, $result->claims->given_name ?? '', $result->claims->family_name ?? '');

$sessions->start($result->userID);
```

Link an identity to the account of the logged in user:
```php
$url = $oauth->getLinkURL('google', $session->userID);
if ($url !== null)
	header('Location: ' . $url);

// on the callback URL
$result = $oauth->callback($state, $code, $sessions->getCurrent()?->userID);
if ($result->status === OAuthStatus::SUCCESS && $result->linked)
	return redirect('/account');
```

Remove an identity:
```php
if (!$oauth->unlink($session->userID, 'google'))
	return ['error' => 'Cannot remove the last login method'];
```

### Login status
status | description
--- | ---
`SUCCESS` | The login or link is complete. `userID` and `claims` are set. `registered` is true for a new account. `linked` is true for a link.
`INVALID_STATE` | The state token does not exist, was used or expired, or the user ID of a link does not match.
`UNKNOWN_PROVIDER` | The provider of the state token is not configured.
`TOKEN_EXCHANGE_FAILED` | The token endpoint did not return an ID token.
`INVALID_ID_TOKEN` | The ID token is malformed, or the signature, nonce, issuer, audience or time claims are not valid.
`MISSING_EMAIL` | The provider did not give an e-mail address.
`EMAIL_NOT_VERIFIED` | The provider does not mark the e-mail address as verified.
`EMAIL_IN_USE` | A different account uses the e-mail address.
`ACCOUNT_NOT_FOUND` | The account of a link does not exist.
`ACCOUNT_DISABLED` | The account has the `ACCOUNT_DISABLED` flag.
`IDENTITY_IN_USE` | The identity of a link is linked to a different account.
___
### Functions
##### > __construct() : `void`
OAuthManager constructor.

parameter | type | description
--- | --- | ---
`$db` | `Generic` | Database that contains the user tables.
`$callbackURL` | `string` | URL that the provider sends the user back to. It must match the URL registered with the provider.
`$providers` | `OAuthProvider[]` | Providers that users can log in with.
`$cache` | `IDataCache|null` | Cache for the signing keys of the providers. **Default: null**.
##### > getProviders() : `string[]`
Get the names of the providers.
##### > getAuthorizationURL() : `string|null`
Start a login. Send the user to the returned URL. Returns null if the provider is unknown. This also deletes expired state tokens.

parameter | type | description
--- | --- | ---
`$provider` | `string` | Name of the provider.
##### > getLinkURL() : `string|null`
Start a link of an identity to an account. Send the user to the returned URL. Returns null if the provider is unknown.

parameter | type | description
--- | --- | ---
`$provider` | `string` | Name of the provider.
`$userID` | `int` | ID of the logged in user account.
##### > getLinkedProviders() : `string[]`
Get the names of the providers that have an identity linked to an account.

parameter | type | description
--- | --- | ---
`$userID` | `int` | ID of the user account.
##### > unlink() : `bool`
Remove the identities of a provider from an account. Returns false if no identity was removed, or if the identity is the last identity of an `OAUTH_ACCOUNT` account.

parameter | type | description
--- | --- | ---
`$userID` | `int` | ID of the user account.
`$provider` | `string` | Name of the provider.
##### > callback() : `OAuthResult`
Complete a login or link with the `state` and `code` that the provider sent to the callback URL. This does not start a session. The result has a `status` (`OAuthStatus`), a `userID`, the `claims` of the ID token, `registered` and `linked`.

parameter | type | description
--- | --- | ---
`$state` | `string` | State parameter of the callback.
`$code` | `string` | Code parameter of the callback.
`$userID` | `int|null` | ID of the logged in user account. A link completes only if this is the user ID given to `getLinkURL()`. **Default: null**.
