<?php

namespace MakinaCorpus\ACL\Impl;

use MakinaCorpus\ACL\Profile;

/**
 * Represent a single entry target (user)
 */
final class NaiveEntry
{
    private $profile;
    private $permissions = [];

    /**
     * Default constructor
     *
     * @param Profile $profile
     * @param string[] $permissions
     */
    public function __construct(Profile $profile, array $permissions)
    {
        $this->profile = $profile;
        $this->permissions = $permissions;
    }

    /**
     * Get profile
     *
     * @return Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Has the given permission
     *
     * @param string $permission
     *
     * @return boolean
     */
    public function hasPermission($permission)
    {
        return in_array($permission, $this->permissions);
    }

    /**
     * Get all permissions
     *
     * @return string[]
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Is this instance empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->permissions);
    }
}
