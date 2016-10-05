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
     * @param int[]|string[] $idList
     */
    public function __construct($type, $idList)
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
     * @return int[]|string[]
     */
    public function getIdList()
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
