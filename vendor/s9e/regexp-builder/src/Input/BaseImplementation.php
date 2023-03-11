<?php declare(strict_types=1);

/**
* @package   s9e\RegexpBuilder
* @copyright Copyright (c) 2016-2021 The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\RegexpBuilder\Input;

abstract class BaseImplementation implements InputInterface
{
	/**
	* {@inheritdoc}
	*/
	public function __construct(array $options = [])
	{
	}

	/**
	* {@inheritdoc}
	*/
	abstract public function split(string $string): array;
}