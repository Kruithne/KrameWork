## KrameWork\Users\IUserMailer

***Table of Contents***
* **Overview** - Information about the interface.
* **Examples** - Usage examples.
* **Functions** - Comprehensive list of all functions in the interface.

___
### Overview
`IUserMailer` sends the account mails of the user system. The application implements it, and supplies the content and the transport. [UserManager](UserManager.md) calls it.
___
### Examples
```php
class MyMailer implements IUserMailer
{
	public function __construct(private IMailTransport $transport) {}

	public function sendVerificationCode(int $userID, string $email, string $token, string $code): void {
		$mail = new Mail();
		$mail->setSender('no-reply@bar.net');
		$mail->to->add($email);
		$mail->setSubject('Your verification code');
		$mail->plainContent->setContent('Your code is ' . $code . '. Enter it at https://bar.net/verify?token=' . $token);
		$mail->send($this->transport);
	}

	public function sendPasswordReset(int $userID, string $email, string $token): void {
		// send a link with the token, for example https://bar.net/reset?token=...
	}
}
```
___
### Functions
##### > sendVerificationCode() : `void`
Send a verification code to a user.

parameter | type | description
--- | --- | ---
`$userID` | `int` | ID of the user account.
`$email` | `string` | E-mail address of the user account.
`$token` | `string` | Verification token that identifies the verification.
`$code` | `string` | Eight-digit verification code.
##### > sendPasswordReset() : `void`
Send a password reset token to a user.

parameter | type | description
--- | --- | ---
`$userID` | `int` | ID of the user account.
`$email` | `string` | E-mail address of the user account.
`$token` | `string` | Password reset token.
