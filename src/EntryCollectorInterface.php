<?php

namespace MakinaCorpus\ACL;

/**
 * Entry collector is to be used in systems where ACL can be rebuilt at any
 * time programatically without storing them, this allows to use the store
 * as a cache which can be dropped at anytime.
 *
 * Such systems includes, for example, the Drupal node_access system which
 * will ask modules for their grants when saving content or at runtime.
 */
interface EntryCollectorInterface extends SupportiveInterface
{
    /**
     * Collect entries for resource
     *
     * @param EntryListBuilder $entries
     */
    public function collect(EntryListBuilder $builder);
}
