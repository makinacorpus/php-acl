<?php

namespace MakinaCorpus\ACL;

/**
 * Represent a full ACL for a single resource
 */
interface EntryListInterface
{
    /**
     * Get resource
     *
     * @return Resource
     */
    public function getResource();

    /**
     * Has the given permission
     *
     * @param string $permission
     *
     * @return boolean
     */
    public function hasPermissionFor(Profile $profile, $permission);

    /**
     * Is this instance empty
     *
     * @return boolean
     */
    public function isEmpty();
}
