<?php

namespace XRuff\Tests\Utils\CsvParser;

use Tester;
use XRuff\Utils\Csv;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @author Pavel Lauko <info@webengine.cz
 */
class Parser extends Tester\TestCase
{

	public function setUp() {

	}

	public function tearDown() {

	}

	public function testGetConfig()
	{
		$parser = new Csv\Parser();
		Tester\Assert::type('XRuff\Utils\Csv\Configuration', $parser->getConfig());
	}

	public function testSetFile()
	{
		$parser = new Csv\Parser();
		$value = 'file';
		$parser->setFile($value);
		Tester\Assert::equal($parser->getConfig()->file, $value);
	}

	public function testSetSeparator()
	{
		$parser = new Csv\Parser();
		$value = ';';
		$parser->setSeparator($value);
		Tester\Assert::equal($parser->getConfig()->separator, $value);
	}

	public function testSetMap()
	{
		$parser = new Csv\Parser();
		$value = array();
		$parser->setMap($value);
		Tester\Assert::equal($parser->getConfig()->map, $value);
	}

	public function testRemoveId()
	{
		$parser = new Csv\Parser();
		Tester\Assert::equal($parser->getConfig()->removeId, false);
		$parser->removeId();
		Tester\Assert::equal($parser->getConfig()->removeId, true);
	}

	public function testSkipHead()
	{
		$parser = new Csv\Parser();
		Tester\Assert::equal($parser->getConfig()->skipHead, false);
		$parser->skipHead();
		Tester\Assert::equal($parser->getConfig()->skipHead, true);
	}

	public function testStopOnEmpty()
	{
		$parser = new Csv\Parser();
		Tester\Assert::equal($parser->getConfig()->stopOnEmpty, false);
		$parser->stopOnEmpty();
		Tester\Assert::equal($parser->getConfig()->stopOnEmpty, true);
	}

	public function testSetEncoding()
	{
		$parser = new Csv\Parser();
		Tester\Assert::null($parser->getConfig()->encodingIn);
		Tester\Assert::null($parser->getConfig()->encodingOut);

		$parser->setEncoding('in', 'out');
		Tester\Assert::equal($parser->getConfig()->encodingIn, 'in');
		Tester\Assert::equal($parser->getConfig()->encodingOut, 'out');
	}

	public function testSetRequired()
	{
		$parser = new Csv\Parser();
		$required = array('Name', 'Email');
		$parser->setRequired($required);
		Tester\Assert::equal($parser->getConfig()->requiredColumns, $required);
	}

	public function testSetColumnsFormat()
	{
		$parser = new Csv\Parser();
		$required = array('Name' => 'string', 'StartDate' => 'date');
		$parser->setColumnsFormat($required);
		Tester\Assert::equal($parser->getConfig()->columnsFormat, $required);
	}

	public function testEmptyRow()
	{
		Tester\Assert::exception(function() {
			$parser = new Csv\Parser();
			$parser
				->setFile('../data/test_empty_row.csv')
				->load();
		}, 'XRuff\Utils\Csv\ParserException', 'Počet slopců na řádku 3 (1) neodpovídá počtu sloupců v hlavičce (3).');

		Tester\Assert::exception(function() {
			$parser = new Csv\Parser();
			$data = $parser
				->setFile('../data/test_empty_row.csv')
				->stopOnEmpty()
				->load();
		}, 'XRuff\Utils\Csv\ParserException', "Řádek '3' je prázdný.");

	}

	public function testLoad()
	{
		$parser = new Csv\Parser();

		$data = $parser
					->setFile('../data/test1.csv')
					->setSeparator(',')
					->setEncoding('cp1250')
					->load();
		Tester\Assert::type('array', $data);
		Tester\Assert::count(3, $data);


		$data = $parser
					->setFile('../data/test1.csv')
					->setSeparator(',')
					->setEncoding('cp1250')
					->skipHead()
					->load();
		Tester\Assert::count(2, $data);

	}

	public function testSetTranslations()
	{
		$parser = new Csv\Parser();

		$testString = 'test string';
		$testString_1 = 'test string 1';

		$parser->setTranslations(array(
			'error_column_name' => $testString,
			'error_row_empty' => $testString_1,
		));

		Tester\Assert::same($testString, Csv\Warnings::$error_column_name);
		Tester\Assert::same($testString_1, Csv\Warnings::$error_row_empty);
	}

}

\run(new Parser());