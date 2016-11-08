<?php

namespace MakinaCorpus\ACL;

use MakinaCorpus\ACL\Collector\EntryCollectorInterface;
use MakinaCorpus\ACL\Collector\ProfileCollectorInterface;
use MakinaCorpus\ACL\Collector\ProfileSetBuilder;
use MakinaCorpus\ACL\Converter\ResourceConverterInterface;
use MakinaCorpus\ACL\Store\EntryStoreInterface;

final class Manager
{
    private $entryStores = [];
    private $resourceCollectors = [];
    private $profileCollectors = [];
    private $resourceConverters = [];
    private $permissionMap;
    private $profileCache = [];
    private $debug = false;

    /**
     * Default constructor
     *
     * @param EntryStoreInterface[] $entryStores
     * @param EntryCollectorInterface[] $resourceCollectors
     * @param ProfileCollectorInterface[] $profileCollectors
     * @param ResourceConverterInterface[] $resourceConverters
     * @param PermissionMap $permissionMap
     * @param boolean $debug
     */
    public function __construct(
        array $entryStores,
        array $resourceCollectors,
        array $profileCollectors = [],
        array $resourceConverters = [],
        PermissionMap $permissionMap = null,
        $debug = false
    ) {
        $this->entryStores = $entryStores;
        $this->resourceCollectors = $resourceCollectors;
        $this->profileCollectors = $profileCollectors;
        $this->resourceConverters = $resourceConverters;
        $this->permissionMap = $permissionMap;

        if (null === $this->permissionMap) {
            $this->permissionMap = new PermissionMap();
        }

        $this->setDebug($debug);
    }

    /**
     * Set debug mode
     *
     * @param boolean $debug
     */
    public function setDebug($debug = true)
    {
        $this->debug = $debug;
    }

    /**
     * Convert object to resource
     *
     * @param mixed $object
     *
     * @return Resource
     */
    private function expandResource($object)
    {
        if ($object instanceof Resource) {
            return $object;
        }

        foreach ($this->resourceConverters as $converter) {
            $resource = $converter->convert($object);
            if ($resource) {
                return $resource;
            }
        }

        throw new \InvalidArgumentException("cannot convert object to resource");
    }

    /**
     * Collect entry list for the given resource
     *
     * @param Resource $resource
     * @param mixed $object
     *
     * @return EntryListInterface
     */
    private function collectEntryListFor(Resource $resource, $object)
    {
        // Having an empty list of collects is valid, it just means that the
        // business layer deals with permissions by itself, using the store
        // directly, which is definitely legal
        if (empty($this->resourceCollectors)) {
            return;
        }

        $builder = $this->permissionMap->createEntryListBuilder($resource, $object);

        foreach ($this->resourceCollectors as $collector) {
            if ($collector->supports($resource->getType())) {
                $collector->collectEntryLists($builder);
            }
        }

        return $builder->convertToEntryList();
    }

    /**
     * Get entry list for
     *
     * @param Resource $resource
     *
     * @return EntryListInterface
     */
    private function getEntryListFor(Resource $resource, $object)
    {
        $list = null;
        $store = null;

        foreach ($this->entryStores as $store) {
            if ($store->supports($resource->getType())) {
                if ($list = $store->load($resource)) {
                    break;
                }
            }
        }

        if (!$list) {
            $list = $this->collectEntryListFor($resource, $object);

            // @todo should we call this at all?
            if ($list && !$list->isEmpty() && $store) {
                $store->save($resource, $list);
            }
        }

        return $list;
    }

    /**
     * Collect entry list for the given resource
     *
     * @param mixed $object
     *
     * @return ProfileSet
     */
    private function collectProfileSetFor($object)
    {
        // Having an empty list of collects is valid, it just means that the
        // business layer deals with permissions by itself, using the store
        // directly, which is definitely legal
        if (empty($this->profileCollectors)) {
            return new ProfileSet([], $object);
        }

        $builder = new ProfileSetBuilder($object);

        foreach ($this->profileCollectors as $collector) {
            $collector->collectProfiles($builder);
        }

        return $builder->convertToProfileSet();
    }

    /**
     * Convert object to profile
     *
     * @param mixed $object
     *
     * @return ProfileSet
     */
    private function expandProfile($object)
    {
        if ($object instanceof ProfileSet) {
            return $object;
        }
        if ($object instanceof Profile) {
            return new ProfileSet([$object]);
        }

        $id = Identity::computeUniqueIdentifier($object);

        if (null === $id) {
            return $this->collectProfileSetFor($object);
        }

        if (isset($this->profileCache[$id])) {
            return $this->profileCache[$id];
        }

        return $this->profileCache[$id] = $this->collectProfileSetFor($object);
    }

    /**
     * Do the real permissions check
     *
     * @param mixed $object
     * @param ProfileSet $profiles
     * @param string $permission
     *
     * @return boolean
     */
    private function doCheck($object, ProfileSet $profiles, $permission)
    {
        if ($object instanceof Resource) {
            $resource = $object;
        } else {
            $resource = $this->expandResource($object);
        }

        $list = $this->getEntryListFor($resource, $object);

        if (!$list || $list->isEmpty()) {
            return false;
        }

        foreach ($profiles->getAll() as $profile) {
            if ($list->hasPermissionFor($profile, $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Preload data if necessary for resources
     *
     * Data will get cached, making the permission checks a lot faster.
     *
     * @param ResourceCollection $resource
     */
    private function doPreload(ResourceCollection $resources)
    {
        foreach ($this->entryStores as $store) {
            if ($store->supports($resources->getType())) {
                $store->loadAll($resources);
            }
        }
    }

    /**
     * Create a resource collection
     *
     * @param string $type
     * @param int[]|string[] $idList
     *
     * @return ResourceCollection
     */
    private function createCollection($type, array $idList)
    {
        return new ResourceCollection($type, $idList);
    }

    /**
     * Empty caches
     */
    public function refresh()
    {
        $this->profileCache = [];
    }

    /**
     * Collect entry list for given resource
     *
     * @param mixed $object
     *   A resource
     *
     * @return EntryListInterface[]
     */
    public function collectEntryListAll($object)
    {
        try {
            $resource = $this->expandResource($object);
        } catch (\InvalidArgumentException $e) {
            if ($this->debug) {
                // @todo let exception or trigger meaningful debug message
            }

            return [];
        }

        $ret = [];
        $type = $resource->getType();

        foreach ($this->entryStores as $store) {
            if ($store->supports($type)) {
                if ($list = $this->getEntryListFor($resource)) {
                    $ret[] = $list;
                }
            }
        }

        return $ret;
    }

    /**
     * Collect profiles for the given object
     *
     * @param mixed $object
     *
     * @return ProfileSet
     */
    public function collectProfiles($object)
    {
        return $this->expandProfile($object);
    }

    /**
     * Does this manager supports the given permission
     *
     * @param string $permission
     */
    public function supportsPermission($permission)
    {
        return $this->permissionMap->supports($permission);
    }

    /**
     * Is the given object supported
     *
     * @param mixed $object
     *
     * @return boolean
     */
    public function supportsResource($object)
    {
        // @todo find a better way
        try {
            $this->expandResource($object);
            return true;
        } catch (\InvalidArgumentException $e) {
            // Just leave this empty
        }
        return false;
    }

    /**
     * Preload data if necessary for resources
     *
     * Data will get cached, making the permission checks a lot faster.
     *
     * @param string $type
     * @param int[]|string[] $idList
     */
    public function preload($type, array $idList)
    {
        $this->doPreload($this->createCollection($type, $idList));
    }

    /**
     * Is profile granted to
     *
     * @param mixed $resource
     * @param mixed|Profile|ProfileSet $profile
     * @param string $permission
     *
     * @return boolean
     */
    public function isGranted($resource, $profile, $permission)
    {
        if (!$this->supportsPermission($permission)) {
            return false;
        }

        $profiles = $this->expandProfile($profile);

        if (!$profiles) {
            return false;
        }

        return $this->doCheck($resource, $profiles, $permission);
    }
}
