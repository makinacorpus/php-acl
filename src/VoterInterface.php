<?php

namespace MakinaCorpus\ACL;

/**
 * Voter interface, for vote based access control
 */
interface VoterInterface extends SupportiveInterface
{
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
