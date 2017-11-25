<?php

namespace MakinaCorpus\ACL\Impl;

use MakinaCorpus\ACL\Manager;

/**
 * Basic and functionnal implementation for ManagerAwareInterface
 */
trait ManagerAwareTrait /* implements ManagerAwareInterface */
{
    private $aclManager;

    /**
     * Set the ACL manager
     */
    public function setACLManager(Manager $aclManager)
    {
        $this->aclManager = $aclManager;
    }

    /**
     * Alias for Manager::isGranted()
     *
     * @param string $permission
     * @param mixed $resource
     * @param mixed $profile
     *
     * @return bool
     */
    protected function isGranted(string $permission, $resource, $profile) : bool
    {
        return $this->aclManager->isGranted($permission, $resource, $profile);
    }
}
