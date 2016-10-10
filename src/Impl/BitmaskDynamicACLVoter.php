<?php

namespace MakinaCorpus\ACL\Impl;

use MakinaCorpus\ACL\Resource;
use MakinaCorpus\ACL\Voter\DynamicACLVoter;

class BitmaskDynamicACLVoter extends DynamicACLVoter
{
    private $map;

    /**
     * Default constructor
     *
     * @param EntryStoreInterface[] $stores
     * @param EntryCollectorInterface[] $collectors
     * @param BitmaskMap $map
     */
    public function __construct(array $stores, array $collectors, BitmaskMap $map = null)
    {
        parent::__construct($stores, $collectors);

        if (!$map) {
            $map = new BitmaskMap();
        }
        $this->map = $map;
    }

    /**
     * {@inheritdoc}
     */
    protected function createBuilder(Resource $resource)
    {
        return new BitmaskEntryListBuilder($resource, $this->map);
    }
}
