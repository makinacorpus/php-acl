<?php

namespace MakinaCorpus\ACL\Voter;

use MakinaCorpus\ACL\Collector\EntryCollectorInterface;
use MakinaCorpus\ACL\EntryListInterface;
use MakinaCorpus\ACL\Impl\NaiveEntryListBuilder;
use MakinaCorpus\ACL\Profile;
use MakinaCorpus\ACL\Resource;
use MakinaCorpus\ACL\ResourceCollection;
use MakinaCorpus\ACL\Store\EntryStoreInterface;
use MakinaCorpus\ACL\Voter\VoterInterface;

/**
 * Same behavior as the ACLVoter, but allows to dynamically build ACL lists
 * if necessary.
 */
class DynamicACLVoter implements VoterInterface
{
    private $stores = [];
    private $collectors = [];

    /**
     * Default constructor
     *
     * @param EntryStoreInterface[] $stores
     * @param EntryCollectorInterface[] $collectors
     */
    public function __construct(array $stores, array $collectors)
    {
        $this->stores = $stores;
        $this->collectors = $collectors;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        foreach ($this->stores as $store) {
            if ($store->supports($type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Collect entry list for the given resource
     *
     * @param Resource $resource
     *
     * @return EntryListInterface
     */
    private function collectEntryListFor(Resource $resource)
    {
        // Having an empty list of collects is valid, it just means that the
        // business layer deals with permissions by itself, using the store
        // directly, which is definitely legal
        if (empty($this->collectors)) {
            return;
        }

        $builder = new NaiveEntryListBuilder($resource);

        foreach ($this->collectors as $collector) {
            if ($collector->supports($resource->getType())) {
                $collector->collect($builder);
            }
        }

        return $builder->convertToEntryList();
    }

    /**
     * Get entry list for
     *
     * @param Resource $resource
     *
     * @return EntryListInterface
     */
    private function getEntryListFor(Resource $resource)
    {
        $list = null;

        foreach ($this->stores as $store) {
            if ($store->supports($resource->getType())) {
                if ($list = $store->load($resource)) {
                    break;
                }
            }
        }

        if (!$list) {
            $list = $this->collectEntryListFor($resource);

            // @todo should we call this at all?
            if ($list && $store) {
                $store->save($resource, $list);
            }
        }

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    public function preload(ResourceCollection $resources)
    {
        foreach ($this->stores as $store) {
            $store->loadAll($resources);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function vote(Resource $resource, Profile $profile, $permission)
    {
        foreach ($this->stores as $store) {
            if ($store->supports($resource->getType())) {
                if ($list = $this->getEntryListFor($resource)) {
                    if ($list->hasPermissionFor($profile, $permission)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
