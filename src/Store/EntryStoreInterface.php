<?php

namespace MakinaCorpus\ACL\Store;

use MakinaCorpus\ACL\EntryListInterface;
use MakinaCorpus\ACL\Resource;
use MakinaCorpus\ACL\ResourceCollection;

/**
 * Deal with entry list storage for all or some resource types.
 */
interface EntryStoreInterface
{
    /**
     * Does this object supports the given resource type
     *
     * @param string $type
     *
     * @return boolean
     */
    public function supports($type);

    /**
     * Delete all entries for the given resource
     *
     * @param Resource $resource
     */
    public function delete(Resource $resource);

    /**
     * Delete all entries for the given resources
     *
     * @param ResourceCollection $resources
     */
    public function deleteAll(ResourceCollection $resources);

    /**
     * Load all entries for the given resource
     *
     * @param Resource $resource
     *
     * @return EntryListInterface
     *   You may return null if nothing exists, but if it has already been
     *   asked to store an empty list, you should then return an empty list
     *   in order to the checker to avoid running the collect event twice.
     */
    public function load(Resource $resource);

    /**
     * Load all entries for the given resources
     *
     * @param ResourceCollection $resources
     *
     * @return EntryListInterface[]
     *   Each entry list for all resources, keys are the same as the $resources
     *   array entry list: without this everything would fail since we cannot
     *   use maps with objects as keys in PHP
     */
    public function loadAll(ResourceCollection $resources);

    /**
     * Save entries for the given resource (removes old one if exists)
     *
     * @param EntryListInterface $list
     *
     * @return EntryListInterface
     */
    public function save(EntryListInterface $list);
}
