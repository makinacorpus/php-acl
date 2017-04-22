<?php

namespace MakinaCorpus\ACL\Tests;

use MakinaCorpus\ACL\Impl\MemoryEntryStore;
use MakinaCorpus\ACL\Impl\NaiveEntryListBuilder;
use MakinaCorpus\ACL\Manager;
use MakinaCorpus\ACL\Permission;
use MakinaCorpus\ACL\Profile;
use MakinaCorpus\ACL\Resource;
use MakinaCorpus\ACL\Collector\CallbackProfileCollector;
use MakinaCorpus\ACL\Collector\ProfileSetBuilder;
use MakinaCorpus\ACL\Collector\CallbackEntryCollector;
use MakinaCorpus\ACL\Collector\EntryListBuilderInterface;
use MakinaCorpus\ACL\Impl\Symfony\ACLVoter;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DefaultTest extends \PHPUnit_Framework_TestCase
{
    const NON_EXISTING_PERMISSION = 'non_existing_permission';

    private $manager;

    protected function createPermissionMap()
    {
        return null;
    }

    protected function createStorages()
    {
        return [new MemoryEntryStore()];
    }

    protected function createResourceConverters()
    {
        return [];
    }

    protected function createResource($id)
    {
        return new Resource('content', $id);
    }

    /**
     * Do the real test, but adds some variations
     *
     * @param boolean $doPreload
     */
    protected function doTheRealTest($doPreload = false, $callbackFilter = false)
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

        $this->manager = new Manager(
            $this->createStorages(),
            [new CallbackEntryCollector(
                function (EntryListBuilderInterface $builder) use ($user1Id, $user2Id, $groupAId, $groupBId, $ARange, $BRange, $ABRange) {

                    // We don't care about resource type here
                    $resource = $builder->getResource();
                    $id       = $resource->getId();

                    if ($id >= $ARange[0] && $id <= $ARange[1]) {
                        $builder->add(Profile::GROUP, $groupAId, [Permission::VIEW, Permission::UPDATE, Permission::DELETE]);
                        $builder->add(Profile::USER, $user1Id, Permission::VIEW);
                        $builder->add(Profile::USER, $user1Id, DefaultTest::NON_EXISTING_PERMISSION);
                    } else if ($id >= $BRange[0] && $id <= $BRange[1]) {
                        $builder->add(Profile::GROUP, $groupBId, [Permission::VIEW, Permission::UPDATE, Permission::DELETE]);
                        $builder->add(Profile::USER, $user2Id, Permission::VIEW);
                        $builder->add(Profile::USER, $user2Id, DefaultTest::NON_EXISTING_PERMISSION);
                    } else if ($id >= $ABRange[0] && $id <= $ABRange[1]) {
                        $builder->add(Profile::GROUP, $groupAId, [Permission::VIEW, Permission::UPDATE, Permission::DELETE]);
                        $builder->add(Profile::USER, $user1Id, Permission::VIEW);
                        $builder->add(Profile::GROUP, $groupBId, [Permission::VIEW, Permission::UPDATE, Permission::DELETE]);
                        $builder->add(Profile::USER, $user2Id, Permission::VIEW);
                        $builder->add(Profile::USER, $user2Id, DefaultTest::NON_EXISTING_PERMISSION);
                    } else {
                        // Those are nowhere
                    }
                },
                [],
                $callbackFilter ? [Permission::VIEW, Permission::UPDATE, Permission::DELETE] : []
            )],
            [new CallbackProfileCollector(
                function (ProfileSetBuilder $builder) use ($user1Id, $user2Id, $groupAId, $groupBId) {

                    $object = $builder->getObject();

                    // For symfony testing
                    if ($object instanceof Token) {
                        $object = $object->getOriginalObject();
                    }

                    switch ($object) {
                        case $user1Id:
                            $builder->add(Profile::USER, $user1Id);
                            break;
                        case $user2Id:
                            $builder->add(Profile::USER, $user2Id);
                            break;
                        case $groupAId:
                            $builder->add(Profile::GROUP, $groupAId);
                            break;
                        case $groupBId:
                            $builder->add(Profile::GROUP, $groupBId);
                            break;
                        case 'set1':
                            $builder->add(Profile::USER, $user1Id);
                            $builder->add(Profile::GROUP, $groupAId);
                            break;
                        case 'set2':
                            $builder->add(Profile::USER, $user2Id);
                            $builder->add(Profile::GROUP, $groupBId);
                            break;
                    }
                }
            )],
            $this->createResourceConverters(),
            $this->createPermissionMap()
        );

        // Now we have a bootstrapped environnement, start testing things
        $start = microtime(true);
        $memoryStart = memory_get_usage();

        $user1  = $user1Id;
        $user2  = $user2Id;
        $groupA = $groupAId;
        $groupB = $groupBId;
        $set1   = 'set1';
        $set2   = 'set2';

        if ($doPreload) {
            $this->manager->preload('content', range(1, 80));
        }

        // We are also going to test Symfony voter
        $symfonyVoter = new ACLVoter($this->manager);

        // Test reapatibility
        for ($i = 0; $i < 3; ++$i) {
            // Test raw permissions
            for ($id = $ARange[0]; $id <= $ARange[1]; ++$id) {
                $resource = $this->createResource($id);
                // Profiles
                $this->assertTrue($this->manager->isGranted(Permission::VIEW, $resource, $groupA));
                $this->assertTrue($this->manager->isGranted(Permission::UPDATE, $resource, $groupA));
                $this->assertTrue($this->manager->isGranted(Permission::DELETE, $resource, $groupA));
                $this->assertTrue($this->manager->isGranted(Permission::VIEW, $resource, $user1));
                $this->assertFalse($this->manager->isGranted(Permission::UPDATE, $resource, $user1));
                $this->assertFalse($this->manager->isGranted(Permission::DELETE, $resource, $user1));
                $this->assertFalse($this->manager->isGranted(Permission::VIEW, $resource, $groupB));
                $this->assertFalse($this->manager->isGranted(Permission::UPDATE, $resource, $groupB));
                $this->assertFalse($this->manager->isGranted(Permission::DELETE, $resource, $groupB));
                $this->assertFalse($this->manager->isGranted(Permission::VIEW, $resource, $user2));
                $this->assertFalse($this->manager->isGranted(Permission::UPDATE, $resource, $user2));
                $this->assertFalse($this->manager->isGranted(Permission::DELETE, $resource, $user2));
                // Profile sets
                $this->assertTrue($this->manager->isGranted(Permission::VIEW, $resource, $set1));
                $this->assertTrue($this->manager->isGranted(Permission::UPDATE, $resource, $set1));
                $this->assertTrue($this->manager->isGranted(Permission::DELETE, $resource, $set1));
                $this->assertFalse($this->manager->isGranted(Permission::VIEW, $resource, $set2));
                $this->assertFalse($this->manager->isGranted(Permission::UPDATE, $resource, $set2));
                $this->assertFalse($this->manager->isGranted(Permission::DELETE, $resource, $set2));
                // Non supported permissions should always return false, no matter what
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $groupA));
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $user1));
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $groupB));
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $user2));
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $set1));
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $set2));
                // Assert the DENY, ALLOW, ABSTAIN from the vote() method
                $this->assertSame(Manager::ALLOW, $this->manager->vote($groupA, $resource, Permission::VIEW));
                $this->assertSame(Manager::DENY, $this->manager->vote($user1, $resource, Permission::UPDATE));
                $this->assertSame(Manager::ABSTAIN, $this->manager->vote($groupA, $resource, self::NON_EXISTING_PERMISSION));
                $this->assertSame(Manager::ABSTAIN, $this->manager->vote($groupA, 'stupid_unsupported_resource', self::NON_EXISTING_PERMISSION));
                $this->assertSame(Manager::ABSTAIN, $this->manager->vote($groupA, 'stupid_unsupported_resource', Permission::VIEW));
                // Assert that symfony voter supports it, there is no use in
                // asserting that thousands of time, let's just do it here.
                $this->assertSame(Voter::ACCESS_GRANTED, $symfonyVoter->vote(new Token($groupA), $resource, [Permission::VIEW]));
                $this->assertSame(Voter::ACCESS_GRANTED, $symfonyVoter->vote(new Token($groupA), $resource, [Permission::UPDATE]));
                $this->assertSame(Voter::ACCESS_GRANTED, $symfonyVoter->vote(new Token($groupA), $resource, [Permission::DELETE]));
                $this->assertSame(Voter::ACCESS_GRANTED, $symfonyVoter->vote(new Token($user1), $resource, [Permission::VIEW]));
                $this->assertSame(Voter::ACCESS_DENIED, $symfonyVoter->vote(new Token($user1), $resource, [Permission::UPDATE]));
                $this->assertSame(Voter::ACCESS_DENIED, $symfonyVoter->vote(new Token($user1), $resource, [Permission::DELETE]));
                $this->assertSame(Voter::ACCESS_DENIED, $symfonyVoter->vote(new Token($groupB), $resource, [Permission::VIEW]));
                $this->assertSame(Voter::ACCESS_DENIED, $symfonyVoter->vote(new Token($groupB), $resource, [Permission::UPDATE]));
                $this->assertSame(Voter::ACCESS_DENIED, $symfonyVoter->vote(new Token($groupB), $resource, [Permission::DELETE]));
                $this->assertSame(Voter::ACCESS_DENIED, $symfonyVoter->vote(new Token($user2), $resource, [Permission::VIEW]));
                $this->assertSame(Voter::ACCESS_DENIED, $symfonyVoter->vote(new Token($user2), $resource, [Permission::UPDATE]));
                $this->assertSame(Voter::ACCESS_DENIED, $symfonyVoter->vote(new Token($user2), $resource, [Permission::DELETE]));
                // Assert that symfony voter abstains for non supported permissions
                $this->assertSame(Voter::ACCESS_ABSTAIN, $symfonyVoter->vote(new Token($groupA), $resource, [self::NON_EXISTING_PERMISSION]));
                $this->assertSame(Voter::ACCESS_ABSTAIN, $symfonyVoter->vote(new Token($groupA), $resource, [self::NON_EXISTING_PERMISSION]));
                $this->assertSame(Voter::ACCESS_ABSTAIN, $symfonyVoter->vote(new Token($groupA), $resource, [self::NON_EXISTING_PERMISSION]));
                $this->assertSame(Voter::ACCESS_ABSTAIN, $symfonyVoter->vote(new Token($user1), $resource, [self::NON_EXISTING_PERMISSION]));
                $this->assertSame(Voter::ACCESS_ABSTAIN, $symfonyVoter->vote(new Token($user1), $resource, [self::NON_EXISTING_PERMISSION]));
                $this->assertSame(Voter::ACCESS_ABSTAIN, $symfonyVoter->vote(new Token($user1), $resource, [self::NON_EXISTING_PERMISSION]));
                $this->assertSame(Voter::ACCESS_ABSTAIN, $symfonyVoter->vote(new Token($groupB), $resource, [self::NON_EXISTING_PERMISSION]));
                $this->assertSame(Voter::ACCESS_ABSTAIN, $symfonyVoter->vote(new Token($groupB), $resource, [self::NON_EXISTING_PERMISSION]));
                $this->assertSame(Voter::ACCESS_ABSTAIN, $symfonyVoter->vote(new Token($groupB), $resource, [self::NON_EXISTING_PERMISSION]));
                $this->assertSame(Voter::ACCESS_ABSTAIN, $symfonyVoter->vote(new Token($user2), $resource, [self::NON_EXISTING_PERMISSION]));
                $this->assertSame(Voter::ACCESS_ABSTAIN, $symfonyVoter->vote(new Token($user2), $resource, [self::NON_EXISTING_PERMISSION]));
                $this->assertSame(Voter::ACCESS_ABSTAIN, $symfonyVoter->vote(new Token($user2), $resource, [self::NON_EXISTING_PERMISSION]));
            }
            for ($id = $BRange[0]; $id <= $BRange[1]; ++$id) {
                $resource = $this->createResource($id);
                $this->assertFalse($this->manager->isGranted(Permission::VIEW, $resource, $groupA));
                $this->assertFalse($this->manager->isGranted(Permission::UPDATE, $resource, $groupA));
                $this->assertFalse($this->manager->isGranted(Permission::DELETE, $resource, $groupA));
                $this->assertFalse($this->manager->isGranted(Permission::VIEW, $resource, $user1));
                $this->assertFalse($this->manager->isGranted(Permission::UPDATE, $resource, $user1));
                $this->assertFalse($this->manager->isGranted(Permission::DELETE, $resource, $user1));
                $this->assertTrue($this->manager->isGranted(Permission::VIEW, $resource, $groupB));
                $this->assertTrue($this->manager->isGranted(Permission::UPDATE, $resource, $groupB));
                $this->assertTrue($this->manager->isGranted(Permission::DELETE, $resource, $groupB));
                $this->assertTrue($this->manager->isGranted(Permission::VIEW, $resource, $user2));
                $this->assertFalse($this->manager->isGranted(Permission::UPDATE, $resource, $user2));
                $this->assertFalse($this->manager->isGranted(Permission::DELETE, $resource, $user2));
                // Profile sets
                $this->assertFalse($this->manager->isGranted(Permission::VIEW, $resource, $set1));
                $this->assertFalse($this->manager->isGranted(Permission::UPDATE, $resource, $set1));
                $this->assertFalse($this->manager->isGranted(Permission::DELETE, $resource, $set1));
                $this->assertTrue($this->manager->isGranted(Permission::VIEW, $resource, $set2));
                $this->assertTrue($this->manager->isGranted(Permission::UPDATE, $resource, $set2));
                $this->assertTrue($this->manager->isGranted(Permission::DELETE, $resource, $set2));
                // Non supported permissions should always return false, no matter what
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $groupA));
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $user1));
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $groupB));
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $user2));
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $set1));
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $set2));
            }
            for ($id = $ABRange[0]; $id <= $ABRange[1]; ++$id) {
                $resource = $this->createResource($id);
                $this->assertTrue($this->manager->isGranted(Permission::VIEW, $resource, $groupA));
                $this->assertTrue($this->manager->isGranted(Permission::UPDATE, $resource, $groupA));
                $this->assertTrue($this->manager->isGranted(Permission::DELETE, $resource, $groupA));
                $this->assertTrue($this->manager->isGranted(Permission::VIEW, $resource, $user1));
                $this->assertFalse($this->manager->isGranted(Permission::UPDATE, $resource, $user1));
                $this->assertFalse($this->manager->isGranted(Permission::DELETE, $resource, $user1));
                $this->assertTrue($this->manager->isGranted(Permission::VIEW, $resource, $groupB));
                $this->assertTrue($this->manager->isGranted(Permission::UPDATE, $resource, $groupB));
                $this->assertTrue($this->manager->isGranted(Permission::DELETE, $resource, $groupB));
                $this->assertTrue($this->manager->isGranted(Permission::VIEW, $resource, $user2));
                $this->assertFalse($this->manager->isGranted(Permission::UPDATE, $resource, $user2));
                $this->assertFalse($this->manager->isGranted(Permission::DELETE, $resource, $user2));
                // Profile sets
                $this->assertTrue($this->manager->isGranted(Permission::VIEW, $resource, $set1));
                $this->assertTrue($this->manager->isGranted(Permission::UPDATE, $resource, $set1));
                $this->assertTrue($this->manager->isGranted(Permission::DELETE, $resource, $set1));
                $this->assertTrue($this->manager->isGranted(Permission::VIEW, $resource, $set2));
                $this->assertTrue($this->manager->isGranted(Permission::UPDATE, $resource, $set2));
                $this->assertTrue($this->manager->isGranted(Permission::DELETE, $resource, $set2));
                // Non supported permissions should always return false, no matter what
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $groupA));
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $user1));
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $groupB));
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $user2));
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $set1));
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $set2));
            }
            for ($id = $NoRange[0]; $id <= $NoRange[1]; ++$id) {
                $resource = $this->createResource($id);
                $this->assertFalse($this->manager->isGranted(Permission::VIEW, $resource, $groupA));
                $this->assertFalse($this->manager->isGranted(Permission::UPDATE, $resource, $groupA));
                $this->assertFalse($this->manager->isGranted(Permission::DELETE, $resource, $groupA));
                $this->assertFalse($this->manager->isGranted(Permission::VIEW, $resource, $user1));
                $this->assertFalse($this->manager->isGranted(Permission::UPDATE, $resource, $user1));
                $this->assertFalse($this->manager->isGranted(Permission::DELETE, $resource, $user1));
                $this->assertFalse($this->manager->isGranted(Permission::VIEW, $resource, $groupB));
                $this->assertFalse($this->manager->isGranted(Permission::UPDATE, $resource, $groupB));
                $this->assertFalse($this->manager->isGranted(Permission::DELETE, $resource, $groupB));
                $this->assertFalse($this->manager->isGranted(Permission::VIEW, $resource, $user2));
                $this->assertFalse($this->manager->isGranted(Permission::UPDATE, $resource, $user2));
                $this->assertFalse($this->manager->isGranted(Permission::DELETE, $resource, $user2));
                // Profile sets
                $this->assertFalse($this->manager->isGranted(Permission::VIEW, $resource, $set1));
                $this->assertFalse($this->manager->isGranted(Permission::UPDATE, $resource, $set1));
                $this->assertFalse($this->manager->isGranted(Permission::DELETE, $resource, $set1));
                $this->assertFalse($this->manager->isGranted(Permission::VIEW, $resource, $set2));
                $this->assertFalse($this->manager->isGranted(Permission::UPDATE, $resource, $set2));
                $this->assertFalse($this->manager->isGranted(Permission::DELETE, $resource, $set2));
                // Non supported permissions should always return false, no matter what
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $groupA));
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $user1));
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $groupB));
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $user2));
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $set1));
                $this->assertFalse($this->manager->isGranted(self::NON_EXISTING_PERMISSION, $resource, $set2));
            }
        }

        $stop = microtime(true);
        $memoryStop = memory_get_usage();
        echo "\n" . get_class($this) . ($callbackFilter ? ' F' : '' ) . ($doPreload ? ' P' : '' ) . ': ' . ceil(($stop - $start) * 1000) . " ms / " . ($memoryStop - $memoryStart) . " bytes\n";
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

    /**
     * Tests bacics, pretty much everything but with preload
     */
    public function testWithCallbackFilter()
    {
        return $this->doTheRealTest(true, true);
    }
}
