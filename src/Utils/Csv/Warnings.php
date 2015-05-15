<?php

namespace XRuff\Utils\Csv;

use Nette\Object;
use Tracy\Debugger;

/**
 * Csv Parser Warning messages
 *
 * @author		Pavel Lauko <info@webengine.cz>
 * @package		Csv
 */
class Warnings extends Object
{
	public static $error_column_name = "Soubor obsahuje špatný název sloupce '%s'.";
	public static $error_column_require = "Povinný sloupec '%s' není k dispozici.";
	public static $error_row_empty = "Řádek '%d' je prázdný.";
	public static $error_column_count = "Počet slopců na řádku %d (%d) neodpovídá počtu sloupců v hlavičce (%d).";
	public static $error_column_empty = "Na řádku %d je hodnota '%d' prázdná, její vyplnění je ale povinné.";
	public static $error_column_type = "Na řádku %d má hodnota '%s' (%s) špatný typ, očekává se %s.";
	public static $error_no_file = "Nebyl zadán soubor pro import.";
	public static $error_no_data = "Data neobsahují žádné hodnoty.";

	/**
	* @param array $translations
	* @return void
	*/
	public static function setTranslations(array $translations)
	{
		foreach ($translations as $name => $value) {
			if (property_exists('XRuff\Utils\Csv\Warnings', $name)) {
				if (trim($value)) {
					self::${$name} = $value;
				}
			}
		}
	}
}
