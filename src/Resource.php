<?php

namespace MakinaCorpus\ACL;

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
