<?php

namespace XRuff\Utils\Csv;

use Nette\Object;
use Nette\Utils\Strings;
use Nette\Utils\Validators;

/**
 * Csv Parser
 *
 * @author		Pavel Lauko <info@webengine.cz>
 * @package		Csv
 */
class Parser extends Object
{

	/** @var array */
	private $head = [];

	/** @var int */
	private $step;

	/** @var int */
	private $config = null;

	/**
	 * @return Configuration
	 */
	public function getConfig()
	{
		if (!$this->config) {
			$this->config = new Configuration();
		};
		return $this->config;
	}

	/**
	 * @param string $separator
	 * @return Parser
	 */
	public function setSeparator($separator)
	{
		$this->getConfig()->separator = $separator;
		return $this;
	}

	/**
	 * @param string $file
	 * @return Parser
	 */
	public function setFile($file)
	{
		$this->getConfig()->file = $file;
		return $this;
	}

	/**
	 * @param array $map
	 * @return Parser
	 */
	public function setMap(array $map) {
		$this->getConfig()->map = $map;
		return $this;
	}

	/**
	 * @return Parser
	 */
	public function removeId()
	{
		$this->getConfig()->removeId = true;
		return $this;
	}

	/**
	 * @return Parser
	 */
	public function skipHead()
	{
		$this->getConfig()->skipHead = true;
		return $this;
	}

	/**
	 * @return Parser
	 */
	public function noHead($noHead = true)
	{
		$this->getConfig()->noHead = $noHead;
		return $this;
	}


	/**
	 * @return Parser
	 */
	public function stopOnEmpty()
	{
		$this->getConfig()->stopOnEmpty = true;
		return $this;
	}

	/**
	 * @param array $requiredColumns
	 * @return Parser
	 */
	public function setRequired(array $requiredColumns)
	{
		$this->getConfig()->requiredColumns = $requiredColumns;
		return $this;
	}

	/**
	 * @param string $in
	 * @param string $out
	 * @return Parser
	 */
	public function setEncoding($in, $out = 'utf-8')
	{
		$this->getConfig()->encodingIn = $in;
		$this->getConfig()->encodingOut = $out;
		return $this;
	}

	/**
	 * @param array $columnsFormat
	 * @return Parser
	 */
	public function setColumnsFormat(array $columnsFormat)
	{
		$this->getConfig()->columnsFormat = $columnsFormat;
		return $this;
	}

	/**
	 * @param array $translations
	 * @return Parser
	 */
	public function setTranslations(array $translations)
	{
		Warnings::setTranslations($translations);
		return $this;
	}

	/**
	 * @param array $line
	 *
	 * @throws ParserException
	 *
	 * @return Parser
	 */
	private function setHead($line)
	{
		$this->head = $line;

		if ($this->getConfig()->noHead) {
			return $this;
		}

		if ($this->getConfig()->requiredColumns) {
			foreach ($this->getConfig()->requiredColumns as $key => $value) {
				if (!in_array($value, $this->head)) {
					throw new ParserException(
						sprintf(Warnings::$error_column_require, $value),
						ParserException::ERROR_COLUMN_REQUIRE
					);
				}
			}
		} else {
			$this->head = [];
			foreach ($line as $key => $value) {
				$this->head[$key] = $key;
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
		if ($this->getConfig()->requiredColumns && in_array($this->head[$key], $this->getConfig()->requiredColumns)) {
			if (trim($value) == '') {
				throw new ParserException(
					sprintf(Warnings::$error_column_empty, ($this->step + 1), $this->head[$key]),
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
			if ($this->getConfig()->stopOnEmpty) {
				throw new ParserException(
					sprintf(Warnings::$error_row_empty, ($this->step + 1)),
					ParserException::ERROR_COLUMN_COUNT
				);
			}
		}

		throw new ParserException(
			sprintf(Warnings::$error_column_count, ($this->step + 1), count($line), count($this->head)),
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
		if ($this->getConfig()->map) {
			if (array_key_exists($this->head[$key], $this->getConfig()->map)) {
				$columnName = $this->getConfig()->map[$this->head[$key]];
			} else {
				throw new ParserException(
					sprintf(Warnings::$error_column_name, $this->head[$key]),
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
	 */
	private function checkFormat($key, $value)
	{
		if ($this->getConfig()->columnsFormat && array_key_exists($this->head[$key], $this->getConfig()->columnsFormat) && $this->step > 0) {
			$format = $this->getConfig()->columnsFormat[$this->head[$key]];

			if (!$this->checkType($value, $format)) {
				throw new ParserException(
					sprintf(Warnings::$error_column_type, ($this->step + 1), $this->head[$key], $value, $format),
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
		if (Strings::contains($dateString, '-')) {
			list($date, $year, $month, $day) = Strings::match($dateString, '~^(\d{4})-(\d{1,22})-(\d{1,22})\z~');
		} elseif (Strings::contains($dateString, '.')) {
			list($date, $day, $month, $year) = Strings::match($dateString, '~^(\d{1,2}).(\d{1,2}).(\d{4})\z~');
		} elseif (Strings::contains($dateString, '/')) {
			list($date, $month, $day, $year) = Strings::match($dateString, '~^(\d{1,2})/(\d{1,2})/(\d{4})\z~');
		} else {
			return false;
		}

		return checkdate($month, $day, $year);
	}

	/**
	 * @throws ParserException
	 *
	 * @return array $rows
	 */
	public function load()
	{
		$file = new File($this->getConfig());
		$content = $file->open();

		$rows = [];
		$afterHeader = true;
		$this->step = 0;
		while (($line = $file->getLine($content)) !== false) {

			if ($afterHeader) {

				if (count($rows) < 1) {
					// prepare head of file with column names
					$this->setHead($line);
				}

				if (count($line) != count($this->head)) {
					$this->checkColumnsCount($line);
				}

				$array = [];
				foreach ($line as $key => $value) {

					$this->checkRequiredColumn($key, $value);

					// check column content format
					$this->checkFormat($key, $value);

					// get canonical column name
					$columnName = $this->getColumnName($key);

					if ($this->getConfig()->removeId && $columnName == 'id') {
						continue;
					}

					$array[(string) $columnName] = $value;
				}
				$rows[] = $array;
				$this->step++;
			}

			if ($this->getConfig()->stopOnEmpty) {
				// skip file until empty line
				if ($line[0] == null) {
					$afterHeader = false;
				}
			}
		}

		$file->close();
		if ($this->getConfig()->skipHead && !$this->getConfig()->noHead) {
			unset($rows[0]);
		}
		return $rows;
	}

}
