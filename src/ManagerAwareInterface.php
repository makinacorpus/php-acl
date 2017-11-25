<?php

namespace MakinaCorpus\ACL;

interface ManagerAwareInterface
{
    /**
     * Set the ACL manager
     */
    public function setACLManager(Manager $aclManager);
}
