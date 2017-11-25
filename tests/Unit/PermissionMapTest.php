<?php

namespace MakinaCorpus\ACL\Tests\Unit;

use MakinaCorpus\ACL\PermissionMap;
use MakinaCorpus\ACL\Permission;

class PermissionMapTest extends \PHPUnit_Framework_TestCase
{
    public function testBitMapIdentity()
    {
        $map = new PermissionMap();

        $this->assertSame(8 | 256, $map->build([Permission::DELETE, Permission::PUBLISH]));

        $this->assertNotEmpty($map->getBitMap());

        $this->assertSame(2048, $map->getBit(Permission::TOUCH));
    }

    public function testPermissionAdd()
    {
        $map = new PermissionMap();

        $this->assertFalse($map->supports('eat'));

        $map->addPermissions([
            'eat' => 524288,
        ]);

        $this->assertTrue($map->supports('eat'));

        $this->expectException(\InvalidArgumentException::class);
        $map->addPermissions([
            'another' => 4096,
        ]);
    }
}
