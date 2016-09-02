<?php

namespace MakinaCorpus\ACL;

/**
 * Deal with entry list storage for all or some resource types.
 */
interface EntryStoreInterface extends SupportiveInterface
{
    /**
     * Delete all entries for the given resource
     *
     * @param Resource $resource
     */
    public function delete(Resource $resource);

    /**
     * Load all entries for the given resource
     *
     * @param Resource $resource
     *
     * @return EntryList
     *   You may return null if nothing exists, but if it has already been
     *   asked to store an empty list, you should then return an empty list
     *   in order to the checker to avoid running the collect event twice.
     */
    public function load(Resource $resource);

    /**
     * Save entries for the given resource (removes old one if exists)
     *
     * @param EntryList $list
     *
     * @return EntryList
     */
    public function save(EntryList $list);
}
