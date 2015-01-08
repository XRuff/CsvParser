<?php

namespace XRuff\Utils\Csv;

use Nette\Object;
use Tracy\Debugger;

/**
 * Csv Parser
 *
 * @author		Pavel Lauko <info@webengine.cz>
 * @package		Csv
 */
class Parser extends Object
{
	/** @var string */
	public $separator = ',';

	/** @var string */
	public $file;

	/** @var string */
	public $removeId = FALSE;

	/** @var bool */
	public $skipHead = FALSE;

	/** @var array|NULL */
	public $map = NULL;

	/*
	* @param string $separator
	* @return Parser
	*/
	public function setSeparator($separator)
	{
		$this->separator = $separator;
		return $this;
	}

	/*
	* @param string $file
	* @return Parser
	*/
	public function setFile($file)
	{
		$this->file = $file;
		return $this;
	}

	/*
	* @param array $map
	* @return Parser
	*/
	public function setMap(array $map) {
		$this->map = $map;
		return $this;
	}

	/*
	* @return Parser
	*/
	public function removeId()
	{
		$this->removeId = TRUE;
		return $this;
	}

	/*
	* @return Parser
	*/
	public function skipHead()
	{
		$this->skipHead = TRUE;
		return $this;
	}

	/*
	* @param string $fileName
	*/
	public function fopenUtf8($fileName)
	{
		$fc = file_get_contents($fileName); // iconv('windows-1250', 'utf-8', file_get_contents($fileName));
		$handle = fopen("php://memory", "rw");
		fwrite($handle, $fc);
		fseek($handle, 0);
		return $handle;
	}

	/*
	* @return array $rows
	*/
	public function load()
	{

		if (!$this->file) {
			throw new ParserException(
				"Nebyl zadán soubor pro import.",
				ParserException::ERROR_NAME
			);
		}

		$file = $this->fopenUtf8($this->file, "r");
		$rows = array();
		$afterHeader = true;
		$step = 0;
		while (($line = fgetcsv($file, 0, $this->separator)) !== FALSE) {

			if ($afterHeader) {
				if (sizeof($rows) < 1) {
					$head = $line;
				}

				$array = array();
				foreach ($line as $key => $value) {
					if ($this->map) {
						if (array_key_exists($head[$key], $this->map)) {
							$columnName = $this->map[$head[$key]];
						} else {
							throw new ParserException(
								"Soubor obsahuje špatný název sloupce.",
								ParserException::ERROR_NAME
							);
						}
					} else {
						$columnName = $head[$key];
					}

					if ($this->removeId && $columnName == 'id') {
						continue;
					}

					$array[(string) $columnName] = $value;
				}
				$rows[] = $array;
				$step++;
			}

			// skip file until empty line
			if ($line[0] == null)
				$afterHeader = false;
		}

		fclose($file);
		if ($this->skipHead) {
			unset($rows[0]);
		}
		return $rows;
	}
}
