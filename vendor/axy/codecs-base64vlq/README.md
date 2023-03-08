# axy\codecs\base64vlq

Codec for VLQ (variable-length quantity) Base64 algorithm (PHP).

* GitHub: [axypro/codecs-base64vlq](https://github.com/axypro/codecs-base64vlq)
* Composer: [axypro/codecs-base64vlq](https://packagist.org/packages/axy/codecs-base64vlq)
* LICENSE: [MIT](LICENSE)
* Author: Oleg Grigoriev

PHP 5.4+

Library does not require any dependencies (except composer packages).

## VQL + Base64

Base64 allows us to represent a sequence of numbers in a text string 
that can be stored and transmit in text formats (JSON, XML, etc).

VLQ allows us to represent an integer in a sequence numbers with little digit capacity.
For example 6 bit is the limit for Base64.
The resulting numbers are called "VLQ digits".
Small input number is represented by fewer VLQ-digits than big number.
Thus VLQ is most effective if the input sequence is contains mainly the small numbers.

VLQ+Base64 allows us effectively represented a sequence of integers (dominated by a small number of) in the text format.

For example, it used in [JavaScript/CSS source map](https://www.google.ru/url?sa=t&rct=j&q=&esrc=s&source=web&cd=1&cad=rja&uact=8&sqi=2&ved=0CBwQFjAA&url=https%3A%2F%2Fdocs.google.com%2Fdocument%2Fd%2F1U1RGAehQwRypUTovF1KRlpiOFze0b-_2gc6fAH0KY0k%2Fedit&ei=abnpVKTZKeaHygPs0oK4BA&usg=AFQjCNFpOFA_dC_8cB50KJ1dXbOH7pvJnA&bvm=bv.86475890,d.bGQ).

## The Algorithm

For example, we have a block of numbers: `[12345, -12345, 0]`.

(1). VLQ only works with unsigned integers.
Transfer the sign bit to the end of the integer.

`12345` in binary is `11000000111001`.
Added `0` (positive) to the end: `110000001110010`.

For `-12345` take a positive form and add `1` (negative) to the end: `110000001110011`.
 
Result is `[110000001110010, 110000001110011, 0]`.

(2). Transform to the VLQ-sequence.
For Base64 we need a block of 6-bit numbers.
Most significant bit is reserved - it is "continuation".

Split numbers to groups of 5 bits: `[11000 00011 10010, 11000 00011 10011, 00000]`.
Output starting from the least significant bits.
If the group is not the last in the current number then set the continuation bit. 

Result: `[110010 100011 011000 110011 100011 011000 000000]`. 
Or decimal `[50, 35, 24, 51, 35, 24, 0]`.
These are VLQ digits.

(3). Replace the numbers on the letters of the Base64-alphabet.
The standard alphabet is `A..Za..z0..9+/`.

Result is `yjYzjYA`.

## How to use the library

```php
use axy\codecs\base64vlq\Encoder;

$encoder = new Encoder();

$encoder->encode([12345, -12345, 0]); // yjYzjYA
$encoder->decode('yjYzjYA'); //  [12345, 012345, 9]

$encoder->decode('Variable+Length+QuantitY'); // [-10, 13, -13349, -13 ... -12797139]
```

Or default encoder can be obtained as follow:

```php
$encoder = Encoder::getStandardInstance();
```

In this case, the object creation and preliminary calculations are performed only once.

## Custom options

The standard encoder uses standard options:
 
1. Transfer the sign bit.
2. 6-bit VLQ digits.
3. Standard alphabet: `A..Za..z0..9+/`.

You can apply your settings:

```php
Encoder::__construct([array|string $alphabet, int $bits, bool $signed = true])
```

```php
/* Custom alphabet, 4 bits, no sign transfer */
$encoder = new Encoder('My Alphabet', 3, false);

$encoder->encode([12345, 6789]); // phalllApplhhhy
```

Custom alphabet can be specified as a string (`My Alphabet`) or as an array (`[1 => 'A', 10 => 'B', 15 => 'C', 20 => 'D']`).

## Exceptions

The error classes are located in the namespace `axy\codecs\base64vlq\Encoder\Errors`.

* Error
    * VLQ
        * InvalidVLQSequence
    * Base64
        * InvalidBase64
        * InvalidBase64Input

### InvalidVLQSequence

```php
$encoder->decode('Az'); // VLQ sequence is invalid: [0,51]
```

`z` is 51 (the continuation bit = 1).
The last digit must have 0 in continuation bit.

### InvalidBase64

```php
$encoder->decode('A*A'); // Base-64 string is invalid: "A*A"
```

`*` are not in the standard Base64 alphabet.

### InvalidBase64Input

For the standard encoder this exception should not occur.

Can occur for incorrect custom options:

```php
$encoder = new Encoder('qwe', 10);
$encoder->encode([10, 20, 30]); // Number 20 is not found in Base64 alphabet
```

10 bits is 1024 variants, but the alphabet contains only 3 letter.
