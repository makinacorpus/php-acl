<?php

namespace MakinaCorpus\ACL\Bridge\Symfony;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Use this interface for components that needs to be able to check permissions.
 */
interface AuthorizationAwareInterface
{
    /**
     * Set the authorization checker
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function setAuthorizatonChecker(AuthorizationCheckerInterface $authorizationChecker);
}
