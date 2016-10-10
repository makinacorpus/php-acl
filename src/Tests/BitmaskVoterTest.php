<?php

namespace MakinaCorpus\ACL\Tests;

use MakinaCorpus\ACL\Impl\BitmaskDynamicACLVoter;
use MakinaCorpus\ACL\Impl\BitmaskMap;
use MakinaCorpus\ACL\Impl\MemoryEntryStore;

class BitmaskVoterTest extends NaiveVoterTest
{
    protected function createStorage()
    {
        return new MemoryEntryStore();
    }

    protected function createVoter($storage, $collector)
    {
        return new BitmaskDynamicACLVoter([$storage], [$collector], new BitmaskMap());
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
