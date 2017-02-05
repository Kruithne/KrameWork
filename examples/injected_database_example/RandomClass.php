<?php
	class RandomClass
	{
		public function __construct(TestDatabaseConnection $connection)
		{
			$statement = $connection->prepare('SELECT test_column FROM test_table')->execute();
			$rows = $statement->getRows();

			if (count($rows))
				echo "RandomClass runs query and gets: " . $rows[0]->test_column;
		}
	}
