<?php

namespace MakinaCorpus\ACL\Impl\Drupal;

use MakinaCorpus\ACL\EntryList;
use MakinaCorpus\ACL\Permission;
use MakinaCorpus\ACL\ProfileSet;

/**
 * Converts entry lists and profiles to Drupal's node_access grants.
 *
 * Please note that this implementation is opiniated, and will use its own
 * Permission::VIEW, Permission::UPDATE and Permission::DELETE constants for
 * conversion, yet it is configurable.
 */
class EntryToNodeAccessConverter
{
    private $viewPermission;
    private $updatePermission;
    private $deletePermission;

    /**
     * Default constructor
     *
     * @param string $viewPermission
     *   Permission string to use for Drupal "view" grant
     * @param string $updatePermission
     *   Permission string to use for Drupal "update" grant
     * @param string $deletePermission
     *   Permission string to use for Drupal "delete" grant
     */
    public function __construct(
        $viewPermission = Permission::VIEW,
        $updatePermission = Permission::UPDATE,
        $deletePermission = Permission::DELETE
    ) {
        $this->viewPermission   = $viewPermission;
        $this->updatePermission = $updatePermission;
        $this->deletePermission = $deletePermission;
    }

    /**
     * Convert entry list to node access grant list
     *
     * @param EntryList $entryList
     *
     * @return array
     */
    public function convertEntryList(EntryList $entryList)
    {
        $ret = [];

        foreach ($entryList->getEntries() as $entry) {
            $profile = $entry->getProfile();

            $ret[] = [
                'realm'         => $profile->getType(),
                'gid'           => $profile->getId(),
                'grant_view'    => (int)$entry->hasPermission($this->viewPermission),
                'grant_update'  => (int)$entry->hasPermission($this->updatePermission),
                'grant_delete'  => (int)$entry->hasPermission($this->deletePermission),
                'priority'      => 0,
            ];
        }

        return $ret;
    }

    /**
     * Convert profile set to user grant list
     *
     * @param ProfileSet $profileSet
     *
     * @return array
     */
    public function convertProfileSet(ProfileSet $profileSet)
    {
        $ret = [];

        foreach ($profileSet->toArray() as $type => $ids) {
            foreach ($ids as $id) {
                $ret[$type][] = $id;
            }
        }

        return $ret;
    }
}
