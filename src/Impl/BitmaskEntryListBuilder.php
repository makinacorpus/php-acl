<?php

namespace MakinaCorpus\ACL\Impl;

use MakinaCorpus\ACL\Collector\EntryListBuilderInterface;
use MakinaCorpus\ACL\Collector\EntryListBuilderTrait;
use MakinaCorpus\ACL\Identity;
use MakinaCorpus\ACL\Resource;

/**
 * Builds entry lists using the bitmask map
 */
final class BitmaskEntryListBuilder implements EntryListBuilderInterface
{
    use EntryListBuilderTrait;

    private $map;

    /**
     * Default constructor
     *
     * @param Resource $resource
     * @param mixed $object
     * @param BitmaskMap $map
     */
    public function __construct(Resource $resource, $object, BitmaskMap $map = null)
    {
        $this->resource = $resource;
        $this->object = $object;

        if (!$map) {
            $map = new BitmaskMap();
        }
        $this->map = $map;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToEntryList()
    {
        $masks = [];

        foreach ($this->entries as $type => $list) {
            foreach ($list as $id => $permissions) {
                $strings = array_keys(array_filter($permissions));
                $masks[Identity::getStringRepresentation($type, $id)] = $this->map->build($strings);
            }
        }

        return new BitmaskEntryList($this->map, $masks);
    }
}
