<?php

namespace MakinaCorpus\ACL\Tests;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class Token extends AbstractToken
{
    private $object;

    public function __construct($object)
    {
        parent::__construct();

        $this->object = $object;
    }

    public function getOriginalObject()
    {
        return $this->object;
    }

    public function getCredentials()
    {
    }
}