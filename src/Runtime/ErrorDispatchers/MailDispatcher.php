<?php
	namespace KrameWork\Runtime\ErrorDispatchers;

	use KrameWork\Mailing\Mail;
	use KrameWork\Runtime\ErrorReports\IErrorReport;

	require_once(__DIR__ . '/../../Mailing/Mail.php');
	require_once(__DIR__ . '/IErrorDispatcher.php');

	/**
	 * Class MailDispatcher
	 * Dispatches error reports via e-mail.
	 *
	 * @package KrameWork\Runtime\ErrorDispatchers
	 * @author Kruithne <kruithne@gmail.com>
	 */
	class MailDispatcher implements IErrorDispatcher
	{
		/**
		 * MailDispatcher constructor.
		 *
		 * @api __construct
		 * @param array $recipients Recipients to receive this report.
		 * @param string $sender Sender e-mail address.
		 * @param string $senderName Name of the e-mail sender.
		 * @param string|array|callable $subject Subject string or callable generator.
		 */
		public function __construct(array $recipients, string $sender, ?string $senderName = null, string|array|callable $subject = 'Error Report') {
			$this->mail = new Mail();
			$this->mail->to->add($recipients);
			$this->mail->setSender($sender, $senderName, true);

			if (\is_array($subject) || \is_callable($subject))
				$this->subjectGen = $subject;
			else
				$this->mail->setSubject($subject);
		}

		/**
		 * Dispatch an error report.
		 *
		 * @api dispatch
		 * @param IErrorReport|string $report Report to dispatch.
		 * @return bool
		 */
		public function dispatch(IErrorReport|string $report): bool {
			if ($this->subjectGen) {
				$subject = $this->subjectGen;
				if (\is_array($subject))
					$subject = \call_user_func(\count($this->subjectGen) == 1 ? $this->subjectGen[0] : $this->subjectGen, $report);
				elseif (\is_callable($subject))
					$subject = $subject($report);

				$this->mail->setSubject($subject);
			}

			$this->mail->htmlContent->setContent($report);
			$this->mail->send();
			return false;
		}

		/**
		 * @var Mail
		 */
		protected Mail $mail;

		/**
		 * @var callable|array|null
		 */
		protected mixed $subjectGen = null;
	}