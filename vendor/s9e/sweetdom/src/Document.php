<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) 2019-2020 The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXPath;

class Document extends DOMDocument
{
	/**
	* @link https://www.php.net/manual/domdocument.construct.php
	*
	* @param string $version  Version number of the document
	* @param string $encoding Encoding of the document
	*/
	public function __construct(string $version = '1.0', string $encoding = 'utf-8')
	{
		parent::__construct($version, $encoding);

		$this->registerNodeClass('DOMElement', Element::class);
	}

	/**
	* Create and return an xsl:apply-templates element
	*
	* @param  string  $select XPath expression for the "select" attribute
	* @return Element
	*/
	public function createXslApplyTemplates(string $select = null): Element
	{
		$element = $this->createElementXSL('apply-templates');
		if (isset($select))
		{
			$element->setAttribute('select', $select);
		}

		return $element;
	}

	/**
	* Create and return an xsl:attribute element
	*
	* @param  string  $name Attribute's name
	* @param  string  $text Text content for the element
	* @return Element
	*/
	public function createXslAttribute(string $name, string $text = ''): Element
	{
		$element = $this->createElementXSL('attribute', $text);
		$element->setAttribute('name', $name);

		return $element;
	}

	/**
	* Create and return an xsl:choose element
	*
	* @return Element
	*/
	public function createXslChoose(): Element
	{
		return $this->createElementXSL('choose');
	}

	/**
	* Create and return an xsl:comment element
	*
	* @param  string  $text Text content for the comment
	* @return Element
	*/
	public function createXslComment(string $text = ''): Element
	{
		return $this->createElementXSL('comment', $text);
	}

	/**
	* Create and return an xsl:copy-of element
	*
	* @param  string  $select XPath expression for the "select" attribute
	* @return Element
	*/
	public function createXslCopyOf(string $select): Element
	{
		$element = $this->createElementXSL('copy-of');
		$element->setAttribute('select', $select);

		return $element;
	}

	/**
	* Create and return an xsl:if element
	*
	* @param  string  $test XPath expression for the "test" attribute
	* @param  string  $text Text content for the element
	* @return Element
	*/
	public function createXslIf(string $test, string $text = ''): Element
	{
		$element = $this->createElementXSL('if', $text);
		$element->setAttribute('test', $test);

		return $element;
	}

	/**
	* Create and return an xsl:otherwise element
	*
	* @param  string  $text Text content for the element
	* @return Element
	*/
	public function createXslOtherwise(string $text = ''): Element
	{
		return $this->createElementXSL('otherwise', $text);
	}

	/**
	* Create and return an xsl:text element
	*
	* @param  string  $text Text content for the element
	* @return Element
	*/
	public function createXslText(string $text = ''): Element
	{
		return $this->createElementXSL('text', $text);
	}

	/**
	* Create and return an xsl:value-of element
	*
	* @param  string  $select XPath expression for the "select" attribute
	* @return Element
	*/
	public function createXslValueOf(string $select): Element
	{
		$element = $this->createElementXSL('value-of');
		$element->setAttribute('select', $select);

		return $element;
	}

	/**
	* Create and return an xsl:variable element
	*
	* @param  string  $name   Name of the variable
	* @param  string  $select XPath expression
	* @return Element
	*/
	public function createXslVariable(string $name, string $select = null): Element
	{
		$element = $this->createElementXSL('variable');
		$element->setAttribute('name', $name);
		if (isset($select))
		{
			$element->setAttribute('select', $select);
		}

		return $element;
	}

	/**
	* Create and return an xsl:when element
	*
	* @param  string  $test XPath expression for the "test" attribute
	* @param  string  $text Text content for the element
	* @return Element
	*/
	public function createXslWhen(string $test, string $text = ''): Element
	{
		$element = $this->createElementXSL('when', $text);
		$element->setAttribute('test', $test);

		return $element;
	}

	/**
	* Evaluate and return the result of a given XPath expression
	*
	* @param  string  $expr           XPath expression
	* @param  DOMNode $node           Context node
	* @param  bool    $registerNodeNS Whether to register the node's namespace
	* @return mixed
	*/
	public function evaluate(string $expr, DOMNode $node = null, bool $registerNodeNS = true)
	{
		return $this->xpath('evaluate', func_get_args());
	}

	/**
	* Evaluate and return the first element of a given XPath query
	*
	* @param  string      $expr           XPath expression
	* @param  DOMNode     $node           Context node
	* @param  bool        $registerNodeNS Whether to register the node's namespace
	* @return DOMNode|null
	*/
	public function firstOf(string $expr, DOMNode $node = null, bool $registerNodeNS = true): ?DOMNode
	{
		return $this->xpath('query', func_get_args())->item(0);
	}

	/**
	* Evaluate and return the result of a given XPath query
	*
	* @param  string      $expr           XPath expression
	* @param  DOMNode     $node           Context node
	* @param  bool        $registerNodeNS Whether to register the node's namespace
	* @return DOMNodeList
	*/
	public function query(string $expr, DOMNode $node = null, bool $registerNodeNS = true): DOMNodeList
	{
		return $this->xpath('query', func_get_args());
	}

	/**
	* Create and return an XSL element
	*
	* @param  string  $name Element's local name
	* @param  string  $text Text content for the element
	* @return Element
	*/
	protected function createElementXSL(string $localName, string $text = ''): Element
	{
		return $this->createElementNS(
			'http://www.w3.org/1999/XSL/Transform',
			'xsl:' . $localName,
			htmlspecialchars($text, ENT_XML1)
		);
	}

	/**
	* Execute a DOMXPath method and return the result
	*
	* @param  string $methodName
	* @param  array  $args
	* @return mixed
	*/
	protected function xpath(string $methodName, array $args)
	{
		$xpath = new DOMXPath($this);
		$xpath->registerNamespace('xsl', 'http://www.w3.org/1999/XSL/Transform');

		return call_user_func_array([$xpath, $methodName], $args);
	}
}