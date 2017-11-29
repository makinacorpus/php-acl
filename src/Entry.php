<?php

namespace MakinaCorpus\ACL;

/**
 * Represent a single entry target (user)
 */
final class Entry
{
    private $type;
    private $id;
    private $permissions = [];

    /**
     * Default constructor
     */
    public function __construct(string $type, string $id, array $permissions)
    {
        $this->type = (string)$type;
        $this->id = (string)$id;
        $this->permissions = $permissions;
    }

    /**
     * Does this entry is for the given profile
     */
    public function isFor(string $type, string $id) : bool
    {
        return $this->type === (string)$type && $this->id === (string)$id;
    }

    /**
     * Get profile
     */
    public function getProfile() : Profile
    {
        return new Profile($this->type, $this->id);
    }

    /**
     * Has the given permission
     */
    public function hasPermission(string $permission) : bool
    {
        return in_array($permission, $this->permissions);
    }

    /**
     * Get all permissions
     *
     * @return string[]
     */
    public function getPermissions() : array
    {
        sort($this->permissions, SORT_NATURAL);

        return $this->permissions;
    }

    /**
     * Is this instance empty
     *
     * @return bool
     */
    public function isEmpty() : bool
    {
        return empty($this->permissions);
    }
}
