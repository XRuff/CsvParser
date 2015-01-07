<?php

namespace XRuff\Utils\Csv;

use Nette\Object;
use Tracy\Debugger;

/**
 * Csv Parse
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

	public function setSeparator($separator) {
		$this->separator = $separator;
		return $this;
	}

	public function setFile($file) {
		$this->file = $file;
		return $this;
	}

	public function setMap($map) {
		$this->map = $map;
		return $this;
	}

	public function removeId() {
		$this->removeId = TRUE;
		return $this;
	}

	public function skipHead() {
		$this->skipHead = TRUE;
		return $this;
	}

	public function fopenUtf8($fileName) {
		$fc = file_get_contents($fileName); // iconv('windows-1250', 'utf-8', file_get_contents($fileName));
		$handle = fopen("php://memory", "rw");
		fwrite($handle, $fc);
		fseek($handle, 0);
		return $handle;
	}

	public function load() {

		if(!$this->file) return array();

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
