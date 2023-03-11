<?php declare(strict_types=1);

/**
* @package   s9e\RegexpBuilder
* @copyright Copyright (c) 2016-2021 The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\RegexpBuilder;

use s9e\RegexpBuilder\MetaCharacters;
use s9e\RegexpBuilder\Output\OutputInterface;

class Serializer
{
	/**
	* @var Escaper
	*/
	protected $escaper;

	/**
	* @var MetaCharacters
	*/
	protected $meta;

	/**
	* @var OutputInterface
	*/
	protected $output;

	/**
	* @param OutputInterface $output
	* @parm  MetaCharacters  $meta
	* @param Escaper         $escaper
	*/
	public function __construct(OutputInterface $output, MetaCharacters $meta, Escaper $escaper)
	{
		$this->escaper = $escaper;
		$this->meta    = $meta;
		$this->output  = $output;
	}

	/**
	* Serialize given strings into a regular expression
	*
	* @param  array[] $strings
	* @return string
	*/
	public function serializeStrings(array $strings): string
	{
		$info         = $this->analyzeStrings($strings);
		$alternations = array_map([$this, 'serializeString'], $info['strings']);
		if (!empty($info['chars']))
		{
			// Prepend the character class to the list of alternations
			array_unshift($alternations, $this->serializeCharacterClass($info['chars']));
		}

		$expr = implode('|', $alternations);
		if ($this->needsParentheses($info))
		{
			$expr = '(?:' . $expr . ')';
		}

		return $expr . $info['quantifier'];
	}

	/**
	* Analyze given strings to determine how to serialize them
	*
	* The returned array may contains any of the following elements:
	*
	*  - (string) quantifier Either '' or '?'
	*  - (array)  chars      List of values from single-char strings
	*  - (array)  strings    List of multi-char strings
	*
	* @param  array[] $strings
	* @return array
	*/
	protected function analyzeStrings(array $strings): array
	{
		$info = ['alternationsCount' => 0, 'quantifier' => ''];
		if ($strings[0] === [])
		{
			$info['quantifier'] = '?';
			unset($strings[0]);
		}

		$chars = $this->getChars($strings);
		if (count($chars) > 1)
		{
			++$info['alternationsCount'];
			$info['chars'] = array_values($chars);
			$strings       = array_diff_key($strings, $chars);
		}

		$info['strings']            = array_values($strings);
		$info['alternationsCount'] += count($strings);

		return $info;
	}

	/**
	* Return the portion of strings that are composed of a single character
	*
	* @param  array[]
	* @return array   String key => value
	*/
	protected function getChars(array $strings): array
	{
		$chars = [];
		foreach ($strings as $k => $string)
		{
			if ($this->isChar($string))
			{
				$chars[$k] = $string[0];
			}
		}

		return $chars;
	}

	/**
	* Get the list of ranges that cover all given values
	*
	* @param  integer[] $values Ordered list of values
	* @return array[]           List of ranges in the form [start, end]
	*/
	protected function getRanges(array $values): array
	{
		$i      = 0;
		$cnt    = count($values);
		$start  = $values[0];
		$end    = $start;
		$ranges = [];
		while (++$i < $cnt)
		{
			if ($values[$i] === $end + 1)
			{
				++$end;
			}
			else
			{
				$ranges[] = [$start, $end];
				$start = $end = $values[$i];
			}
		}
		$ranges[] = [$start, $end];

		return $ranges;
	}

	/**
	* Test whether given string represents a single character
	*
	* @param  array $string
	* @return bool
	*/
	protected function isChar(array $string): bool
	{
		return count($string) === 1 && is_int($string[0]) && MetaCharacters::isChar($string[0]);
	}

	/**
	* Test whether an expression is quantifiable based on the strings info
	*
	* @param  array $info
	* @return bool
	*/
	protected function isQuantifiable(array $info): bool
	{
		$strings = $info['strings'];

		return empty($strings) || $this->isSingleQuantifiableString($strings);
	}

	/**
	* Test whether a list of strings contains only one single quantifiable string
	*
	* @param  array[] $strings
	* @return bool
	*/
	protected function isSingleQuantifiableString(array $strings): bool
	{
		return count($strings) === 1 && count($strings[0]) === 1 && MetaCharacters::isQuantifiable($strings[0][0]);
	}

	/**
	* Test whether an expression needs parentheses based on the strings info
	*
	* @param  array $info
	* @return bool
	*/
	protected function needsParentheses(array $info): bool
	{
		return ($info['alternationsCount'] > 1 || ($info['quantifier'] && !$this->isQuantifiable($info)));
	}

	/**
	* Serialize a given list of values into a character class
	*
	* @param  integer[] $values
	* @return string
	*/
	protected function serializeCharacterClass(array $values): string
	{
		$expr = '[';
		foreach ($this->getRanges($values) as list($start, $end))
		{
			$expr .= $this->serializeCharacterClassUnit($start);
			if ($end > $start)
			{
				if ($end > $start + 1)
				{
					$expr .= '-';
				}
				$expr .= $this->serializeCharacterClassUnit($end);
			}
		}
		$expr .= ']';

		return $expr;
	}

	/**
	* Serialize a given value to be used in a character class
	*
	* @param  integer $value
	* @return string
	*/
	protected function serializeCharacterClassUnit(int $value): string
	{
		return $this->serializeValue($value, 'escapeCharacterClass');
	}

	/**
	* Serialize an element from a string
	*
	* @param  array|integer $element
	* @return string
	*/
	protected function serializeElement($element): string
	{
		return (is_array($element)) ? $this->serializeStrings($element) : $this->serializeLiteral($element);
	}

	/**
	* Serialize a given value to be used as a literal
	*
	* @param  integer $value
	* @return string
	*/
	protected function serializeLiteral(int $value): string
	{
		return $this->serializeValue($value, 'escapeLiteral');
	}

	/**
	* Serialize a given string into a regular expression
	*
	* @param  array  $string
	* @return string
	*/
	protected function serializeString(array $string): string
	{
		return implode('', array_map([$this, 'serializeElement'], $string));
	}

	/**
	* Serialize a given value
	*
	* @param  integer $value
	* @param  string  $escapeMethod
	* @return string
	*/
	protected function serializeValue(int $value, string $escapeMethod): string
	{
		return ($value < 0) ? $this->meta->getExpression($value) : $this->escaper->$escapeMethod($this->output->output($value));
	}
}