<?php

namespace MakinaCorpus\ACL\Bridge\Symfony;

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
     * @param string|string[] $attributes
     * @param mixed $object
     * @param mixed $profile
     *
     * @return boolean
     */
    protected function isGranted($attributes, $object = null, $profile = null)
    {
        // When using the base Symfony implementation, profile will be ignored
        return $this->authorizationChecker->isGranted($attributes, $object, $profile);
    }
}
