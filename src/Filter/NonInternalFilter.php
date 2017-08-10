<?php

namespace Bolt\Api\Filter;

use Sami\Parser\Filter\FilterInterface;
use Sami\Reflection\ClassReflection;
use Sami\Reflection\MethodReflection;
use Sami\Reflection\PropertyReflection;

class NonInternalFilter implements FilterInterface
{
    public function acceptClass(ClassReflection $class)
    {
        return empty($class->getTags('internal'));
    }

    public function acceptMethod(MethodReflection $method)
    {
        return empty($method->getTags('internal'));
    }

    public function acceptProperty(PropertyReflection $property)
    {
        return empty($property->getTags('internal'));
    }
}
