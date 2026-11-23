<?php
	namespace KrameWork\Timing;

	/**
	 * Class Timer
	 * Cute little tiny timer tool named Tim.
	 *
	 * @package KrameWork\Timing
	 * @author Kruithne (kruithne@gmail.com)
	 */
	class Timer
	{
		const FORMAT_SECONDS = 0x1;
		const FORMAT_MICROSECONDS = 0x2;

		/**
		 * Timer constructor.
		 *
		 * @api __construct
		 * @param int $format Timing format, use Timer::FORMAT_ constants.
		 * @param bool $autoStart Timer will start when constructed.
		 */
		public function __construct(int $format = self::FORMAT_SECONDS, bool $autoStart = false) {
			$this->format = $format;
			if ($autoStart)
				$this->start();
		}

		/**
		 * Start this timer.
		 *
		 * @api start
		 */
		public function start() {
			$this->startTime = $this->getCurrentTime();
			$this->stopTime = null;
		}

		/**
		 * Stop the timer and return the current elapsed time.
		 *
		 * @api stop
		 */
		public function stop() {
			$this->stopTime = $this->getCurrentTime();
			return $this->getElapsed();
		}

		/**
		 * Restart the timer and return the current elapsed time.
		 *
		 * @api restart
		 */
		public function restart() {
			$stop = $this->stop();
			$this->start();
			return $stop;
		}

		/**
		 * Get the elapsed time of this timer.
		 *
		 * @api getElapsed
		 * @return float|int
		 */
		public function getElapsed() {
			if ($this->startTime == null)
				return 0;

			return ($this->stopTime ?? $this->getCurrentTime()) - $this->startTime;
		}

		/**
		 * Returns the timestamp of when this timer started.
		 * If the timer is not started, will return 0.
		 *
		 * @api getStartTimestamp
		 * @return float|int
		 */
		public function getStartTimestamp() {
			return $this->startTime ?? 0;
		}

		/**
		 * Get the formatted result of this timer.
		 *
		 * @api format
		 * @param string $format Format string.
		 * @return string
		 */
		public function format(string $format):string {
			return \sprintf($format, $this->getElapsed());
		}

		/**
		 * Return the elapsed time as a string.
		 *
		 * @api __toString
		 * @return string
		 * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
		 */
		public function __toString():string {
			return (string) $this->getElapsed();
		}

		/**
		 * Get the current timestamp in the timers format.
		 *
		 * @internal
		 * @return int|float
		 */
		private function getCurrentTime() {
			if ($this->format == self::FORMAT_MICROSECONDS)
				return \microtime(true);

			return \time(); // Seconds (or default).
		}

		/**
		 * @var int
		 */
		private $format;

		/**
		 * @var int|float
		 */
		private $startTime;

		/**
		 * @var int|float
		 */
		private $stopTime;
	}