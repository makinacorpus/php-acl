<?php

namespace MakinaCorpus\ACL\Tests\Unit;

use MakinaCorpus\ACL\Resource;
use MakinaCorpus\ACL\ResourceCollection;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    public function testResourceIdentity()
    {
        $resource1 = new Resource('sheep', '7');
        $this->assertSame('sheep', $resource1->getType());
        $this->assertSame('7', $resource1->getId());

        $resource2 = new Resource('sheep', 7);
        $this->assertSame('sheep', $resource2->getType());
        $this->assertSame('7', $resource2->getId());

        $false1 = new Resource('sheep', 12);
        $false2 = new Resource('user', 7);

        $this->assertTrue($resource1->equals($resource2));
        $this->assertTrue($resource2->equals($resource1));

        $this->assertFalse($false1->equals($resource2));
        $this->assertFalse($resource1->equals($false1));

        $this->assertFalse($false2->equals($resource2));
        $this->assertFalse($resource1->equals($false2));
    }

    public function testResourceCollection()
    {
        $collection = new ResourceCollection('fruit', ['ananas', 'peach', 'apple']);
        $this->assertSame('fruit', $collection->getType());
        $this->assertSame(['ananas', 'peach', 'apple'], $collection->getIdList());

        $found = [];
        foreach ($collection as $resource) {
            $this->assertInstanceOf(Resource::class, $resource);
            $this->assertSame('fruit', $resource->getType());
            $found[] = $resource;
        }

        $this->assertCount(3, $found);

        $this->assertSame('ananas', $found[0]->getId());
        $this->assertSame('peach', $found[1]->getId());
        $this->assertSame('apple', $found[2]->getId());
    }
}
