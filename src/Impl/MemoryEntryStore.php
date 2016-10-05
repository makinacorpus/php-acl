<?php

namespace MakinaCorpus\ACL\Impl;

use MakinaCorpus\ACL\EntryList;
use MakinaCorpus\ACL\Resource;
use MakinaCorpus\ACL\ResourceCollection;
use MakinaCorpus\ACL\Store\EntryStoreInterface;

/**
 * In memory entry storage, useful for either caching or unit testing.
 *
 * Any sane person would never use this in a production environment.
 */
class MemoryEntryStore implements EntryStoreInterface
{
    private $entries = [];

    /**
     * {@inheritdoc}
     */
    public function supports($type)
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
    public function deleteAll(ResourceCollection $resources)
    {
        foreach ($resources as $resource) {
            $this->delete($resource);
        }
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
     * Load all entries for the given resources
     *
     * @param ResourceCollection $resources
     *
     * @return EntryList[]
     *   Each entry list for all resources, keys are the same as the $resources
     *   array entry list: without this everything would fail since we cannot
     *   use maps with objects as keys in PHP
     */
    public function loadAll(ResourceCollection $resources)
    {
        $ret = [];

        foreach ($resources as $key => $resource) {
            $ret[$key] = $this->load($resource);
        }

        return $ret;
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
