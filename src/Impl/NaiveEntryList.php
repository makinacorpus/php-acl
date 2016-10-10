<?php

namespace MakinaCorpus\ACL\Impl;

use MakinaCorpus\ACL\EntryListInterface;
use MakinaCorpus\ACL\Profile;

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
    public function hasPermissionFor(Profile $profile, $permission)
    {
        foreach ($this->entries as $entry) {
            if ($entry->getProfile()->equals($profile)) {
                if ($entry->hasPermission($permission)) {
                    return true;
                }
            }
        }
        return false;
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
