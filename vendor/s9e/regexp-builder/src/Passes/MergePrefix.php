<?php declare(strict_types=1);

/**
* @package   s9e\RegexpBuilder
* @copyright Copyright (c) 2016-2021 The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\RegexpBuilder\Passes;

/**
* Replaces (?:axx|ayy) with a(?:xx|yy)
*/
class MergePrefix extends AbstractPass
{
	/**
	* {@inheritdoc}
	*/
	protected function runPass(array $strings): array
	{
		$newStrings = [];
		foreach ($this->getStringsByPrefix($strings) as $prefix => $strings)
		{
			$newStrings[] = (isset($strings[1])) ? $this->mergeStrings($strings) : $strings[0];
		}

		return $newStrings;
	}

	/**
	* Get the number of leading elements common to all given strings
	*
	* @param  array[] $strings
	* @return integer
	*/
	protected function getPrefixLength(array $strings): int
	{
		$len = 1;
		$cnt = count($strings[0]);
		while ($len < $cnt && $this->stringsMatch($strings, $len))
		{
			++$len;
		}

		return $len;
	}

	/**
	* Return given strings grouped by their first element
	*
	* NOTE: assumes that this pass is run before the first element of any string could be replaced
	*
	* @param  array[] $strings
	* @return array[]
	*/
	protected function getStringsByPrefix(array $strings): array
	{
		$byPrefix = [];
		foreach ($strings as $string)
		{
			$byPrefix[$string[0]][] = $string;
		}

		return $byPrefix;
	}

	/**
	* Merge given strings into a new single string
	*
	* @param  array[] $strings
	* @return array
	*/
	protected function mergeStrings(array $strings): array
	{
		$len       = $this->getPrefixLength($strings);
		$newString = array_slice($strings[0], 0, $len);
		foreach ($strings as $string)
		{
			$newString[$len][] = array_slice($string, $len);
		}

		return $newString;
	}

	/**
	* Test whether all given strings' elements match at given position
	*
	* @param  array[] $strings
	* @param  integer $pos
	* @return bool
	*/
	protected function stringsMatch(array $strings, int $pos): bool
	{
		$value = $strings[0][$pos];
		foreach ($strings as $string)
		{
			if (!isset($string[$pos]) || $string[$pos] !== $value)
			{
				return false;
			}
		}

		return true;
	}
}