<?php

namespace MakinaCorpus\ACL;

class Role extends Profile
{
    use IdentityTrait;

    private $permissions = [];

    /**
     * Default constructor
     *
     * @param int|string $id
     * @param string[] $permissions
     */
    public function __construct($id, array $permissions = [])
    {
        parent::__construct(Profile::ROLE, $id);

        $this->permissions = $permissions;
    }

    /**
     * Has this instance an object
     *
     * @return boolean
     */
    public function hasObject()
    {
        return true;
    }

    /**
     * Get object
     *
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }
}
