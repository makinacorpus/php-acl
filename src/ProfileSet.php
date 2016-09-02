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
    private $profiles = [];
    private $index = [];
    private $object;

    /**
     * Default constructor
     *
     * @param Profile[] $profiles
     *   List of profiles the user has
     * @param mixed $object
     *   Original user profile
     */
    public function __construct(array $profiles, $object = null)
    {
        foreach ($profiles as $profile) {
            $this->profiles[] = $profile;
            $this->index[$profile->getType()][(string)$profile->getId()] = true;
        }

        $this->object = $object;
    }

    /**
     * Get original user object
     *
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Does the have the given profile
     *
     * @param string $type
     * @param int|string $id
     *
     * @return boolean
     */
    public function is($type, $id)
    {
        return isset($this->index[$type][(string)$id]);
    }

    /**
     * Get all user profiles
     *
     * @return Profile[]
     */
    public function getAll()
    {
        return $this->profiles;
    }

    /**
     * Does it have any profile
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->profiles);
    }
}
