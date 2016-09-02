<?php

namespace MakinaCorpus\ACL\Impl\Native;

use MakinaCorpus\ACL\Collector\EntryCollectorInterface;
use MakinaCorpus\ACL\Collector\EntryListBuilder;
use MakinaCorpus\ACL\Profile;
use MakinaCorpus\ACL\Resource;
use MakinaCorpus\ACL\Store\EntryStoreInterface;
use MakinaCorpus\ACL\VoterInterface;

/**
 * Default voter, uses ACL stored as EntryList instances by one or more
 * entry stores
 */
class ACLVoter implements VoterInterface
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
    public function supports(Resource $resource)
    {
        foreach ($this->stores as $store) {
            if ($store->supports($resource)) {
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
     * @return EntryList
     */
    private function collectEntryListFor(Resource $resource)
    {
        // Having an empty list of collects is valid, it just means that the
        // business layer deals with permissions by itself, using the store
        // directly, which is definitely legal
        if (empty($this->collectors)) {
            return;
        }

        $builder = new EntryListBuilder($resource);

        foreach ($this->collectors as $collector) {
            if ($collector->supports($resource)) {
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
     * @return EntryList
     */
    private function getEntryListFor(Resource $resource)
    {
        $list = null;

        foreach ($this->stores as $store) {
            if ($store->supports($resource)) {
                if ($list = $store->load($resource)) {
                    break;
                }
            }
        }

        if (!$list) {
            $list = $this->collectEntryListFor($resource);

            // @todo should we call this at all?
            if ($list && $store) {
                $store->save($list);
            }
        }

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(Resource $resource, Profile $profile, $permission)
    {
        foreach ($this->stores as $store) {
            if ($store->supports($resource)) {
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
