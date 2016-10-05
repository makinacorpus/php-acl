<?php

namespace MakinaCorpus\ACL\Impl;

use MakinaCorpus\ACL\Profile;
use MakinaCorpus\ACL\Resource;
use MakinaCorpus\ACL\Collector\EntryListBuilderInterface;

/**
 * Builds entry lists
 */
final class NaiveEntryListBuilder implements EntryListBuilderInterface
{
    private $resource;
    private $entries = [];

    /**
     * Default constructor
     *
     * @param Resource $resource
     */
    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToEntryList()
    {
        $entries = [];
        //$map = new BitmaskMap();

        foreach ($this->entries as $type => $list) {
            foreach ($list as $id => $permissions) {
                // Here the original profile object is unnecessary, since it is
                // only meant to deal with ACL storage in the end
                $profile = new Profile($type, $id);
                $entries[] = new NaiveEntry($profile, array_keys($permissions));
            }
        }

        return new NaiveEntryList($this->resource, $entries);
    }

    /**
     * {@inheritdoc}
     */
    public function add($type, $id, $permission)
    {
        // @todo should we fail on overwrite?
        if (is_array($permission)) {
            foreach ($permission as $real) {
                $this->entries[$type][(string)$id][$real] = true;
            }
        } else {
            $this->entries[$type][(string)$id][$permission] = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function relocateType($previous, $next, $merge = true)
    {
        if (!isset($this->entries[$previous])) {
            return; // @todo should it really be silent
        }

        if (isset($this->entries[$next])) {
            if (!$merge) {
                unset($this->entries[$next]);
            }
        }

        foreach ($this->entries[$previous] as $id => $permissions) {
            foreach ($permissions as $permission => $value) {
                $this->entries[$next][$id][$permission] = true;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function relocate($previousType, $previousId, $nextType, $nextId, $merge = true)
    {
        $previousId = (string)$previousId;
        $nextId = (string)$nextId;

        if (!isset($this->entries[$previousType][$previousId])) {
            return; // @todo should it really be silent
        }

        if (isset($this->entries[$nextType][$nextId])) {
            if (!$merge) {
                unset($this->entries[$nextType][$nextId]);
            }
        }

        foreach ($this->entries[$previousType][$previousId] as $permission => $value) {
            $this->entries[$nextType][$nextId][$permission] = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has($type, $id = null, $permission = null)
    {
        if (null === $id) {
            return isset($this->entries[$type]);
        } else if (null === $permission) {
            return isset($this->entries[$type][(string)$id]);
        } else {
            return isset($this->entries[$type][(string)$id][$permission]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($type, $id = null, $permission = null)
    {
        if (null === $id) {
            unset($this->entries[$type]);
        } else if (null === $permission) {
            unset($this->entries[$type][(string)$id]);
        } else {
            unset($this->entries[$type][(string)$id][$permission]);
        }
    }
}
