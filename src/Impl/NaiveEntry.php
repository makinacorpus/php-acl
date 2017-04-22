<?php

namespace MakinaCorpus\ACL\Impl;

use MakinaCorpus\ACL\Profile;

/**
 * Represent a single entry target (user)
 */
final class NaiveEntry
{
    private $type;
    private $id;
    private $permissions = [];

    /**
     * Default constructor
     *
     * @param string $type
     *   Profile type
     * @param string $id
     *   Profile identifier
     * @param string[] $permissions
     *   Permissions
     */
    public function __construct($type, $id, array $permissions)
    {
        $this->type = $type;
        $this->id = $id;
        $this->permissions = $permissions;
    }

    /**
     * Does this entry is for the given profile
     *
     * @param string $type
     * @param string $id
     *
     * @return bool
     */
    public function isFor($type, $id)
    {
        return $this->type === $type && $this->id === $id;
    }

    /**
     * Get profile
     *
     * @return Profile
     */
    public function getProfile()
    {
        return new Profile($this->type, $this->id);
    }

    /**
     * Has the given permission
     *
     * @param string $permission
     *
     * @return bool
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
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->permissions);
    }
}
