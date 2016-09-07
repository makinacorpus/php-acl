<?php

namespace MakinaCorpus\ACL\Voter;

use MakinaCorpus\ACL\Profile;
use MakinaCorpus\ACL\Resource;

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
    public function supports(Resource $resource);

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
