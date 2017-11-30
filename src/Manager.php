<?php

namespace MakinaCorpus\ACL;

use MakinaCorpus\ACL\Collector\EntryListBuilder;
use MakinaCorpus\ACL\Collector\ProfileSetBuilder;
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
     * @param \MakinaCorpus\ACL\Store\EntryStoreInterface[] $entryStores
     * @param \MakinaCorpus\ACL\Collector\EntryCollectorInterface[] $resourceCollectors
     * @param \MakinaCorpus\ACL\Collector\ProfileCollectorInterface[] $profileCollectors
     * @param \MakinaCorpus\ACL\Converter\ResourceConverterInterface[] $resourceConverters
     * @param PermissionMap $permissionMap
     * @param boolean $debug
     */
    public function __construct(
        array $entryStores,
        array $resourceCollectors,
        array $profileCollectors = [],
        array $resourceConverters = [],
        PermissionMap $permissionMap = null,
        bool $debug = false
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
     */
    public function setDebug(bool $debug = true)
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
    private function createResource($object) : Resource
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
     * @return EntryList
     */
    private function collectEntryListFor(Resource $resource, $object, string $permission = null) : EntryList
    {
        // Having an empty list of collects is valid, it just means that the
        // business layer deals with permissions by itself, using the store
        // directly, which is definitely legal
        if (empty($this->resourceCollectors)) {
            throw new UnsupportedResourceException();
        }

        $isSupported = false;
        $builder = new EntryListBuilder($resource, $object, $this->permissionMap);

        foreach ($this->resourceCollectors as $collector) {
            $localSupport = false;

            if (null === $permission) {
                $localSupport = $collector->supportsType($resource->getType());
            } else {
                $localSupport = $collector->supports($resource->getType(), $permission);
            }

            if ($localSupport) {
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
     * @return EntryList
     */
    private function loadEntryListFor(Resource $resource, $object, string $permission = null) : EntryList
    {
        $list = null;
        $store = null;

        foreach ($this->entryStores as $store) {
            $localSupport = false;

            if (null === $permission) {
                $localSupport = $store->supportsType($resource->getType());
            } else {
                $localSupport = $store->supports($resource->getType(), $permission);
            }

            if ($localSupport) {
                if ($list = $store->load($resource)) {
                    break;
                }
            }
        }

        if (!$list) {
            $list = $this->collectEntryListFor($resource, $object, $permission);

            // @todo should we call this at all?
            if (!$list->isEmpty() && $store) {
                $store->save($resource, $list);
            }
        }

        return $list;
    }

    /**
     * Collect entry list for the given resource
     */
    private function collectProfileSetFor($object) : ProfileSet
    {
        // Having an empty list of collects is valid, it just means that the
        // business layer deals with permissions by itself, using the store
        // directly, which is definitely legal
        if (empty($this->profileCollectors)) {
            return ProfileSet::createFromArray([]);
        }

        $builder = new ProfileSetBuilder($object);

        foreach ($this->profileCollectors as $collector) {
            $collector->collectProfiles($builder);
        }

        return $builder->convertToProfileSet();
    }

    /**
     * Convert object to profile
     */
    private function expandProfile($object) : ProfileSet
    {
        if ($object instanceof ProfileSet) {
            return $object;
        }
        if ($object instanceof Profile) {
            return ProfileSet::createFromProfiles([$object]);
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
     */
    private function doCheck($object, ProfileSet $profiles, string $permission) : bool
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

        if ($list->hasPermissionFor($profiles, $permission)) {
            return true;
        }

        return false;
    }

    /**
     * Preload data if necessary for resources
     *
     * Data will get cached, making the permission checks a lot faster.
     */
    private function doPreload(ResourceCollection $resources)
    {
        foreach ($this->entryStores as $store) {
            if ($store->supportsType($resources->getType())) {
                $store->loadAll($resources);
            }
        }
    }

    /**
     * Create a resource collection
     */
    private function createCollection(string $type, array $idList) : ResourceCollection
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
     *
     * @return EntryList
     */
    public function collectEntryListAll($object) : EntryList
    {
        try {
            $resource = $this->createResource($object);
        } catch (\InvalidArgumentException $e) {
            if ($this->debug) {
                // @todo let exception or trigger meaningful debug message
            }

            return [];
        }

        return $this->loadEntryListFor($resource, $object);
    }

    /**
     * Collect profiles for the given object
     */
    public function collectProfiles($object) : ProfileSet
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
    public function preload(string $type, array $idList)
    {
        $this->doPreload($this->createCollection($type, $idList));
    }

    /**
     * Vote is the same as the isGranted operation except it can abstain
     *
     * @param mixed|Profile|ProfileSet $profile
     * @param mixed $resource
     * @param string $permission
     *
     * @return int
     *   Manager::ALLOW, Manager::DENY or Manager::ABSTAIN
     */
    public function vote($profile, $resource, string $permission) : int
    {
        if (!$profile) {
            throw new \Exception("Passing a null profile is unsupported yet");
        }

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
     * @param string $permission
     * @param mixed $resource
     * @param mixed|Profile|ProfileSet $profile
     *
     * @return bool
     */
    public function isGranted(string $permission, $resource, $profile = null) : bool
    {
        if (!$profile) {
            throw new \Exception("Passing a null profile is unsupported yet");
        }

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
