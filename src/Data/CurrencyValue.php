<?php
	namespace KrameWork\Data;
	require_once (__DIR__.'/DecimalValue.php');
	require_once (__DIR__.'/Currency.php');

	/**
	 * Class CurrencyValue
	 * A currency value
	 * @author docpify <morten@runsafe.no>
	 */
	class CurrencyValue extends DecimalValue
	{
		public static string $decimalSeparator = '.';
		public static string $thousandsSeparator = ',';

		protected string $currency;

		public function __construct(mixed $value, ?string $currency = null) {
			parent::__construct($value);
			$this->currency = Currency::normalize($currency ?? Currency::DEFAULT_CODE);
		}

		public function currency(): string {
			return $this->currency;
		}

		public function json(): array {
			return ['amount' => $this->value, 'currency' => $this->currency];
		}

		public function compare(mixed $to): int|float {
			if ($to instanceof CurrencyValue && $this->currency !== $to->currency)
				return \strcmp($this->currency, $to->currency);

			return parent::compare($to);
		}

		public function __toString(): string {
			$amount = $this->format(Currency::decimals($this->currency));
			if ($amount === '')
				return $amount;

			return $amount . ' ' . $this->currency;
		}
	}
