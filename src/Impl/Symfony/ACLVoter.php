<?php

namespace MakinaCorpus\ACL\Impl\Symfony;

use MakinaCorpus\ACL\Manager;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ACLVoter implements VoterInterface
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
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        $vote = VoterInterface::ACCESS_ABSTAIN;

        foreach ($attributes as $attribute) {

            if (!is_string($attribute)) {
                continue;
            }

            $local = $this->manager->vote($subject, $token, $attribute);

            if (Manager::ALLOW === $local) {
                // ALLOW wins over DENY and ABSTAIN locally, I know it seems
                // weird the user should drive itself the ALLOW, DENY order by
                // configuration, but actually Symfony does this, so let's not
                // surprise it and give it what it wants.
                return VoterInterface::ACCESS_GRANTED;
            }

            if (Manager::DENY === $local) {
                // DENY takes precedence over ABSTAIN and should not be
                // overriden if the local value return ABSTAIN.
                $vote = VoterInterface::ACCESS_DENIED;
            }
        }

        return $vote;
    }
}
