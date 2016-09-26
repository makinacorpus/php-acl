<?php

namespace MakinaCorpus\ACL\Impl;

use MakinaCorpus\ACL\EntryList;
use MakinaCorpus\ACL\Resource;
use MakinaCorpus\ACL\Store\EntryStoreInterface;

/**
 * Stores and fetch data on the resource target object property instead
 * of trying to load data from anywhere else, with fallback on another
 * default cache;
 */
class PropertyCacheEntryStoreProxy implements EntryStoreInterface
{
    const DEFAULT_PROPERTY = '_php_acl';

    private $store;
    private $property;

    public function __construct(EntryStoreInterface $store, $property = self::DEFAULT_PROPERTY)
    {
        $this->store = $store;
        $this->property = $property;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Resource $resource)
    {
        return $this->store->supports($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Resource $resource)
    {
        $object = $resource->getObject();

        if (is_object($object)) {
            unset($object->{$this->property});
        }

        parent::delete($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function load(Resource $resource)
    {
        $object = $resource->getObject();

        if (is_object($object)) {
            if (isset($object->{$this->property})) {
                return $object->{$this->property};
            }

            return $object->{$this->property} = $this->store->load($resource);
        }

        return $this->store->load($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function save(EntryList $list)
    {
        $object = $list->getResource()->getObject();

        $list = $this->store->save($list);

        if (is_object($object)) {
            $object->{$this->property} = $list;
        }

        return $list;
    }
}
