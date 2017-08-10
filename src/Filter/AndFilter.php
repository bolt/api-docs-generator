<?php

namespace Bolt\Api\Filter;

use Sami\Parser\Filter\FilterInterface;
use Sami\Reflection\ClassReflection;
use Sami\Reflection\MethodReflection;
use Sami\Reflection\PropertyReflection;

class AndFilter implements FilterInterface
{
    /** @var FilterInterface[] */
    private $filters;

    /**
     * Constructor.
     *
     * @param FilterInterface[] $filters
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function add(FilterInterface $filter)
    {
        $this->filters[] = $filter;
    }

    public function acceptClass(ClassReflection $class)
    {
        foreach ($this->filters as $filter) {
            if (!$filter->acceptClass($class)) {
                return false;
            }
        }

        return true;
    }

    public function acceptMethod(MethodReflection $method)
    {
        foreach ($this->filters as $filter) {
            if (!$filter->acceptMethod($method)) {
                return false;
            }
        }

        return true;
    }

    public function acceptProperty(PropertyReflection $property)
    {
        foreach ($this->filters as $filter) {
            if (!$filter->acceptProperty($property)) {
                return false;
            }
        }

        return true;
    }
}
