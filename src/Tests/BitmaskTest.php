<?php

namespace MakinaCorpus\ACL\Tests;

use MakinaCorpus\ACL\Impl\BitmaskEntryListBuilder;
use MakinaCorpus\ACL\Impl\BitmaskMap;

class BitmaskTest extends DefaultTest
{
    protected function createPermissionMap()
    {
        return new BitmaskMap();
    }

    /**
     * Tests bacics, pretty much everything except edge case we'd find out later
     */
    public function testBasicFeatures()
    {
        return $this->doTheRealTest(false);
    }

    /**
     * Tests bacics, pretty much everything but with preload
     */
    public function testWithPreload()
    {
        return $this->doTheRealTest(true);
    }
}
