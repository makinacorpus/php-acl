<?php

namespace MakinaCorpus\ACL\Impl\Symfony;

use MakinaCorpus\ACL\Collector\ProfileSetBuilder;

use Symfony\Component\EventDispatcher\Event;

/**
 * Collects ACL entries for resources
 */
class CollectProfileEvent extends Event
{
    const EVENT_COLLECT = 'php_acl.collect.profile';

    private $builder;

    /**
     * Default constructor
     *
     * @param ProfileSetBuilder $builder
     */
    public function __construct(ProfileSetBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Get original object
     *
     * @return mixed
     */
    public function getObject()
    {
        return $this->builder->getObject();
    }

    /**
     * Get entry list builder
     *
     * @return ProfileSetBuilder
     */
    public function getBuilder()
    {
        return $this->builder;
    }
}
