<?php

namespace MakinaCorpus\ACL;

/**
 * Represent a single entry target (user)
 */
final class Profile
{
    const GROUP = 'group';
    const USER = 'user';
    const REALM = 'realm';
    const ROLE = 'role';

    use IdentityTrait;
}
