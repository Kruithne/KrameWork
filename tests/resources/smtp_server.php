<?php
	// fake SMTP server for SMTPTransportTest; usage: smtp_server.php <transcript> <base64 json: mechanisms, password, rejected>
	$transcript = $argv[1];
	['mechanisms' => $mechanisms, 'password' => $password, 'rejected' => $rejected] = \json_decode(\base64_decode($argv[2]), true);

	$server = \stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
	if ($server === false)
		exit(1);

	$name = \stream_socket_get_name($server, false);
	echo \substr($name, \strrpos($name, ':') + 1) . "\n";
	\flush();

	$client = @\stream_socket_accept($server, 10);
	if ($client === false)
		exit(1);

	\stream_set_timeout($client, 10);

	$reply = function(string $line) use ($client) {
		\fwrite($client, $line . "\r\n");
	};

	$read = function() use ($client, $transcript) {
		$line = \fgets($client);
		if ($line === false)
			exit(0);

		$line = \rtrim($line, "\r\n");
		\file_put_contents($transcript, $line . "\n", \FILE_APPEND);
		return $line;
	};

	$reply('220 fake ESMTP');

	while (true) {
		$line = $read();
		$command = \strtoupper(\substr($line, 0, 4));

		if ($command === 'EHLO') {
			$reply('250-fake');
			if ($mechanisms !== '')
				$reply('250-AUTH ' . $mechanisms);

			$reply('250 8BITMIME');
		} elseif (\str_starts_with($line, 'AUTH PLAIN ')) {
			$credentials = \explode("\0", \base64_decode(\substr($line, 11)));
			$reply(($credentials[2] ?? '') === $password ? '235 OK' : '535 Authentication failed');
		} elseif ($line === 'AUTH LOGIN') {
			$reply('334 VXNlcm5hbWU6');
			$read();
			$reply('334 UGFzc3dvcmQ6');
			$reply(\base64_decode($read()) === $password ? '235 OK' : '535 Authentication failed');
		} elseif ($command === 'MAIL') {
			$reply('250 OK');
		} elseif ($command === 'RCPT') {
			$reply($line === 'RCPT TO:<' . $rejected . '>' ? '550 No such user' : '250 OK');
		} elseif ($command === 'DATA') {
			$reply('354 Send data');
			while ($read() !== '.');
			$reply('250 Queued');
		} elseif ($command === 'QUIT') {
			$reply('221 Bye');
			exit(0);
		} else {
			$reply('500 Unknown command');
		}
	}
