<?php

namespace MakinaCorpus\ACL\Converter;

use MakinaCorpus\ACL\Resource;

/**
 * Attempts to derivate information from the given object
 */
final class DynamicResourceConverter implements ResourceConverterInterface
{
    private $bondage;
    private $typeMap = [];

    /**
     * Default constructor
     */
    public function __construct($typeMap = [])
    {
        // https://ocramius.github.io/blog/accessing-private-php-class-members-without-reflection/
        $this->bondage = function ($object) {
            if (!empty($object->id)) {
                return $object->id;
            }
        };

        $this->typeMap = $typeMap;
    }

    /**
     * Attempt to steal object identifier by any mean possible
     *
     * @param mixed $object
     *
     * @return string
     */
    private function findId($object)
    {
        $closure = \Closure::bind($this->bondage, $object, $object);
        $id = $closure($object);

        if (!is_scalar($id)) {
            $id = null;
        }

        if (!$id) {
            if (method_exists($object, 'getId')) {
                $id = $object->getId();
            } else if (method_exists($object, 'id')) {
                $id = $object->id();
            }
        }

        if (is_scalar($id)) {
            return $id;
        }
    }

    /**
     * Find object type
     *
     * @param mixed $object
     *
     * @return string
     */
    private function findType($object) : string
    {
        $class = get_class($object);

        if (isset($this->typeMap[$class])) {
            return $this->typeMap[$class];
        }

        // Let's suppose you have the following class: \Foo\Bar\Baz, this algorithm
        // will attempt Bar as type, and give that.
        // Now, let's suppose you also have the \Bar\Baz class, and it happens that
        // it was register first (using 'Baz' as identifier), then the algorithm
        // attempts BarBaz, and gives that to our class, and so on...
        $parts = explode('\\', $class);
        $current = '';
        for ($i = count($parts) - 1; $i >= 0; --$i) {
            if ($current) {
                $current = ucfirst($parts[$i]) . $current;
            } else {
                $current = $parts[$i];
            }

            if (!in_array($current, $this->typeMap)) {
                return $this->typeMap[$class] = $current;
            }
        }

        throw new \LogicException("This is supposed to be NOT possible");
    }

    /**
     * {@inheritdoc}
     */
    public function convert($object)
    {
        if (!is_object($object)) {
            return;
        }

        $id = $this->findId($object);

        if ($id) {
            return new Resource($this->findType($object), $id);
        }
    }
}
