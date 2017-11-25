<?php

namespace MakinaCorpus\ACL\Collector;

/**
 * Entry collector is to be used in systems where ACL can be rebuilt at any
 * time programatically without storing them, this allows to use the store
 * as a cache which can be dropped at anytime.
 *
 * Such systems includes, for example, the Drupal node_access system which
 * will ask modules for their grants when saving content or at runtime.
 */
interface EntryCollectorInterface
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
     * Collect entries for resource
     */
    public function collectEntryLists(EntryListBuilder $builder);
}
