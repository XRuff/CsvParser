<?php

namespace XRuff\Utils\Csv;

use Nette\Object;

/**
 * Csv Parser Configuration
 *
 * @author		Pavel Lauko <info@webengine.cz>
 * @package		Csv
 */
class Configuration extends Object
{

	/** @var string */
	public $separator = ',';

	/** @var string */
	public $file;

	/** @var array|null */
	public $map = null;

	/** @var string */
	public $removeId = false;

	/** @var bool */
	public $skipHead = false;

	/** @var bool */
	public $stopOnEmpty = false;

	/** @var array|null */
	public $requiredColumns = null;

	/** @var array|null */
	public $columnsFormat = null;

	/** @var string */
	public $encodingIn = null;

	/** @var string */
	public $encodingOut = null;

}
