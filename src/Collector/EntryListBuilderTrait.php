<?php

namespace MakinaCorpus\ACL\Collector;

use MakinaCorpus\ACL\Resource;

/**
 * Builds entry lists using the bitmask map
 */
trait EntryListBuilderTrait /* implements EntryListBuilderInterface */
{
    private $resource;
    private $object;
    private $entries = [];

    /**
     * Default constructor
     *
     * @param Resource $resource
     * @param mixed $object
     */
    public function __construct(Resource $resource, $object)
    {
        $this->resource = $resource;
        $this->object = $object;
    }

    /**
     * Get object
     *
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
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
