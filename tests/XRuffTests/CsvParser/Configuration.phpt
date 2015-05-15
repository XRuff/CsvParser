<?php

namespace XRuff\Tests\Utils\CsvParser;

use Tester;
use XRuff\Utils\Csv;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @author Pavel Lauko <info@webengine.cz
 */
class Cofiguration extends Tester\TestCase
{

	public function testRead()
	{
		$config = new Csv\Configuration();
		Tester\Assert::equal($config->separator, ',');
		Tester\Assert::equal($config->map, null);
		Tester\Assert::equal($config->file, null);
		Tester\Assert::equal($config->removeId, false);
		Tester\Assert::equal($config->skipHead, false);
		Tester\Assert::equal($config->stopOnEmpty, false);
		Tester\Assert::equal($config->requiredColumns, null);
		Tester\Assert::equal($config->columnsFormat, null);
		Tester\Assert::equal($config->encodingIn, null);
		Tester\Assert::equal($config->encodingOut, null);
	}

}

\run(new Cofiguration());