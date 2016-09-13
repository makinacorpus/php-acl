<?php

namespace MakinaCorpus\ACL\Cache;

use MakinaCorpus\ACL\Entry;
use MakinaCorpus\ACL\Profile;

/**
 * Specific entry implementation that will use a bitmask instead of
 * storing locally a permission map
 */
class BitmaskEntry extends Entry
{
    private $value;
    private $map;

    /**
     * Default constructor
     *
     * @param Profile $profile
     * @param string[] $permissions
     * @param BitmaskMap $map
     */
    public function __construct(Profile $profile, array $permissions, BitmaskMap $map)
    {
        parent::__construct($profile, $permissions);

        $this->map = $map;
        $this->value = $map->build($permissions);
    }

    /**
     * {@inheritdoc}
     */
    public function hasPermission($permission)
    {
        return $this->map->check($this->value, $permission);
    }
}
