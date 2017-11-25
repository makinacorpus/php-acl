<?php

namespace MakinaCorpus\ACL;

/**
 * Represent a resource collection
 */
final class ResourceCollection implements \IteratorAggregate
{
    private $type;
    private $idList = [];

    /**
     * Default constructor
     *
     * @param string $type
     * @param string[] $idList
     */
    public function __construct(string $type, array $idList)
    {
        $this->type = $type;
        $this->idList = $idList;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get identifier list
     *
     * @return string[]
     */
    public function getIdList() : array
    {
        return $this->idList;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        foreach ($this->idList as $key => $id) {
            yield $key => new Resource($this->type, $id);
        }
    }
}
