<?php

namespace MakinaCorpus\ACL;

/**
 * Set of default commonly used permissions, but nothing forces you to use this.
 *
 * Permission are basically strings that reprensent a specific action, you can
 * use anything you want.
 */
final class Permission
{
    const CLONE = 'clone';
    const COMMENT = 'comment';
    const CREATE = 'create';
    const DELETE = 'delete';
    const HIDE = 'hide';
    const LOCK = 'lock';
    const MOVE = 'move';
    const OVERVIEW = 'overview';
    const PUBLISH = 'publish';
    const SHARE = 'share';
    const SHOW = 'show';
    const TOUCH = 'touch';
    const UNLOCK = 'unlock';
    const UPDATE = 'update';
    const VIEW = 'view';
}
