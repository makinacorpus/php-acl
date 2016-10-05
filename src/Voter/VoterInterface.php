<?php

namespace MakinaCorpus\ACL\Voter;

use MakinaCorpus\ACL\Profile;
use MakinaCorpus\ACL\Resource;
use MakinaCorpus\ACL\ResourceCollection;

/**
 * Voter interface, for vote based access control
 */
interface VoterInterface
{
    /**
     * Does this object supports the given resource type
     *
     * @param string $type
     *
     * @return boolean
     */
    public function supports($type);

    /**
     * Preload data for a set for resources, if revelant
     *
     * @param ResourceCollection $resources
     */
    public function preload(ResourceCollection $resources);

    /**
     * Does this voter agree for giving the permission
     *
     * @param Resource $resource
     * @param Profile $profile
     * @param string $permission
     *
     * @return boolean
     */
    public function vote(Resource $resource, Profile $profile, $permission);
}
