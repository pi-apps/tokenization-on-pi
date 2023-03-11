<?php declare(strict_types=1);

/**
* @package   s9e\RegexpBuilder
* @copyright Copyright (c) 2016-2021 The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\RegexpBuilder\Passes;

/**
* Enables other passes to replace (?:[xy]|a[xy]) with a?[xy]
*/
class GroupSingleCharacters extends AbstractPass
{
	/**
	* {@inheritdoc}
	*/
	protected function runPass(array $strings): array
	{
		$singles = $this->getSingleCharStrings($strings);
		$cnt     = count($singles);
		if ($cnt > 1 && $cnt < count($strings))
		{
			// Remove the singles from the input, then prepend them as a new string
			$strings = array_diff_key($strings, $singles);
			array_unshift($strings, [array_values($singles)]);
		}

		return $strings;
	}

	/**
	* Return an array of every single-char string in given list of strings
	*
	* @param  array[] $strings
	* @return array[]
	*/
	protected function getSingleCharStrings(array $strings): array
	{
		return array_filter($strings, [$this, 'isSingleCharString']);
	}
}