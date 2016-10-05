<?php

namespace MakinaCorpus\ACL\Impl\Symfony;

use MakinaCorpus\ACL\Collector\EntryListBuilderInterface;

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
     * @param EntryListBuilderInterface $builder
     */
    public function __construct(EntryListBuilderInterface $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Get entry list builder
     *
     * @return EntryListBuilderInterface
     */
    public function getBuilder()
    {
        return $this->builder;
    }
}
