<?php

namespace MakinaCorpus\ACL\Tests;

use MakinaCorpus\ACL\Impl\MemoryEntryStore;
use MakinaCorpus\ACL\Impl\Symfony\CollectEntryEvent;
use MakinaCorpus\ACL\Impl\Symfony\CollectProfileEvent;
use MakinaCorpus\ACL\Impl\Symfony\EventEntryCollector;
use MakinaCorpus\ACL\Impl\Symfony\EventProfileCollector;
use MakinaCorpus\ACL\Manager;
use MakinaCorpus\ACL\Permission;
use MakinaCorpus\ACL\Profile;
use MakinaCorpus\ACL\Resource;
use MakinaCorpus\ACL\Voter\DynamicACLVoter;

use Symfony\Component\EventDispatcher\EventDispatcher;

class NaiveVoterTest extends \PHPUnit_Framework_TestCase
{
    private $voter;
    private $entryCollector;
    private $profileCollector;
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

    protected function createVoter($storage, $collector)
    {
        return new DynamicACLVoter([$storage], [$collector]);
    }

    protected function setUp()
    {
        $this->dispatcher       = new EventDispatcher();
        $this->entryCollector   = new EventEntryCollector($this->dispatcher);
        $this->profileCollector = new EventProfileCollector($this->dispatcher);
        $this->storage          = $this->createStorage();
        $this->voter            = $this->createVoter($this->storage, $this->entryCollector);
        $this->manager          = new Manager([$this->voter], [$this->profileCollector], []);
    }

    /**
     * Do the real test, but adds some variations
     *
     * @param boolean $doPreload
     */
    protected function doTheRealTest($doPreload = false)
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

        $this->dispatcher->addListener(
            CollectProfileEvent::EVENT_COLLECT,
            function (CollectProfileEvent $event) use ($user1Id, $user2Id, $groupAId, $groupBId) {
                switch ($event->getBuilder()->getObject()) {
                    case $user1Id:
                        $event->getBuilder()->add(Profile::USER, $user1Id);
                        break;
                    case $user2Id:
                        $event->getBuilder()->add(Profile::USER, $user2Id);
                        break;
                    case $groupAId:
                        $event->getBuilder()->add(Profile::GROUP, $groupAId);
                        break;
                    case $groupBId:
                        $event->getBuilder()->add(Profile::GROUP, $groupBId);
                        break;
                    case 'set1':
                        $event->getBuilder()->add(Profile::USER, $user1Id);
                        $event->getBuilder()->add(Profile::GROUP, $groupAId);
                        break;
                    case 'set2':
                        $event->getBuilder()->add(Profile::USER, $user2Id);
                        $event->getBuilder()->add(Profile::GROUP, $groupBId);
                        break;
                }
            })
        ;

        // Now we have a bootstrapped environnement, start testing things
        $start = microtime(true);

        $user1  = $user1Id;
        $user2  = $user2Id;
        $groupA = $groupAId;
        $groupB = $groupBId;
        $set1   = 'set1';
        $set2   = 'set2';

        if ($doPreload) {
            $this->manager->preload('content', range(1, 80));
        }

        // Test reapatibility
        for ($i = 0; $i < 3; ++$i) {
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
