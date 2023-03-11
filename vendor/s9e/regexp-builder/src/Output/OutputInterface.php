<?php declare(strict_types=1);

/**
* @package   s9e\RegexpBuilder
* @copyright Copyright (c) 2016-2021 The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\RegexpBuilder\Output;

interface OutputInterface
{
	/**
	* Serialize a value into a character
	*
	* @param  integer $value
	* @return string
	*/
	public function output(int $value): string;
}