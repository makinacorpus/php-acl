<?php

namespace MakinaCorpus\ACL\Impl;

use MakinaCorpus\ACL\EntryListInterface;
use MakinaCorpus\ACL\Profile;

/**
 * Represent a full ACL for a single resource
 */
final class BitmaskEntryList implements EntryListInterface
{
    private $map;
    private $masks = [];

    /**
     * Default constructor
     *
     * @param BitmaskMap $map
     * @param NaiveEntry[] $entries
     */
    public function __construct(BitmaskMap $map, array $masks)
    {
        $this->map = $map;
        $this->masks = $masks;
    }

    /**
     * {@inheritdoc}
     */
    public function hasPermissionFor(Profile $profile, $permission)
    {
        $repr = $profile->hasString();

        if (isset($this->masks[$repr])) {
            return $this->masks[$repr] & $this->map->getBit($permission);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return empty($this->masks);
    }
}
