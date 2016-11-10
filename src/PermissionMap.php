<?php

namespace MakinaCorpus\ACL;

use MakinaCorpus\ACL\Permission;
use MakinaCorpus\ACL\Impl\NaiveEntryListBuilder;
use MakinaCorpus\ACL\Collector\EntryListBuilderInterface;

/**
 * Map of supported permissions
 */
class PermissionMap
{
    protected $map = [];

    /**
     * Default constructor
     *
     * @param int[] $map
     *   Keys are permission names, values are bitmasks
     */
    public function __construct(array $map = [])
    {
        if (!$map) {
            // Provide sensible defaults
            $map = [
                Permission::COMMENT => 1,
                Permission::CREATE => 2,
                Permission::DELETE => 4,
                Permission::HIDE => 8,
                Permission::LOCK => 16,
                Permission::MOVE => 32,
                Permission::SHARE => 64,
                Permission::SHOW => 128,
                Permission::TOUCH => 256,
                Permission::UNLOCK => 512,
                Permission::UPDATE => 1024,
                Permission::VIEW => 2048,
            ];
        }

        $this->map = $map;
    }

    /**
     * Create entry list builder
     *
     * @param Resource $resource
     * @param mixed $object
     *
     * @return EntryListBuilderInterface
     */
    public function createEntryListBuilder(Resource $resource, $object)
    {
        return new NaiveEntryListBuilder($resource, $object);
    }

    /**
     * Does this map supports the given permission
     *
     * @param string $permission
     *
     * @return boolean
     */
    public function supports($permission)
    {
        return isset($this->map[$permission]);
    }
}
