<?php

namespace MakinaCorpus\ACL;

/**
 * Represent a full ACL for a single resource
 */
final class EntryList
{
    private $map;
    private $masks = [];

    /**
     * Default constructor
     *
     * @param PermissionMap $map
     * @param int[] $masks
     */
    public function __construct(PermissionMap $map, array $masks)
    {
        $this->map = $map;
        $this->masks = $masks;
    }

    /**
     * Has the given permission
     */
    public function hasPermissionFor(ProfileSet $profiles, string $permission) : bool
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
     * Get all registered entries
     *
     * This operations is not performance wise, and should never be used during
     * normal operations and security checks, it exists only for data conversion
     * and administration purposes.
     *
     * For implementations, such as the bitmask based default implementation,
     * this will need a data decompression phase which will be CPU heavy.
     *
     * @return \MakinaCorpus\ACL\Entry[]
     */
    public function getEntries() : array
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

            $ret[] = new Entry($type, $id, $permissions);
        }

        return $ret;
    }

    /**
     * Is this instance empty
     */
    public function isEmpty() : bool
    {
        return empty($this->masks);
    }
}
