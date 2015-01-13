<?php

namespace XRuff\Utils\Csv;

use Nette\Object;
use Tracy\Debugger;
use Nette\Utils\Validators;
use Nette\Utils\Strings;

/**
 * Csv Parser
 *
 * @author		Pavel Lauko <info@webengine.cz>
 * @package		Csv
 */
class Parser extends Object
{
	/** @var string */
	private $separator = ',';

	/** @var string */
	private $file;

	/** @var string */
	private $removeId = false;

	/** @var bool */
	private $skipHead = false;

	/** @var bool */
	private $stopOnEmpty = false;

	/** @var array|null */
	private $map = null;

	/** @var array|null */
	private $requiredColumns = null;

	/** @var array|null */
	private $columnsFormat = null;

	/** @var string */
	private $encodingIn = null;

	/** @var string */
	private $encodingOut = null;

	/** @var array */
	private $head = array();

	/** @var int */
	private $step;

	/**
	* @param string $separator
	* @return Parser
	*/
	public function setSeparator($separator)
	{
		$this->separator = $separator;
		return $this;
	}

	/**
	* @param string $file
	* @return Parser
	*/
	public function setFile($file)
	{
		$this->file = $file;
		return $this;
	}

	/**
	* @param array $map
	* @return Parser
	*/
	public function setMap(array $map) {
		$this->map = $map;
		return $this;
	}

	/**
	* @return Parser
	*/
	public function removeId()
	{
		$this->removeId = TRUE;
		return $this;
	}

	/**
	* @return Parser
	*/
	public function skipHead()
	{
		$this->skipHead = TRUE;
		return $this;
	}

	/**
	* @return Parser
	*/
	public function stopOnEmpty()
	{
		$this->stopOnEmpty = TRUE;
		return $this;
	}

	/**
	* @param array $requiredColumns
	* @return Parser
	*/
	public function setRequired(array $requiredColumns)
	{
		$this->requiredColumns = $requiredColumns;
		return $this;
	}

	/**
	* @param array $columnsFormat
	* @return Parser
	*/
	public function setColumnsFormat(array $columnsFormat)
	{
		$this->columnsFormat = $columnsFormat;
		return $this;
	}

	/**
	* @param string $fileName
	*/
	public function fopen($fileName)
	{
		$fc = file_get_contents($fileName); // iconv('windows-1250', 'utf-8', file_get_contents($fileName));
		if (trim($fc) == '') {
			throw new ParserException(
				"Data neobsahují žádné hodnoty.",
				ParserException::ERROR_NO_DATA
			);
		}
		$handle = fopen("php://memory", "rw");
		fwrite($handle, $fc);
		fseek($handle, 0);
		return $handle;
	}

	/**
	* @param array $line
	*
	* @throws ParserException
	*
	* @return string $columnName
	*/
	private function setHead($line)
	{
		$this->head = $line;
		if ($this->requiredColumns) {
			foreach ($this->requiredColumns as $key => $value) {
				if (!in_array($value, $this->head)) {
					throw new ParserException(
						"Povinný sloupec '" . $value . "' není k dispozici.",
						ParserException::ERROR_COLUMN_REQUIRE
					);
				}
			}
		}

		return $this;
	}

	/**
	* @param string $key
	* @param string $value
	*
	* @throws ParserException
	*
	* @return void
	*/
	private function checkRequiredColumn($key, $value)
	{
		if ($this->requiredColumns && in_array($this->head[$key], $this->requiredColumns)) {
			if (trim($value) == '') {
				throw new ParserException(
					"Na řádku " . ($this->step + 1) . " je hodnota '" . $this->head[$key] . "' prázdná, její vyplnění je ale povinné.",
					ParserException::ERROR_COLUMN_EMPTY
				);
			}
		}
	}

	/**
	* @param array $line
	*
	* @throws ParserException
	*
	* @return void
	*/
	private function checkColumnsCount($line)
	{
		if ($line[0] == null) {
			if ($this->stopOnEmpty) {
				throw new ParserException(
					"Řádek " . ($this->step + 1) . " je prázdný.",
					ParserException::ERROR_COLUMN_COUNT
				);
			} else {
				continue;
			}
		}

		throw new ParserException(
			"Počet slopců na řádku " . ($this->step + 1) . " (" . count($line) . ") neodpovídá počtu sloupců v hlavičce (" . count($this->head) . ").",
			ParserException::ERROR_COLUMN_COUNT
		);
	}

	/**
	* @param string $key
	*
	* @throws ParserException
	*
	* @return string $columnName
	*/
	private function getColumnName($key)
	{
		if ($this->map) {
			if (array_key_exists($this->head[$key], $this->map)) {
				$columnName = $this->map[$this->head[$key]];
			} else {
				throw new ParserException(
					"Soubor obsahuje špatný název sloupce '" . $this->head[$key] . "'.",
					ParserException::ERROR_COLUMN_NAMES
				);
			}
		} else {
			$columnName = $this->head[$key];
		}

		return $columnName;
	}

	/**
	* @param string $key
	* @param string $value
	*
	* @throws ParserException
	*
	* @return bool
	*/
	private function checkFormat($key, $value)
	{
		if ($this->columnsFormat && array_key_exists($this->head[$key], $this->columnsFormat) && $this->step > 0) {
			$format = $this->columnsFormat[$this->head[$key]];

			if (!$this->checkType($value, $format)) {
				throw new ParserException(
					"Na řádku " . ($this->step + 1) . " má hodnota '" . $this->head[$key] . "' (" . $value . ") špatný typ, očekává se " . $format . ".",
					ParserException::ERROR_COLUMN_TYPE
				);
			}
		}
	}

	/**
	* @param string $value
	* @param string $format
	*
	* @return bool
	*/
	private function checkType($value, $format)
	{
		if ($format == 'date') {
			return $this->isValidDateString($value);
		}

		return Validators::is($value, $format);
	}

	/**
	* @param string $dateString
	*
	* @return bool
	*/
	private function isValidDateString($dateString)
	{
		list($date, $year, $month, $day) = Strings::match($dateString, '~^(\d{4})-(\d{2})-(\d{2})\z~');
		return checkdate($month, $day, $year);
	}

	/**
	* @throws ParserException
	*
	* @return array $rows
	*/
	public function load()
	{
		if (!$this->file) {
			throw new ParserException(
				"Nebyl zadán soubor pro import.",
				ParserException::ERROR_NO_FILE
			);
		}

		$file = $this->fopen($this->file, "r");
		$rows = array();
		$afterHeader = true;
		$this->step = 0;
		while (($line = fgetcsv($file, 0, $this->separator)) !== false) {

			if ($afterHeader) {

				if (sizeof($rows) < 1) {
					// prepare head of file with column names
					$this->setHead($line);
				}

				if (count($line) != count($this->head)) {
					$this->checkColumnsCount($line);
				}

				$array = array();
				foreach ($line as $key => $value) {

					$this->checkRequiredColumn($key, $value);

					// check column content format
					$this->checkFormat($key, $value);

					// get canonical column name
					$columnName = $this->getColumnName($key);

					if ($this->removeId && $columnName == 'id') {
						continue;
					}

					$array[(string) $columnName] = $value;
				}
				$rows[] = $array;
				$this->step++;
			}

			if ($this->stopOnEmpty) {
				// skip file until empty line
				if ($line[0] == null) {
					$afterHeader = false;
				}
			}
		}

		fclose($file);
		if ($this->skipHead) {
			unset($rows[0]);
		}
		return $rows;
	}

}
