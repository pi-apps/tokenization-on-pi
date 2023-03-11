<?php declare(strict_types=1);

/**
* @package   s9e\RegexpBuilder
* @copyright Copyright (c) 2016-2021 The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\RegexpBuilder;

class Escaper
{
	/**
	* @var array Characters to escape in a character class
	*/
	public $inCharacterClass = ['-' => '\\-', '\\' => '\\\\', ']' => '\\]', '^' => '\\^'];

	/**
	* @var array Characters to escape outside of a character class
	*/
	public $inLiteral = [
		'$'  => '\\$',  '(' => '\\(', ')' => '\\)', '*' => '\\*',
		'+'  => '\\+',  '.' => '\\.', '?' => '\\?', '[' => '\\[',
		'\\' => '\\\\', '^' => '\\^', '{' => '\\{', '|' => '\\|'
	];

	/**
	* @param string $delimiter Delimiter used in the final regexp
	*/
	public function __construct(string $delimiter = '/')
	{
		foreach (str_split($delimiter, 1) as $char)
		{
			$this->inCharacterClass[$char] = '\\' . $char;
			$this->inLiteral[$char]        = '\\' . $char;
		}
	}

	/**
	* Escape given character to be used in a character class
	*
	* @param  string $char Original character
	* @return string       Escaped character
	*/
	public function escapeCharacterClass(string $char): string
	{
		return $this->inCharacterClass[$char] ?? $char;
	}

	/**
	* Escape given character to be used outside of a character class
	*
	* @param  string $char Original character
	* @return string       Escaped character
	*/
	public function escapeLiteral(string $char): string
	{
		return $this->inLiteral[$char] ?? $char;
	}
}