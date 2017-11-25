<?php

namespace MakinaCorpus\ACL\Tests\Functionnal;

class StupidClass
{
    private $id;

    public function  __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
