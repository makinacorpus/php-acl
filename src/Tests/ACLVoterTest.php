<?php

namespace MakinaCorpus\ACL\Tests;

use MakinaCorpus\ACL\ACLVoter;
use MakinaCorpus\ACL\Manager;
use MakinaCorpus\ACL\Memory\MemoryEntryStore;
use MakinaCorpus\ACL\Permission;
use MakinaCorpus\ACL\Profile;
use MakinaCorpus\ACL\Resource;
use MakinaCorpus\ACL\Symfony\CollectEntryEvent;
use MakinaCorpus\ACL\Symfony\EventCollector;

use Symfony\Component\EventDispatcher\EventDispatcher;
use MakinaCorpus\ACL\ProfileSet;

class ACLVoterTest extends \PHPUnit_Framework_TestCase
{
    private $voter;
    private $collector;
    private $dispatcher;
    private $storage;
    private $manager;

    protected function createStorage()
    {
        return new MemoryEntryStore();
    }

    protected function getVoter()
    {
        return $this->voter;
    }

    protected function setUp()
    {
        $this->dispatcher = new EventDispatcher();
        $this->collector  = new EventCollector($this->dispatcher);
        $this->storage    = $this->createStorage();
        $this->voter      = new ACLVoter([$this->storage], [$this->collector]);
        $this->manager    = new Manager([$this->voter], [], []);
    }

    /**
     * Tests bacics, pretty much everything except edge case we'd find out later
     */
    public function testBasicFeatures()
    {
        // We are just going to do this:
        //  - group 1 (id is 100)
        //  - group 2 (id is 200)
        //  - User 1 is member of A (id is 1)
        //  - User 2 is member of A and B (id is 42)
        //  - content 1 to 5 are in A
        //  - content 6 to 10 are in B
        //  - content 11 to 15 are in both
        //  - content 16 to 20 are in none

        $groupAId = 100;
        $groupBId = 200;
        $user1Id  = 1;
        $user2Id  = 42;
        $ARange   = [1, 20];
        $BRange   = [21, 40];
        $ABRange  = [41, 60];
        $NoRange  = [61, 80];

        // Bootstrap environement and test cases
        $this->dispatcher->addListener(
            CollectEntryEvent::EVENT_COLLECT,
            function (CollectEntryEvent $event) use ($user1Id, $user2Id, $groupAId, $groupBId, $ARange, $BRange, $ABRange) {

                // We don't care about resource type here
                $builder  = $event->getBuilder();
                $resource = $builder->getResource();
                $id       = $resource->getId();

                if ($id >= $ARange[0] && $id <= $ARange[1]) {
                    $builder->add(Profile::GROUP, $groupAId, [Permission::VIEW, Permission::UPDATE, Permission::DELETE]);
                    $builder->add(Profile::USER, $user1Id, Permission::VIEW);
                } else if ($id >= $BRange[0] && $id <= $BRange[1]) {
                    $builder->add(Profile::GROUP, $groupBId, [Permission::VIEW, Permission::UPDATE, Permission::DELETE]);
                    $builder->add(Profile::USER, $user2Id, Permission::VIEW);
                } else if ($id >= $ABRange[0] && $id <= $ABRange[1]) {
                    $builder->add(Profile::GROUP, $groupAId, [Permission::VIEW, Permission::UPDATE, Permission::DELETE]);
                    $builder->add(Profile::USER, $user1Id, Permission::VIEW);
                    $builder->add(Profile::GROUP, $groupBId, [Permission::VIEW, Permission::UPDATE, Permission::DELETE]);
                    $builder->add(Profile::USER, $user2Id, Permission::VIEW);
                } else {
                    // Those are nowhere
                }
            })
        ;

        // Now we have a bootstrapped environnement, start testing things
        $start = microtime(true);

        $user1  = new Profile(Profile::USER, $user1Id);
        $user2  = new Profile(Profile::USER, $user2Id);
        $groupA = new Profile(Profile::GROUP, $groupAId);
        $groupB = new Profile(Profile::GROUP, $groupBId);
        $set1   = new ProfileSet([$user1, $groupA]);
        $set2   = new ProfileSet([$user2, $groupB]);

        // Test reapatibility
        for ($i = 0; $i < 1; ++$i) {
            // Test raw permissions
            for ($id = $ARange[0]; $id <= $ARange[1]; ++$id) {
                $resource = new Resource('content', $id);
                // Profiles
                $this->assertTrue($this->manager->isGranted($resource, $groupA, Permission::VIEW));
                $this->assertTrue($this->manager->isGranted($resource, $groupA, Permission::UPDATE));
                $this->assertTrue($this->manager->isGranted($resource, $groupA, Permission::DELETE));
                $this->assertTrue($this->manager->isGranted($resource, $user1,  Permission::VIEW));
                $this->assertFalse($this->manager->isGranted($resource, $user1,  Permission::UPDATE));
                $this->assertFalse($this->manager->isGranted($resource, $user1,  Permission::DELETE));
                $this->assertFalse($this->manager->isGranted($resource, $groupB, Permission::VIEW));
                $this->assertFalse($this->manager->isGranted($resource, $groupB, Permission::UPDATE));
                $this->assertFalse($this->manager->isGranted($resource, $groupB, Permission::DELETE));
                $this->assertFalse($this->manager->isGranted($resource, $user2,  Permission::VIEW));
                $this->assertFalse($this->manager->isGranted($resource, $user2,  Permission::UPDATE));
                $this->assertFalse($this->manager->isGranted($resource, $user2,  Permission::DELETE));
                // Profile sets
                $this->assertTrue($this->manager->isGranted($resource, $set1, Permission::VIEW));
                $this->assertTrue($this->manager->isGranted($resource, $set1, Permission::UPDATE));
                $this->assertTrue($this->manager->isGranted($resource, $set1, Permission::DELETE));
                $this->assertFalse($this->manager->isGranted($resource, $set2,  Permission::VIEW));
                $this->assertFalse($this->manager->isGranted($resource, $set2, Permission::UPDATE));
                $this->assertFalse($this->manager->isGranted($resource, $set2, Permission::DELETE));
            }
            for ($id = $BRange[0]; $id <= $BRange[1]; ++$id) {
                $resource = new Resource('content', $id);
                $this->assertFalse($this->manager->isGranted($resource, $groupA, Permission::VIEW));
                $this->assertFalse($this->manager->isGranted($resource, $groupA, Permission::UPDATE));
                $this->assertFalse($this->manager->isGranted($resource, $groupA, Permission::DELETE));
                $this->assertFalse($this->manager->isGranted($resource, $user1,  Permission::VIEW));
                $this->assertFalse($this->manager->isGranted($resource, $user1,  Permission::UPDATE));
                $this->assertFalse($this->manager->isGranted($resource, $user1,  Permission::DELETE));
                $this->assertTrue($this->manager->isGranted($resource, $groupB, Permission::VIEW));
                $this->assertTrue($this->manager->isGranted($resource, $groupB, Permission::UPDATE));
                $this->assertTrue($this->manager->isGranted($resource, $groupB, Permission::DELETE));
                $this->assertTrue($this->manager->isGranted($resource, $user2,  Permission::VIEW));
                $this->assertFalse($this->manager->isGranted($resource, $user2,  Permission::UPDATE));
                $this->assertFalse($this->manager->isGranted($resource, $user2,  Permission::DELETE));
                // Profile sets
                $this->assertFalse($this->manager->isGranted($resource, $set1, Permission::VIEW));
                $this->assertFalse($this->manager->isGranted($resource, $set1, Permission::UPDATE));
                $this->assertFalse($this->manager->isGranted($resource, $set1, Permission::DELETE));
                $this->assertTrue($this->manager->isGranted($resource, $set2,  Permission::VIEW));
                $this->assertTrue($this->manager->isGranted($resource, $set2, Permission::UPDATE));
                $this->assertTrue($this->manager->isGranted($resource, $set2, Permission::DELETE));
            }
            for ($id = $ABRange[0]; $id <= $ABRange[1]; ++$id) {
                $resource = new Resource('content', $id);
                $this->assertTrue($this->manager->isGranted($resource, $groupA, Permission::VIEW));
                $this->assertTrue($this->manager->isGranted($resource, $groupA, Permission::UPDATE));
                $this->assertTrue($this->manager->isGranted($resource, $groupA, Permission::DELETE));
                $this->assertTrue($this->manager->isGranted($resource, $user1,  Permission::VIEW));
                $this->assertFalse($this->manager->isGranted($resource, $user1,  Permission::UPDATE));
                $this->assertFalse($this->manager->isGranted($resource, $user1,  Permission::DELETE));
                $this->assertTrue($this->manager->isGranted($resource, $groupB, Permission::VIEW));
                $this->assertTrue($this->manager->isGranted($resource, $groupB, Permission::UPDATE));
                $this->assertTrue($this->manager->isGranted($resource, $groupB, Permission::DELETE));
                $this->assertTrue($this->manager->isGranted($resource, $user2,  Permission::VIEW));
                $this->assertFalse($this->manager->isGranted($resource, $user2,  Permission::UPDATE));
                $this->assertFalse($this->manager->isGranted($resource, $user2,  Permission::DELETE));
                // Profile sets
                $this->assertTrue($this->manager->isGranted($resource, $set1, Permission::VIEW));
                $this->assertTrue($this->manager->isGranted($resource, $set1, Permission::UPDATE));
                $this->assertTrue($this->manager->isGranted($resource, $set1, Permission::DELETE));
                $this->assertTrue($this->manager->isGranted($resource, $set2,  Permission::VIEW));
                $this->assertTrue($this->manager->isGranted($resource, $set2, Permission::UPDATE));
                $this->assertTrue($this->manager->isGranted($resource, $set2, Permission::DELETE));
            }
            for ($id = $NoRange[0]; $id <= $NoRange[1]; ++$id) {
                $resource = new Resource('content', $id);
                $this->assertFalse($this->manager->isGranted($resource, $groupA, Permission::VIEW));
                $this->assertFalse($this->manager->isGranted($resource, $groupA, Permission::UPDATE));
                $this->assertFalse($this->manager->isGranted($resource, $groupA, Permission::DELETE));
                $this->assertFalse($this->manager->isGranted($resource, $user1,  Permission::VIEW));
                $this->assertFalse($this->manager->isGranted($resource, $user1,  Permission::UPDATE));
                $this->assertFalse($this->manager->isGranted($resource, $user1,  Permission::DELETE));
                $this->assertFalse($this->manager->isGranted($resource, $groupB, Permission::VIEW));
                $this->assertFalse($this->manager->isGranted($resource, $groupB, Permission::UPDATE));
                $this->assertFalse($this->manager->isGranted($resource, $groupB, Permission::DELETE));
                $this->assertFalse($this->manager->isGranted($resource, $user2,  Permission::VIEW));
                $this->assertFalse($this->manager->isGranted($resource, $user2,  Permission::UPDATE));
                $this->assertFalse($this->manager->isGranted($resource, $user2,  Permission::DELETE));
                // Profile sets
                $this->assertFalse($this->manager->isGranted($resource, $set1, Permission::VIEW));
                $this->assertFalse($this->manager->isGranted($resource, $set1, Permission::UPDATE));
                $this->assertFalse($this->manager->isGranted($resource, $set1, Permission::DELETE));
                $this->assertFalse($this->manager->isGranted($resource, $set2,  Permission::VIEW));
                $this->assertFalse($this->manager->isGranted($resource, $set2, Permission::UPDATE));
                $this->assertFalse($this->manager->isGranted($resource, $set2, Permission::DELETE));
            }
        }

        $stop = microtime(true);
        echo "\nTook " . ceil(($stop - $start) * 1000) . " ms\n";
    }
}
