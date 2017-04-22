<?php

namespace MakinaCorpus\ACL\Impl;

use MakinaCorpus\ACL\EntryListInterface;
use MakinaCorpus\ACL\ProfileSet;

/**
 * Represent a full ACL for a single resource
 */
final class NaiveEntryList implements EntryListInterface
{
    private $entries = [];

    /**
     * Default constructor
     *
     * @param NaiveEntry[] $entries
     */
    public function __construct(array $entries)
    {
        $this->entries = $entries;
    }

    /**
     * {@inheritdoc}
     */
    public function hasPermissionFor(ProfileSet $profiles, $permission)
    {
        foreach ($profiles->toArray() as $type => $ids) {
            foreach ($ids as $id) {
                foreach ($this->entries as $entry) {
                    if ($entry->isFor($type, $id) && $entry->hasPermission($permission)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        foreach ($this->entries as $entry) {
            if (!$entry->isEmpty()) {
                return false;
            }
        }

        return true;
    }
}
