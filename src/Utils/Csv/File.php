<?php

namespace XRuff\Utils\Csv;

use Nette\Object;

/**
 * Csv Parser File
 *
 * @author		Pavel Lauko <info@webengine.cz>
 * @package		Csv
 */
class File extends Object
{
	/** @var Configuration */
	private $config;

	/** @var */
	private $handle;

	public function __construct(Configuration $config) {
		$this->config = $config;
	}

	/**
	 * @param string $file
	 * @param string $method
	 * @return resource
	 */
	public function open($file = null, $method = 'rw')
	{
		if (!$file) {
			$file = $this->config->file;
		}

		if (!$file) {
			throw new ParserException(
				Warnings::$error_no_file,
				ParserException::ERROR_NO_FILE
			);
		}

		$fc = file_get_contents($file);

		if ($this->config->encodingIn) {
			$fc = iconv($this->config->encodingIn, $this->config->encodingOut, $fc);
		}

		if (trim($fc) === '') {
			throw new ParserException(
				Warnings::$error_no_data,
				ParserException::ERROR_NO_DATA
			);
		}
		$this->handle = fopen('php://memory', $method);
		fwrite($this->handle, $fc);
		fseek($this->handle, 0);
		return $this->handle;
	}

	public function close() {
		fclose($this->handle);
	}

	/**
	 * @param string $content
	 * @return array
	 */
	public function getLine($content) {
		return fgetcsv($content, 0, $this->config->separator);
	}

}
