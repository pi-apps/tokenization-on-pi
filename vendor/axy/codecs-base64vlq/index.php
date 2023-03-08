<?php
/**
 * Codec for VLQ (variable-length quantity) Base64 algorithm.
 *
 * @package axy\codecs\base64vlq
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 * @license https://raw.github.com/axypro/codecs-base64vlq/master/LICENSE MIT
 * @link https://github.com/axypro/codecs-base64vlq repository
 * @link https://packagist.org/packages/axy/codecs-base64vlq composer
 * @link https://github.com/axypro/codecs-base64vlq/blob/master/README.md documentation
 * @uses PHP5.4+
 */

namespace axy\codecs\base64vlq;

if (!is_file(__DIR__.'/vendor/autoload.php')) {
    throw new \LogicException('Please: composer install');
}

require_once(__DIR__.'/vendor/autoload.php');
