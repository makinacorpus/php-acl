<?php

namespace MakinaCorpus\ACL\Memory;

use MakinaCorpus\ACL\EntryList;
use MakinaCorpus\ACL\EntryStoreInterface;
use MakinaCorpus\ACL\Resource;

class MemoryEntryStore implements EntryStoreInterface
{
    private $entries = [];

    /**
     * {@inheritdoc}
     */
    public function supports(Resource $resource)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Resource $resource)
    {
        unset($this->entries[$resource->getType()][(string)$resource->getId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function load(Resource $resource)
    {
        $id = (string)$resource->getId();

        if (isset($this->entries[$resource->getType()][$id])) {
            return $this->entries[$resource->getType()][$id];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save(EntryList $list)
    {
        $resource = $list->getResource();

        $this->entries[$resource->getType()][(string)$resource->getId()] = $list;

        return $list;
    }
}
