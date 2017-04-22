<?php

namespace MakinaCorpus\ACL\Impl;

use MakinaCorpus\ACL\EntryListInterface;
use MakinaCorpus\ACL\Identity;
use MakinaCorpus\ACL\ProfileSet;

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
    public function hasPermissionFor(ProfileSet $profiles, $permission)
    {
        foreach ($profiles->toArray() as $type => $ids) {
            foreach ($ids as $id) {
                $repr = Identity::getStringRepresentation($type, $id);

                if (isset($this->masks[$repr]) && ($this->masks[$repr] & $this->map->getBit($permission))) {
                    return true;
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
        $ret = [];

        foreach ($this->masks as $repr => $mask) {

            list($type, $id) = Identity::fromString($repr);

            $permissions = [];
            foreach ($this->map->getBitMap() as $permission => $value) {
                if ($mask & $value) {
                    $permissions[] = $permission;
                }
            }

            $ret[] = new NaiveEntry($type, $id, $permissions);
        }

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return empty($this->masks);
    }
}
