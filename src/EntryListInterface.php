<?php

namespace MakinaCorpus\ACL;

use MakinaCorpus\ACL\Impl\NaiveEntry;

/**
 * Represent a full ACL for a single resource
 */
interface EntryListInterface
{
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

    /**
     * Get all registered entries
     *
     * This operations is not performance wise, and should never be used during
     * normal operations and security checks, it exists only for data conversion
     * and administration purposes.
     *
     * For implementations, such as the bitmask based implementation, this will
     * need a data decompression phase which will be CPU heavy.
     *
     * @return NaiveEntry[]
     */
    public function getEntries();
}
