<?php

namespace MakinaCorpus\ACL\Tests;

use MakinaCorpus\ACL\Impl\BitmaskEntryListBuilder;

class BitmaskTest extends DefaultTest
{
//     protected function createVoter($storage, $collector)
//     {
//         return new BitmaskDynamicACLVoter([$storage], [$collector], new BitmaskMap());
//     }

    protected function createBuilderFactory()
    {
        return BitmaskEntryListBuilder::class;
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
