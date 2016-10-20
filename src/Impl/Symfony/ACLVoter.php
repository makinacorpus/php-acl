<?php

namespace MakinaCorpus\ACL\Impl\Symfony;

use MakinaCorpus\ACL\Manager;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ACLVoter extends Voter
{
    private $manager;

    /**
     * Default constructor
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inhertdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $this->manager->supportsPermission($attribute) && $this->manager->supportsResource($subject);
    }

    /**
     * {@inhertidoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->manager->isGranted($subject, $token, $attribute);
    }
}
