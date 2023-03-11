<?php declare(strict_types=1);

/**
* @package   s9e\RegexpBuilder
* @copyright Copyright (c) 2016-2021 The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\RegexpBuilder\Output;

class PHP extends PrintableAscii
{
	/** {@inheritdoc} */
	protected $maxValue = 0x10FFFF;

	/**
	* {@inheritdoc}
	*/
	protected function escapeUnicode(int $cp): string
	{
		return sprintf('\\x{%04' . $this->hexCase . '}', $cp);
	}
}