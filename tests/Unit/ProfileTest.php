<?php

namespace MakinaCorpus\ACL\Tests\Unit;

use MakinaCorpus\ACL\Profile;
use MakinaCorpus\ACL\ProfileSet;

class ProfileTest extends \PHPUnit_Framework_TestCase
{
    public function testProfileIdentity()
    {
        $profile1 = new Profile('sheep', '7');
        $this->assertSame('sheep', $profile1->getType());
        $this->assertSame('7', $profile1->getId());

        $profile2 = new Profile('sheep', 7);
        $this->assertSame('sheep', $profile2->getType());
        $this->assertSame('7', $profile2->getId());

        $false1 = new Profile('sheep', 12);
        $false2 = new Profile('user', 7);

        $this->assertTrue($profile1->equals($profile2));
        $this->assertTrue($profile2->equals($profile1));

        $this->assertFalse($false1->equals($profile2));
        $this->assertFalse($profile1->equals($false1));

        $this->assertFalse($false2->equals($profile2));
        $this->assertFalse($profile1->equals($false2));
    }

    public function testProfileSet()
    {
        $set1 = ProfileSet::createFromProfiles([
            new Profile('sheep', 'black'),
            new Profile('user', 12),
            new Profile('user', 13),
        ]);

        $set2 = ProfileSet::createFromArray([
            'sheep' => ['black'],
            'user' => [12, 13],
        ]);

        foreach ([$set1, $set2] as $set) {
            $this->assertFalse($set->isEmpty());
            $this->assertSame('sheep:black;user:12,13', $set->getCacheIdentifier());
            $this->assertTrue($set->is('sheep', 'black'));
            $this->assertTrue($set->is('user', 12));
            $this->assertTrue($set->is('user', '12'));
            $this->assertFalse($set->is('user', 7));
            $this->assertFalse($set->is('group', 12));
        }
    }
}
