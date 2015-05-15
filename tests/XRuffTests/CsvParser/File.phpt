<?php

namespace XRuff\Tests\Utils\CsvParser;

use Tester;
use XRuff\Utils\Csv;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @author Pavel Lauko <info@webengine.cz
 */
class File extends Tester\TestCase
{

	public function testNoFile()
	{
		$file = new Csv\File(new Csv\Configuration());

		Tester\Assert::exception(function() use ($file) {
			$file->open();
		}, 'XRuff\Utils\Csv\ParserException', "Nebyl zadán soubor pro import.");

	}

	public function testEmptyFile()
	{
		$file = new Csv\File(new Csv\Configuration());

		Tester\Assert::exception(function() use ($file) {
			$file->open('../data/test_empty.csv');
		}, 'XRuff\Utils\Csv\ParserException', 'Data neobsahují žádné hodnoty.');

	}

	public function testOpenData()
	{
		$file = new Csv\File(new Csv\Configuration());
		$content = $file->open('../data/test1.csv');
		Tester\Assert::type('resource', $content);
	}

	public function testGetLine()
	{
		$file = new Csv\File(new Csv\Configuration());
		$content = $file->open('../data/test1.csv');
		$line = $file->getLine($content);
		Tester\Assert::type('array', $line);
		Tester\Assert::count(3, $line);
	}

	public function testClose()
	{
		$file = new Csv\File(new Csv\Configuration());
		$content = $file->open('../data/test1.csv');
		Tester\Assert::type('resource', $content);
		$closed = $file->close();
	}

}

\run(new File());