<?php declare(strict_types=1);

/**
* @package   s9e\RegexpBuilder
* @copyright Copyright (c) 2016-2021 The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\RegexpBuilder\Input;

interface InputInterface
{
	/**
	* @param array $options
	*/
	public function __construct(array $options = []);

	/**
	* Split given string into a list of values
	*
	* @param  string    $string
	* @return integer[]
	*/
	public function split(string $string): array;
}