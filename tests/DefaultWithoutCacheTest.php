<?php

namespace MakinaCorpus\ACL\Tests;

class DefautWithoutCacheTest extends DefaultTest
{
    protected function createStorages()
    {
        return [];
    }
}
