## KrameWork\Mailing\SMTPTransport

***Table of Contents***
* **Overview** - Information about the class.
* **Examples** - Usage examples.
* **Functions** - Comprehensive list of all functions in the class.

___
### Overview
`SMTPTransport` sends `Mail` instances to an SMTP server. It does not need a local mail agent.

`SMTPTransport` implements `IMailTransport`. To send a mail through a transport, give the transport to `Mail::send()`. If you do not give a transport, `Mail` uses `NativeMailTransport`, which calls the PHP `mail()` function.

The transport keeps the connection open between sends. Call `close()` to send `QUIT` and close the connection. The destructor also calls `close()`.

The transport verifies the TLS certificate of the server.
___
### Examples
Send one mail through an SMTP server with implicit TLS on port 465:
```php
$transport = new SMTPTransport('smtp.bar.net', 465, 'user@bar.net', 'password');

$mail = new Mail();
$mail->setSender('no-reply@bar.net');
$mail->to->add('foo@bar.net');
$mail->send($transport);
```

Use STARTTLS on port 587:
```php
$transport = new SMTPTransport('smtp.bar.net', 587, 'user@bar.net', 'password', SMTPTransport::SECURITY_STARTTLS);
```

Send many mails on one connection:
```php
foreach ($mails as $mail)
	$mail->send($transport);

$transport->close();
```

Send error reports through SMTP with `MailDispatcher`:
```php
$dispatcher = new MailDispatcher(['foo@bar.net' => null], 'errors@bar.net', null, 'Error Report', $transport);
```

### Security
Use one of these constants for the `$security` parameter:

constant | description
--- | ---
`SECURITY_TLS` | Connect with TLS (implicit TLS). Usually port 465. **Default**.
`SECURITY_STARTTLS` | Connect without TLS, then start TLS with `STARTTLS`. Usually port 587.
`SECURITY_NONE` | Do not use TLS. Use this only for a trusted local server.

> **Note**: With `SECURITY_STARTTLS`, the transport throws an `SMTPException` if the server does not offer `STARTTLS`. The transport does not send mail without TLS in this mode.

### Authentication
If you give a user name, the transport authenticates after `EHLO`. It uses `AUTH PLAIN` if the server offers it. If not, it uses `AUTH LOGIN`. If you do not give a user name, the transport does not authenticate.

### Recipients
The transport sends each `to`, `cc` and `bcc` address to the server. It does not write `bcc` addresses to the message headers.

If the server rejects some recipients, the transport sends the mail to the other recipients. Call `getRejectedRecipients()` after `send()` to get the rejected addresses. If the server rejects all recipients, the transport throws an `SMTPException`.

### Errors
The transport throws an `SMTPException` when it cannot connect, when authentication fails, or when the server sends an error response. After an error, the transport closes the connection. The next send opens a new connection.
___
### Functions
##### > __construct() : `void`
SMTPTransport constructor.

parameter | type | description
--- | --- | ---
`$host` | `string` | SMTP server host name.
`$port` | `int` | SMTP server port. **Default: 465**.
`$username` | `string|null` | Authentication user name. Null to skip authentication.
`$password` | `string|null` | Authentication password.
`$security` | `string` | Connection security, one of the `SECURITY_` constants. **Default: `SECURITY_TLS`**.
`$timeout` | `int` | Connection and response timeout in seconds. **Default: 30**.
`$clientName` | `string|null` | Host name sent with `EHLO`. Null to use the local host name.
##### > send() : `void`
Deliver a compiled e-mail. `Mail::send()` calls this function. You do not usually call it directly.

parameter | type | description
--- | --- | ---
`$sender` | `string` | Envelope sender address.
`$to` | `RecipientCollection` | Primary recipients.
`$cc` | `RecipientCollection` | Carbon copy recipients.
`$bcc` | `RecipientCollection` | Blind carbon copy recipients.
`$subject` | `string` | Encoded subject.
`$headers` | `array` | Headers, without To, Cc, Bcc and Subject.
`$body` | `string` | Compiled MIME body.

exception | reason
--- | ---
`SMTPException` | Connection, authentication or delivery failed.
##### > getRejectedRecipients() : `array`
Get the recipients that the SMTP server rejected in the last send.
##### > close() : `void`
Send `QUIT` to the SMTP server and close the connection.
