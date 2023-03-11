<?php declare(strict_types=1);

/**
* @package   s9e\RegexpBuilder
* @copyright Copyright (c) 2016-2021 The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\RegexpBuilder;

use s9e\RegexpBuilder\Input\InputInterface;
use s9e\RegexpBuilder\Output\OutputInterface;
use s9e\RegexpBuilder\Passes\CoalesceOptionalStrings;
use s9e\RegexpBuilder\Passes\CoalesceSingleCharacterPrefix;
use s9e\RegexpBuilder\Passes\GroupSingleCharacters;
use s9e\RegexpBuilder\Passes\MergePrefix;
use s9e\RegexpBuilder\Passes\MergeSuffix;
use s9e\RegexpBuilder\Passes\PromoteSingleStrings;
use s9e\RegexpBuilder\Passes\Recurse;

class Builder
{
	/**
	* @var InputInterface
	*/
	protected $input;

	/**
	* @var MetaCharacters
	*/
	protected $meta;

	/**
	* @var Runner
	*/
	protected $runner;

	/**
	* @var Serializer
	*/
	protected $serializer;

	/**
	* @param array $config
	*/
	public function __construct(array $config = [])
	{
		$config += [
			'delimiter'     => '/',
			'input'         => 'Bytes',
			'inputOptions'  => [],
			'meta'          => [],
			'output'        => 'Bytes',
			'outputOptions' => []
		];

		$this->setInput($config['input'], $config['inputOptions']);
		$this->setMeta($config['meta']);
		$this->setSerializer($config['output'], $config['outputOptions'], $config['delimiter']);
		$this->setRunner();
	}

	/**
	* Build and return a regular expression that matches all of the given strings
	*
	* @param  string[] $strings Literal strings to be matched
	* @return string            Regular expression (without delimiters)
	*/
	public function build(array $strings): string
	{
		$strings = array_unique($strings);
		sort($strings);
		if ($this->isEmpty($strings))
		{
			return '';
		}

		$strings = $this->splitStrings($strings);
		$strings = $this->meta->replaceMeta($strings);
		$strings = $this->runner->run($strings);

		return $this->serializer->serializeStrings($strings);
	}

	/**
	* Test whether the list of strings is empty
	*
	* @param  string[] $strings
	* @return bool
	*/
	protected function isEmpty(array $strings): bool
	{
		return (empty($strings) || $strings === ['']);
	}

	/**
	* Set the InputInterface instance in $this->input
	*
	* @param  string $inputType
	* @param  array  $inputOptions
	* @return void
	*/
	protected function setInput(string $inputType, array $inputOptions): void
	{
		$className   = __NAMESPACE__ . '\\Input\\' . $inputType;
		$this->input = new $className($inputOptions);
	}

	/**
	* Set the MetaCharacters instance in $this->meta
	*
	* @param  array $map
	* @return void
	*/
	protected function setMeta(array $map): void
	{
		$this->meta = new MetaCharacters($this->input);
		foreach ($map as $char => $expr)
		{
			$this->meta->add($char, $expr);
		}
	}

	/**
	* Set the Runner instance $in this->runner
	*
	* @return void
	*/
	protected function setRunner(): void
	{
		$this->runner = new Runner;
		$this->runner->addPass(new MergePrefix);
		$this->runner->addPass(new GroupSingleCharacters);
		$this->runner->addPass(new Recurse($this->runner));
		$this->runner->addPass(new PromoteSingleStrings);
		$this->runner->addPass(new CoalesceOptionalStrings);
		$this->runner->addPass(new MergeSuffix);
		$this->runner->addPass(new CoalesceSingleCharacterPrefix);
	}

	/**
	* Set the Serializer instance in $this->serializer
	*
	* @param  string $outputType
	* @param  array  $outputOptions
	* @param  string $delimiter
	* @return void
	*/
	protected function setSerializer(string $outputType, array $outputOptions, string $delimiter): void
	{
		$className = __NAMESPACE__ . '\\Output\\' . $outputType;
		$output    = new $className($outputOptions);
		$escaper   = new Escaper($delimiter);

		$this->serializer = new Serializer($output, $this->meta, $escaper);
	}

	/**
	* Split all given strings by character
	*
	* @param  string[] $strings List of strings
	* @return array[]           List of arrays
	*/
	protected function splitStrings(array $strings): array
	{
		return array_map([$this->input, 'split'], $strings);
	}
}