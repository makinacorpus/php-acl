<?php

namespace MakinaCorpus\ACL\Impl\Symfony;

use MakinaCorpus\ACL\Collector\EntryListBuilder;
use MakinaCorpus\ACL\Resource;

use Symfony\Component\EventDispatcher\Event;

/**
 * Collects ACL entries for resources
 */
class CollectEntryEvent extends Event
{
    const EVENT_COLLECT = 'php_acl.collect.entry';

    private $builder;

    /**
     * Default constructor
     *
     * @param EntryListBuilder $builder
     */
    public function __construct(EntryListBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Get resource type
     *
     * @return string
     */
    public function getResourceType()
    {
        return $this->builder->getResource()->getType();
    }

    /**
     * Get entry list builder
     *
     * @return EntryListBuilder
     */
    public function getBuilder()
    {
        return $this->builder;
    }
}
