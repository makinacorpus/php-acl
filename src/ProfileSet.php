<?php

namespace MakinaCorpus\ACL;

/**
 * This represents a set of profiles to match on a certain user that may have
 * more than one profile in a certain context.
 *
 * For exemple, you may imagine the following scenario:
 *   - there is a group a on your site
 *   - user 1 is member of group a
 *   - user 1 is also admin of group a
 *   - user also have global role "reader" on the site
 * User 1 could have the following profiles (depending on how you want to
 * implement it):
 *   - (user, 1)
 *   - (group, a)
 *   - (group_admin, a)
 *   - (role, reader)
 *
 * You could imagine giving any kind of profiles to any user, which may vary
 * upon context. In a Drupal system for example, we would use the profile types
 * as realm for the 'node_access' table.
 */
final class ProfileSet
{
    /**
     * Create profile from profile instance list
     *
     * @param Profile[] $profiles
     *
     * @return ProfileSet
     */
    public static function createFromProfiles(array $profiles) : ProfileSet
    {
        $instance = new self();
        foreach ($profiles as $profile) {
            $instance->index[$profile->getType()] = (string)$profile->getId();
        }

        return $instance;
    }

    /**
     * Create profile from profile instance list
     *
     * @param string[][]
     *
     * @return ProfileSet
     */
    public static function createFromArray(array $array) : ProfileSet
    {
        $instance = new self();
        $instance->index = $array;

        return $instance;
    }

    /**
     * @var string
     */
    private $cacheId;

    /**
     * @var string[][]
     */
    private $index = [];

    /**
     * Compute a unique identifier for this profile set
     */
    private function computeCacheIdentifier() : string
    {
        $ret = [];

        foreach ($this->index as $type => $ids) {
            $ret[$type] = $type . ':' . implode(',', $ids);
        }

        return implode(';', $ret);
    }

    /**
     * Get a unique identifier for this profile set
     *
     * This will be used for caching purpose only
     */
    public function getCacheIdentifier() : string
    {
        if (!$this->cacheId) {
            $this->cacheId = $this->computeCacheIdentifier();
        }

        return $this->cacheId;
    }

    /**
     * Does the have the given profile
     */
    public function is(string $type, string $id) : bool
    {
        if (isset($this->index[$type])) {
            return in_array($id, $this->index[$type]);
        }
        return false;
    }

    /**
     * Get current profile as array
     *
     * @return string[][]
     *   Keys are profile types, values are arrays whose keys are profile
     *   identifiers, values are identifier arrays
     */
    public function toArray() : array
    {
        return $this->index;
    }

    /**
     * Does it have any profile
     *
     * @return bool
     */
    public function isEmpty() : bool
    {
        return empty($this->index);
    }
}
