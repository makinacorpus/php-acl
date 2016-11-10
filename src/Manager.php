<?php

namespace MakinaCorpus\ACL;

use MakinaCorpus\ACL\Collector\EntryCollectorInterface;
use MakinaCorpus\ACL\Collector\ProfileCollectorInterface;
use MakinaCorpus\ACL\Collector\ProfileSetBuilder;
use MakinaCorpus\ACL\Converter\ResourceConverterInterface;
use MakinaCorpus\ACL\Store\EntryStoreInterface;
use MakinaCorpus\ACL\Error\UnsupportedResourceException;

final class Manager
{
    const ALLOW = 1;
    const DENY = 2;
    const ABSTAIN = 3;

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
    private function createResource($object)
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

        throw new UnsupportedResourceException();
    }

    /**
     * Collect entry list for the given resource
     *
     * @param Resource $resource
     *   The expanded and normalized resource object
     * @param mixed $object
     *   The original object from the caller, propagated for performance reasons
     * @param string $permission
     *   The permission to check for, allowing early return using the supports()
     *   method with stores and collectors for performance reasons
     *
     * @return EntryListInterface
     */
    private function collectEntryListFor(Resource $resource, $object, $permission)
    {
        // Having an empty list of collects is valid, it just means that the
        // business layer deals with permissions by itself, using the store
        // directly, which is definitely legal
        if (empty($this->resourceCollectors)) {
            throw new UnsupportedResourceException();
        }

        $isSupported = false;
        $builder = $this->permissionMap->createEntryListBuilder($resource, $object);

        foreach ($this->resourceCollectors as $collector) {
            if ($collector->supports($resource->getType(), $permission)) {
                $isSupported = true;
                $collector->collectEntryLists($builder);
            }
        }

        if (!$isSupported) {
            throw new UnsupportedResourceException();
        }

        return $builder->convertToEntryList();
    }

    /**
     * Get entry list for
     *
     * @param Resource $resource
     *   The expanded and normalized resource object
     * @param mixed $object
     *   The original object from the caller, propagated for performance reasons
     * @param string $permission
     *   The permission to check for, allowing early return using the supports()
     *   method with stores and collectors for performance reasons
     *
     * @return EntryListInterface
     */
    private function loadEntryListFor(Resource $resource, $object, $permission)
    {
        $list = null;
        $store = null;

        foreach ($this->entryStores as $store) {
            if ($store->supports($resource->getType(), $permission)) {
                if ($list = $store->load($resource)) {
                    break;
                }
            }
        }

        if (!$list) {
            $list = $this->collectEntryListFor($resource, $object, $permission);

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
            $resource = $this->createResource($object);
        }

        $list = $this->loadEntryListFor($resource, $object, $permission);

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
     * @param Resource $resource
     *   The expanded and normalized resource object
     */
    private function doPreload(ResourceCollection $resources)
    {
        return;
        foreach ($this->entryStores as $store) {
            if ($store->supports($resources->getType(), $permission)) {
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
     * Vote is the same as the isGranted operation except it can abstain
     *
     * @param mixed $resource
     * @param mixed|Profile|ProfileSet $profile
     * @param string $permission
     *
     * @return int
     *   Manager::ALLOW, Manager::DENY or Manager::ABSTAIN
     */
    public function vote($resource, $profile, $permission)
    {
        if (!$this->permissionMap->supports($permission)) {
            return self::ABSTAIN;
        }

        $profiles = $this->expandProfile($profile);

        if (!$profiles) {
            return self::ABSTAIN;
        }

        try {
            if ($this->doCheck($resource, $profiles, $permission)) {
                return self::ALLOW;
            } else {
                return self::DENY;
            }
        } catch (UnsupportedResourceException $e) {
            return self::ABSTAIN;
        }
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
        if (!$this->permissionMap->supports($permission)) {
            return false;
        }

        $profiles = $this->expandProfile($profile);

        if (!$profiles) {
            return false;
        }

        try {
            return $this->doCheck($resource, $profiles, $permission);
        } catch (UnsupportedResourceException $e) {
            return false;
        }
    }
}
