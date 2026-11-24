<?php
	namespace KrameWork\Runtime\ErrorDispatchers;

	use KrameWork\Caching\IDataCache;
	use KrameWork\Runtime\ErrorHandler;
	use KrameWork\Runtime\ErrorReports\IErrorReport;

	require_once(__DIR__ . '/../../Caching/IDataCache.php');
	require_once(__DIR__ . '/../ErrorHandler.php');
	require_once(__DIR__ . '/IErrorDispatcher.php');

	class GitHubDispatcher implements IErrorDispatcher
	{
		const API_URL = 'https://api.github.com/repos/%s/issues';
		const API_VERSION = '2022-11-28';
		const MAX_BODY_LENGTH = 65536;
		const TRUNCATED_NOTICE = "\n\n_Report truncated._";
		const REQUEST_TIMEOUT = 5;
		const CACHE_PREFIX = 'kw_github_dispatch_';

		/**
		 * GitHubDispatcher constructor.
		 *
		 * @api __construct
		 * @param string $token GitHub personal access token.
		 * @param string $repository Target repository, in the format owner/repository.
		 * @param string[] $labels Labels to add to raised issues.
		 * @param int $throttle Seconds before a report with the same title can be raised again.
		 * @param IDataCache|null $cache Cache to store throttle data in.
		 * @param (callable(IErrorReport|string): string)|null $title Title generator, called with the report.
		 */
		public function __construct(protected string $token, protected string $repository, protected array $labels = [], protected int $throttle = 86400, protected ?IDataCache $cache = null, ?callable $title = null) {
			$this->title = $title === null ? null : \Closure::fromCallable($title);
		}

		public function dispatch(IErrorReport|string $report): bool {
			ErrorHandler::suspend();

			try {
				$title = ErrorHandler::sanitize($this->getTitle($report));
				if (!$this->isThrottled($title))
					$this->createIssue($title, ErrorHandler::sanitize($this->getBody($report)));
			} catch (\Throwable) {
			} finally {
				ErrorHandler::resume();
			}

			return false;
		}

		private function getTitle(IErrorReport|string $report): string {
			if ($this->title !== null)
				return ($this->title)($report);

			if (!($report instanceof IErrorReport))
				return 'Error Report';

			$error = $report->getError();
			return \sprintf('%s: %s in %s:%d', $error->getPrefix(), $error->getName(), \basename($error->getFile()), $error->getLine());
		}

		private function getBody(IErrorReport|string $report): string {
			$body = (string) $report;

			if (!($report instanceof IErrorReport) || !\str_starts_with($report->getContentType(), 'text/markdown')) {
				$fence = '```';
				while (\str_contains($body, $fence))
					$fence .= '`';

				$body = $fence . "\n" . $body . "\n" . $fence;
			}

			if (\strlen($body) > self::MAX_BODY_LENGTH)
				$body = \substr($body, 0, self::MAX_BODY_LENGTH - \strlen(self::TRUNCATED_NOTICE)) . self::TRUNCATED_NOTICE;

			return $body;
		}

		private function createIssue(string $title, string $body): void {
			$payload = ['title' => $title, 'body' => $body];
			if ($this->labels)
				$payload['labels'] = $this->labels;

			$context = \stream_context_create(['http' => [
				'method' => 'POST',
				'header' => \implode("\r\n", [
					'Authorization: Bearer ' . $this->token,
					'Accept: application/vnd.github+json',
					'Content-Type: application/json',
					'User-Agent: KrameWork',
					'X-GitHub-Api-Version: ' . self::API_VERSION
				]),
				'content' => \json_encode($payload, \JSON_INVALID_UTF8_SUBSTITUTE),
				'timeout' => self::REQUEST_TIMEOUT,
				'ignore_errors' => true
			]]);

			\file_get_contents(\sprintf(self::API_URL, $this->repository), false, $context);
		}

		private function isThrottled(string $title): bool {
			if ($this->throttle <= 0)
				return false;

			$key = \md5($this->repository . "\0" . $title);
			return $this->cache !== null ? $this->isCacheThrottled($key) : $this->isFileThrottled($key);
		}

		private function isCacheThrottled(string $key): bool {
			$key = self::CACHE_PREFIX . $key;
			if ($this->cache->exists($key))
				return true;

			$this->cache->store($key, \time(), $this->throttle);
			return false;
		}

		private function isFileThrottled(string $key): bool {
			$path = \sys_get_temp_dir() . \DIRECTORY_SEPARATOR . self::CACHE_PREFIX . \md5($this->repository) . '.json';
			$handle = \fopen($path, 'c+');
			if ($handle === false)
				return false;

			\flock($handle, \LOCK_EX);

			$now = \time();
			$entries = \json_decode(\stream_get_contents($handle), true);
			$entries = \array_filter(\is_array($entries) ? $entries : [], fn($time) => \is_int($time) && $now - $time < $this->throttle);

			$throttled = isset($entries[$key]);
			if (!$throttled)
				$entries[$key] = $now;

			\ftruncate($handle, 0);
			\rewind($handle);
			\fwrite($handle, \json_encode($entries));
			\fflush($handle);
			\flock($handle, \LOCK_UN);
			\fclose($handle);

			return $throttled;
		}

		/**
		 * @var (\Closure(IErrorReport|string): string)|null
		 */
		protected ?\Closure $title = null;
	}
