<?php
	require_once(__DIR__ . '/../src/Mailing/RecipientCollection.php');

	use KrameWork\Mailing\RecipientCollection;

	class RecipientCollectionTest extends PHPUnit\Framework\TestCase
	{
		/**
		 * Test that a recipient added without a name compiles to the address only.
		 */
		public function testUnnamedRecipientCompilesToAddressOnly() {
			$recipients = new RecipientCollection();
			$recipients->add('foo@bar.net');

			$this->assertEquals('<foo@bar.net>', (string) $recipients, 'Unnamed recipient did not compile to the address only.');
		}
	}
