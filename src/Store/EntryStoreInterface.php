<?php

namespace MakinaCorpus\ACL\Store;

use MakinaCorpus\ACL\EntryList;
use MakinaCorpus\ACL\Resource;
use MakinaCorpus\ACL\ResourceCollection;

/**
 * Deal with entry list storage for all or some resource types.
 */
interface EntryStoreInterface
{
    /**
     * Does this object supports the given resource type
     */
    public function supports(string $type, string $permission) : bool;

    /**
     * Does this object supports the given resource type
     */
    public function supportsType(string $type) : bool;

    /**
     * Delete all entries for the given resource
     */
    public function delete(Resource $resource);

    /**
     * Delete all entries for the given resources
     */
    public function deleteAll(ResourceCollection $resources);

    /**
     * Load all entries for the given resource
     *
     * @return null|\MakinaCorpus\ACL\EntryList
     */
    public function load(Resource $resource);

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
    public function loadAll(ResourceCollection $resources) : array;

    /**
     * Save entries for the given resource (removes old one if exists)
     */
    public function save(Resource $resource, EntryList $list) : EntryList;
}
