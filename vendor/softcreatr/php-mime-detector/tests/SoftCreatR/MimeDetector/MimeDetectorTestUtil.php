<?php

/**
 * Mime Detector for PHP.
 *
 * @license https://github.com/SoftCreatR/php-mime-detector/blob/main/LICENSE  ISC License
 */

declare(strict_types=1);

namespace SoftCreatR\Tests\MimeDetector;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use SoftCreatR\MimeDetector\MimeDetector;

/**
 * Utility methods for MimeDetectorTest.
 */
class MimeDetectorTestUtil
{
    /**
     * Returns a private method for testing purposes.
     *
     * @throws  ReflectionException
     */
    public static function getPrivateMethod(MimeDetector $obj, string $methodName): ReflectionMethod
    {
        $class = new ReflectionClass($obj);

        if (!$class->hasMethod($methodName)) {
            throw new ReflectionException('Method ' . $methodName . ' is not defined.');
        }

        $method = $class->getMethod($methodName);

        if (!$method->isPrivate()) {
            throw new ReflectionException('Method ' . $methodName . ' is not private.');
        }

        $method->setAccessible(true);

        return $method;
    }

    /**
     * Returns a protected method for testing purposes.
     *
     * @throws  ReflectionException
     */
    public static function getProtectedMethod(MimeDetector $obj, string $methodName): ReflectionMethod
    {
        $class = new ReflectionClass($obj);

        if (!$class->hasMethod($methodName)) {
            throw new ReflectionException('Method ' . $methodName . ' is not defined.');
        }

        $method = $class->getMethod($methodName);

        if (!$method->isProtected()) {
            throw new ReflectionException('Method ' . $methodName . ' is not protected.');
        }

        $method->setAccessible(true);

        return $method;
    }

    /**
     * Updates a protected property for testing purposes.
     *
     * @throws  ReflectionException
     */
    public static function setProtectedProperty(MimeDetector $obj, string $propertyName, $value = null): void
    {
        $class = new ReflectionClass($obj);

        if (!$class->hasProperty($propertyName)) {
            throw new ReflectionException('Property ' . $propertyName . ' is not defined.');
        }

        $property = $class->getProperty($propertyName);

        if (!$property->isProtected()) {
            throw new ReflectionException('Property ' . $propertyName . ' is not protected.');
        }

        $property->setAccessible(true);
        $property->setValue($obj, $value);
        $property->setAccessible(false);
    }

    /**
     * Updates a protected property for testing purposes.
     *
     * @throws  ReflectionException
     */
    public static function setPrivateProperty(MimeDetector $obj, string $propertyName, $value = null): void
    {
        $class = new ReflectionClass($obj);

        if (!$class->hasProperty($propertyName)) {
            throw new ReflectionException('Property ' . $propertyName . ' is not defined.');
        }

        $property = $class->getProperty($propertyName);

        if (!$property->isPrivate()) {
            throw new ReflectionException('Property ' . $propertyName . ' is not private.');
        }

        $property->setAccessible(true);
        $property->setValue($obj, $value);
        $property->setAccessible(false);
    }
}
