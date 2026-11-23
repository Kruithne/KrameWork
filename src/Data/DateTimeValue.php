<?php
	namespace KrameWork\Data;
	require_once (__DIR__.'/DateValue.php');

	/**
	 * Class DateTimeValue
	 * A date and time value
	 * @author docpify <morten@runsafe.no>
	 */
	class DateTimeValue extends DateValue
	{
		/**
		 * @return string The date and time in "31.12.2016 12:38" format
		 */
		public function __toString() {
			return $this->value ? \date('d.m.Y H:i', $this->value) : '';
		}
	}