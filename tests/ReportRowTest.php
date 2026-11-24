<?php
	use KrameWork\Data\DateValue;

	require_once(__DIR__.'/../src/Reporting/ReportRow.php');
	require_once(__DIR__.'/../src/Data/DateValue.php');

	class ReportRowTest extends \PHPUnit\Framework\TestCase
	{
		public function testSimpleJsonSerialization() {
			$data = ['test' => 1];
			$row = new \KrameWork\Reporting\ReportRow((object)$data);
			$this->assertEquals($data, $row->jsonSerialize());
		}

		public function testDateValueJsonSerialization() {
			$data = (object)['test' => new DateValue('1980-01-01')];
			$expect = ['test'=> '1980-01-01T00:00:00+00:00'];
			$row = new \KrameWork\Reporting\ReportRow($data);
			$this->assertEquals($expect, $row->jsonSerialize());
		}

		public function testForeachIteration() {
			$data = ['a'=>'test a','b'=>'test b'];
			$expect = join('', $data);
			$row = new \KrameWork\Reporting\ReportRow((object)$data);
			$actual = '';
			foreach ($row as $item)
				$actual .= $item;
			$this->assertEquals($expect, $actual);
		}

		public function testValueRead() {
			$data = (object)['test' => 42];
			$expect = 42;
			$row = new \KrameWork\Reporting\ReportRow($data);
			$actual = $row['test'];
			$this->assertEquals($expect, $actual);
		}

			public function testValueWrite() {
			$data = (object)['test' => 42];
			$expect = 0;
			$row = new \KrameWork\Reporting\ReportRow($data);
			$row['test'] = 0;
			$actual = $row['test'];
			$this->assertEquals($expect, $actual);
		}

		/**
		 * Iterating a row built from an array yields the array values.
		 */
		public function testArrayForeachIteration() {
			$data = ['a' => 'test a', 'b' => 'test b'];
			$expect = join('', $data);
			$row = new \KrameWork\Reporting\ReportRow($data);
			$actual = '';
			foreach ($row as $item)
				$actual .= $item;
			$this->assertEquals($expect, $actual);
		}

		/**
		 * Reading a field from a row built from an array returns the array value.
		 */
		public function testArrayValueRead() {
			$row = new \KrameWork\Reporting\ReportRow(['test' => 42]);
			$this->assertEquals(42, $row['test']);
		}

		/**
		 * Writing a field on a row built from an array updates the value.
		 */
		public function testArrayValueWrite() {
			$row = new \KrameWork\Reporting\ReportRow(['test' => 42]);
			$row['test'] = 0;
			$this->assertEquals(0, $row['test']);
		}

		/**
		 * A field on a row built from an array is reported as set.
		 */
		public function testArrayValueExists() {
			$row = new \KrameWork\Reporting\ReportRow(['test' => 42]);
			$this->assertTrue(isset($row['test']));
		}

		/**
		 * Unsetting a field on a row built from an array removes the value.
		 */
		public function testArrayValueUnset() {
			$row = new \KrameWork\Reporting\ReportRow(['test' => 42]);
			unset($row['test']);
			$this->assertFalse(isset($row['test']));
		}

		/**
		 * Reading a field by index returns the value when the field is named '0'.
		 */
		public function testValueReadByIndexWithZeroKey() {
			$row = new \KrameWork\Reporting\ReportRow((object)['0' => 'zero']);
			$this->assertEquals('zero', $row[0]);
		}
	}