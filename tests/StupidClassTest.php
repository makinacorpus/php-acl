<?php

namespace MakinaCorpus\ACL\Tests;

use MakinaCorpus\ACL\Converter\DynamicResourceConverter;

class StupidClassTest extends DefaultTest
{
    protected function createResource($id)
    {
        return new StupidClass($id);
    }

    protected function createResourceConverters()
    {
        return [
            new DynamicResourceConverter(),
        ];
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
