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

        $instance->cacheId = $instance->computeCacheIdentifier();

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
        $instance->cacheId = $instance->computeCacheIdentifier();

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
     *
     * This will be used for caching purpose only
     *
     * @return string
     */
    private function computeCacheIdentifier()
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
     *
     * @return string
     */
    public function getCacheIdentifier()
    {
        return $this->cacheId;
    }

    /**
     * Does the have the given profile
     *
     * @param string $type
     * @param string $id
     *
     * @return boolean
     */
    public function is($type, $id)
    {
        if (isset($this->index[$type])) {
            return in_array($id, $this->index[$type]);
        }
        return false;
    }

    /**
     * Get all user profiles
     *
     * Do not call this at runtime, it will instanciate the Profile
     * instances
     *
     * @return Profile[]
     */
    public function getAll()
    {
        $ret = [];

        foreach ($this->index as $type => $ids) {
            foreach ($ids as $id) {
                $ret = new Profile($type, $id);
            }
        }

        return $ret;
    }

    /**
     * Get current profile as array
     *
     * @return string[][]
     *   Keys are profile types, values are arrays whose keys are profile
     *   identifiers, values are identifier arrays
     */
    public function toArray()
    {
        return $this->index;
    }

    /**
     * Does it have any profile
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->index);
    }
}
