<?php
	namespace KrameWork\Users\OAuth;

	/**
	 * Class OAuthProvider
	 * Configuration of an OpenID Connect provider for OAuth logins.
	 *
	 * @package KrameWork\Users\OAuth
	 */
	class OAuthProvider
	{
		/**
		 * OAuthProvider constructor.
		 *
		 * @api __construct
		 * @param string $name Unique name of the provider, for example "google".
		 * @param string $clientID Client ID given by the provider.
		 * @param string $clientSecret Client secret given by the provider.
		 * @param string $authEndpoint Authorization endpoint URL.
		 * @param string $tokenEndpoint Token endpoint URL.
		 * @param string $jwksURI URL of the JWKS that contains the signing keys of ID tokens.
		 * @param string[] $issuers Accepted issuers of ID tokens. "{tenantid}" matches the tid claim of the token if it is a GUID.
		 * @param string[] $scopes Scopes to request.
		 * @param string|null $userInfoEndpoint UserInfo endpoint URL. Used when the ID token has no e-mail address.
		 * @param string|null $emailVerifiedClaim Claim that must be true to accept the e-mail address. Null to trust the e-mail address without a check.
		 */
		public function __construct(
			public readonly string $name,
			public readonly string $clientID,
			public readonly string $clientSecret,
			public readonly string $authEndpoint,
			public readonly string $tokenEndpoint,
			public readonly string $jwksURI,
			public readonly array $issuers,
			public readonly array $scopes = ['openid', 'email', 'profile'],
			public readonly ?string $userInfoEndpoint = null,
			public readonly ?string $emailVerifiedClaim = 'email_verified'
		) {}

		/**
		 * Create the configuration for Google.
		 *
		 * @api google
		 * @param string $clientID Client ID given by Google.
		 * @param string $clientSecret Client secret given by Google.
		 * @return OAuthProvider
		 */
		public static function google(string $clientID, string $clientSecret): OAuthProvider {
			return new OAuthProvider(
				'google',
				$clientID,
				$clientSecret,
				'https://accounts.google.com/o/oauth2/v2/auth',
				'https://oauth2.googleapis.com/token',
				'https://www.googleapis.com/oauth2/v3/certs',
				['https://accounts.google.com', 'accounts.google.com'],
				userInfoEndpoint: 'https://openidconnect.googleapis.com/v1/userinfo'
			);
		}

		/**
		 * Create the configuration for Microsoft.
		 * For a single tenant, give the tenant ID, not the domain name.
		 * Add the optional xms_edov claim to the ID token in the app registration. Logins without this claim fail.
		 *
		 * @api microsoft
		 * @param string $clientID Application (client) ID given by Microsoft.
		 * @param string $clientSecret Client secret given by Microsoft.
		 * @param string $tenant "common", "organizations", "consumers" or a tenant ID.
		 * @return OAuthProvider
		 */
		public static function microsoft(string $clientID, string $clientSecret, string $tenant = 'common'): OAuthProvider {
			$issuerTenant = \in_array($tenant, ['common', 'organizations', 'consumers'], true) ? '{tenantid}' : $tenant;

			return new OAuthProvider(
				'microsoft',
				$clientID,
				$clientSecret,
				'https://login.microsoftonline.com/' . $tenant . '/oauth2/v2.0/authorize',
				'https://login.microsoftonline.com/' . $tenant . '/oauth2/v2.0/token',
				'https://login.microsoftonline.com/' . $tenant . '/discovery/v2.0/keys',
				['https://login.microsoftonline.com/' . $issuerTenant . '/v2.0'],
				userInfoEndpoint: 'https://graph.microsoft.com/oidc/userinfo',
				emailVerifiedClaim: 'xms_edov'
			);
		}
	}
