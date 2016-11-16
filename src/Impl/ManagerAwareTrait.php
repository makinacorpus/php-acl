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
     *
     * @param Manager $manager
     */
    public function setACLManager(Manager $aclManager)
    {
        $this->aclManager = $aclManager;
    }

    /**
     * Alias for Manager::isGranted()
     *
     * @param mixed $resource
     * @param mixed $profile
     * @param string $permission
     *
     * @return boolean
     */
    protected function isGranted($permission, $resource, $profile)
    {
        return $this->aclManager->isGranted($permission, $resource, $profile);
    }
}
