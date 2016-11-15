<?php

namespace MakinaCorpus\ACL\Impl\Symfony;

use MakinaCorpus\ACL\Manager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Basic and functionnal implementation for AuthorizationAwareInterface
 */
trait AuthorizationAwareTrait /* implements AuthorizationAwareInterface */
{
    private $authorizationChecker;

    /**
     * Set the authorization checker
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function setAuthorizatonChecker(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Alias for Manager::isGranted()
     *
     * @param mixed $object
     * @param mixed $profile
     * @param string|string[] $attributes
     *
     * @return boolean
     */
    protected function isGranted($object, $profile, $attributes)
    {
        // When using the base Symfony implementation, profile will be ignored
        return $this->authorizationChecker->isGranted($attributes, $object, $profile);
    }
}
