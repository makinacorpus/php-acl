<?php

namespace MakinaCorpus\ACL;

/**
 * Collect entries for all or a some resource types
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
