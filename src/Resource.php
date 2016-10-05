<?php

namespace MakinaCorpus\ACL;

use MakinaCorpus\ACL\Impl\IdentityTrait;

/**
 * Represent a single resource
 */
final class Resource
{
    const GROUP = 'group';
    const USER = 'user';
    const ROLE = 'role';

    use IdentityTrait;
}
