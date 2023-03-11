<?php declare(strict_types=1);

/**
* @package   s9e\RegexpBuilder
* @copyright Copyright (c) 2016-2021 The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\RegexpBuilder\Output;

use InvalidArgumentException;

abstract class BaseImplementation implements OutputInterface
{
	/**
	* @var integer
	*/
	protected $maxValue = 0;

	/**
	* @var integer
	*/
	protected $minValue = 0;

	/**
	* @param array $options
	*/
	public function __construct(array $options = [])
	{
	}

	/**
	* {@inheritdoc}
	*/
	public function output(int $value): string
	{
		$this->validate($value);

		return $this->outputValidValue($value);
	}

	/**
	* Validate given value
	*
	* @param  integer $value
	* @return void
	*/
	protected function validate(int $value): void
	{
		if ($value < $this->minValue || $value > $this->maxValue)
		{
			throw new InvalidArgumentException('Value ' . $value . ' is out of bounds (' . $this->minValue . '..' . $this->maxValue . ')');
		}
	}

	/**
	* Serialize a valid value into a character
	*
	* @param  integer $value
	* @return string
	*/
	abstract protected function outputValidValue(int $value): string;
}