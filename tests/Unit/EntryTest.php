<?php

namespace MakinaCorpus\ACL\Tests\Unit;

use MakinaCorpus\ACL\Entry;
use MakinaCorpus\ACL\Permission;
use MakinaCorpus\ACL\Profile;

class EntryTest extends \PHPUnit_Framework_TestCase
{
    public function testEntry()
    {
        $entry = new Entry('group', 'administrators', [
            Permission::VIEW,
            Permission::DELETE,
        ]);

        $this->assertFalse($entry->isEmpty());
        $this->assertTrue($entry->isFor('group', 'administrators'));
        $this->assertFalse($entry->isFor('group', 12));
        $this->assertFalse($entry->isFor('user', 'administrators'));

        $this->assertTrue($entry->hasPermission(Permission::VIEW));
        $this->assertTrue($entry->hasPermission(Permission::DELETE));
        $this->assertFalse($entry->hasPermission(Permission::UPDATE));

        $this->assertTrue((new Profile('group', 'administrators'))->equals($entry->getProfile()));
    }
}
