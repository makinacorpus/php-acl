<?php

namespace MakinaCorpus\ACL\Symfony;

use MakinaCorpus\ACL\EntryListBuilder;
use MakinaCorpus\ACL\Resource;

use Symfony\Component\EventDispatcher\Event;

/**
 * Collect entries using the Symfony event dispatcher
 */
class CollectEntryEvent extends Event
{
    const EVENT_COLLECT = 'php_acl_collect';

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
     * Get original object
     *
     * @return mixed
     */
    public function getObject()
    {
        if ($this->builder->getResource()->hasObject()) {
            return null;
        }

        return $this->builder->getResource()->getObject();
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
