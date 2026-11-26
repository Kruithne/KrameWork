## KrameWork\Users\OAuth\OAuthProvider

***Table of Contents***
* **Overview** - Information about the class.
* **Examples** - Usage examples.
* **Functions** - Comprehensive list of all functions in the class.

___
### Overview
`OAuthProvider` is the configuration of an OpenID Connect provider. Give providers to [OAuthManager](OAuthManager.md).

`google()` and `microsoft()` create the configuration for Google and Microsoft. For other providers, use the constructor.

In an issuer, `{tenantid}` matches the `tid` claim of the ID token. Microsoft uses this for the `common`, `organizations` and `consumers` tenants.

The login accepts an e-mail address only if the claim `emailVerifiedClaim` is `true`. The default is `email_verified`. Set `emailVerifiedClaim` to null only for a provider that always verifies the e-mail address.

Microsoft does not send `email_verified`. A tenant admin can set the `email` claim to any address. `microsoft()` uses the `xms_edov` claim, which is true when Microsoft verified the domain of the address. `xms_edov` is an optional claim. Add it to the ID token in the app registration (**Token configuration** > **Add optional claim**). Without it, each Microsoft login fails with `EMAIL_NOT_VERIFIED`.
___
### Examples
```php
$google = OAuthProvider::google('client-id', 'client-secret');
$microsoft = OAuthProvider::microsoft('client-id', 'client-secret');
```

Use a single Microsoft tenant. Give the tenant ID, not the domain name:
```php
$microsoft = OAuthProvider::microsoft('client-id', 'client-secret', '00000000-0000-0000-0000-000000000000');
```
___
### Functions
##### > __construct() : `void`
OAuthProvider constructor.

parameter | type | description
--- | --- | ---
`$name` | `string` | Unique name of the provider, for example `google`.
`$clientID` | `string` | Client ID given by the provider.
`$clientSecret` | `string` | Client secret given by the provider.
`$authEndpoint` | `string` | Authorization endpoint URL.
`$tokenEndpoint` | `string` | Token endpoint URL.
`$jwksURI` | `string` | URL of the JWKS that contains the signing keys of ID tokens.
`$issuers` | `string[]` | Accepted issuers of ID tokens.
`$scopes` | `string[]` | Scopes to request. **Default: `openid`, `email`, `profile`**.
`$userInfoEndpoint` | `string|null` | UserInfo endpoint URL. Used when the ID token has no e-mail address. **Default: null**.
`$emailVerifiedClaim` | `string|null` | Claim that must be `true` to accept the e-mail address. Null to trust the e-mail address without a check. **Default: `email_verified`**.
##### > google() : `OAuthProvider`
Create the configuration for Google.

parameter | type | description
--- | --- | ---
`$clientID` | `string` | Client ID given by Google.
`$clientSecret` | `string` | Client secret given by Google.
##### > microsoft() : `OAuthProvider`
Create the configuration for Microsoft. Add the optional `xms_edov` claim to the ID token in the app registration. Logins without this claim fail.

parameter | type | description
--- | --- | ---
`$clientID` | `string` | Application (client) ID given by Microsoft.
`$clientSecret` | `string` | Client secret given by Microsoft.
`$tenant` | `string` | `common`, `organizations`, `consumers` or a tenant ID. **Default: `common`**.
