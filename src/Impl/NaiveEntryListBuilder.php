<?php

namespace MakinaCorpus\ACL\Impl;

use MakinaCorpus\ACL\Profile;
use MakinaCorpus\ACL\Collector\EntryListBuilderInterface;

/**
 * Builds entry lists
 */
final class NaiveEntryListBuilder implements EntryListBuilderInterface
{
    use EntryListBuilderTrait;

    /**
     * {@inheritdoc}
     */
    public function convertToEntryList()
    {
        $entries = [];
        //$map = new BitmaskMap();

        foreach ($this->entries as $type => $list) {
            foreach ($list as $id => $permissions) {
                // Here the original profile object is unnecessary, since it is
                // only meant to deal with ACL storage in the end
                $profile = new Profile($type, $id);
                $entries[] = new NaiveEntry($profile, array_keys($permissions));
            }
        }

        return new NaiveEntryList($entries);
    }
}
