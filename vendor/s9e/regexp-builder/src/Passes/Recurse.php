<?php declare(strict_types=1);

/**
* @package   s9e\RegexpBuilder
* @copyright Copyright (c) 2016-2021 The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\RegexpBuilder\Passes;

use s9e\RegexpBuilder\Runner;

/**
* Enables passes to be run recursively into alternations to replace a(?:x0|x1|y0|y1) with a[xy][01]
*/
class Recurse extends AbstractPass
{
	/**
	* @var Runner
	*/
	protected $runner;

	/**
	* @param Runner $runner
	*/
	public function __construct(Runner $runner)
	{
		$this->runner = $runner;
	}

	/**
	* {@inheritdoc}
	*/
	protected function runPass(array $strings): array
	{
		return array_map([$this, 'recurseString'], $strings);
	}

	/**
	* Recurse into given string and run all passes on each element
	*
	* @param  array $string
	* @return array
	*/
	protected function recurseString(array $string): array
	{
		$isOptional = $this->isOptional;
		foreach ($string as $k => $element)
		{
			if (is_array($element))
			{
				$string[$k] = $this->runner->run($element);
			}
		}
		$this->isOptional = $isOptional;

		return $string;
	}
}