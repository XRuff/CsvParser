<?php

namespace XRuff\Utils\Csv;

/**
 * Csv Parser Exception
 *
 * @author		Pavel Lauko <info@webengine.cz>
 * @package		Csv
 */
class ParserException extends \Exception
{
	const ERROR_COLUMN_NAMES = 1;
	const ERROR_NO_FILE = 2;
	const ERROR_COLUMN_COUNT = 3;
	const ERROR_NO_DATA = 4;
	const ERROR_COLUMN_EMPTY = 5;
	const ERROR_COLUMN_TYPE = 6;
	const ERROR_COLUMN_REQUIRE = 7;
}
