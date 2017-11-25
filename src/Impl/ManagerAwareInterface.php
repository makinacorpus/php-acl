<?php

namespace MakinaCorpus\ACL\Impl;

use MakinaCorpus\ACL\Manager;

/**
 * Use this interface for components that needs to be able to check permissions.
 */
interface ManagerAwareInterface
{
    /**
     * Set the ACL manager
     */
    public function setACLManager(Manager $aclManager);
}
