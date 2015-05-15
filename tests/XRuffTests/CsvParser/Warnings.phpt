<?php

namespace XRuff\Tests\Utils\CsvParser;

use Tester;
use XRuff\Utils\Csv;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @author Pavel Lauko <info@webengine.cz
 */
class Warnings extends Tester\TestCase
{

	public function testProperties()
	{
		Tester\Assert::type('string', Csv\Warnings::$error_column_name);
		Tester\Assert::type('string', Csv\Warnings::$error_column_require);
		Tester\Assert::type('string', Csv\Warnings::$error_row_empty);
		Tester\Assert::type('string', Csv\Warnings::$error_column_count);
		Tester\Assert::type('string', Csv\Warnings::$error_column_empty);
		Tester\Assert::type('string', Csv\Warnings::$error_column_type);
		Tester\Assert::type('string', Csv\Warnings::$error_no_file);
		Tester\Assert::type('string', Csv\Warnings::$error_no_data);
	}

	public function testTranslations()
	{
		$origin = Csv\Warnings::$error_column_name;
		$origin_1 = Csv\Warnings::$error_column_name;
		$testString = 'test string';
		$testString_1 = 'test string 1';

		Csv\Warnings::setTranslations(array(
			'error_column_name' => $testString,
			'error_row_empty' => $testString_1,
		));

		$changed = Csv\Warnings::$error_column_name;
		$changed_1 = Csv\Warnings::$error_row_empty;

		Tester\Assert::same($testString, $changed);
		Tester\Assert::notSame($testString, $origin);

		Tester\Assert::same($testString_1, $changed_1);
		Tester\Assert::notSame($testString_1, $origin_1);

	}

}

\run(new Warnings());