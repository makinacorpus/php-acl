<?php

namespace MakinaCorpus\ACL;

use MakinaCorpus\ACL\Collector\EntryCollectorInterface;
use MakinaCorpus\ACL\Collector\ProfileCollectorInterface;
use MakinaCorpus\ACL\Collector\ProfileSetBuilder;
use MakinaCorpus\ACL\Converter\ResourceConverterInterface;
use MakinaCorpus\ACL\Impl\NaiveEntryListBuilder;
use MakinaCorpus\ACL\Store\EntryStoreInterface;

final class Manager
{
    private $entryStores = [];
    private $resourceCollectors = [];
    private $profileCollectors = [];
    private $resourceConverters = [];
    private $builderClass = NaiveEntryListBuilder::class;
    private $profileCache = [];
    private $permissionCache = [];

    /**
     * Default constructor
     *
     * @param EntryStoreInterface[] $entryStores
     * @param EntryCollectorInterface[] $resourceCollectors
     * @param ProfileCollectorInterface[] $profileCollectors
     * @param ResourceConverterInterface[] $resourceConverters
     */
    public function __construct(
        array $entryStores,
        array $resourceCollectors,
        array $profileCollectors = [],
        array $resourceConverters = [],
        $builderClass = NaiveEntryListBuilder::class
    ) {
        $this->entryStores = $entryStores;
        $this->resourceCollectors = $resourceCollectors;
        $this->profileCollectors = $profileCollectors;
        $this->resourceConverters = $resourceConverters;
        $this->builderClass = $builderClass;
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
     * Create the builder instance
     *
     * @return EntryListBuilderInterface
     */
    private function createBuilder(Resource $resource)
    {
        return new NaiveEntryListBuilder($resource);
    }

    /**
     * Collect entry list for the given resource
     *
     * @param Resource $resource
     *
     * @return EntryListInterface
     */
    private function collectEntryListFor(Resource $resource)
    {
        // Having an empty list of collects is valid, it just means that the
        // business layer deals with permissions by itself, using the store
        // directly, which is definitely legal
        if (empty($this->resourceCollectors)) {
            return;
        }

        $builder = $this->createBuilder($resource);

        foreach ($this->resourceCollectors as $collector) {
            if ($collector->supports($resource->getType())) {
                $collector->collect($builder);
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
    private function getEntryListFor(Resource $resource)
    {
        $list = null;

        foreach ($this->entryStores as $store) {
            if ($store->supports($resource->getType())) {
                if ($list = $store->load($resource)) {
                    break;
                }
            }
        }

        if (!$list) {
            $list = $this->collectEntryListFor($resource);

            // @todo should we call this at all?
            if ($list && $store) {
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
            $collector->collect($builder);
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

        if (isset($this->profileCache[$id])) {
            return $this->profileCache[$id];
        }

        return $this->profileCache[$id] = $this->collectProfileSetFor($object);
    }

    /**
     * Empty caches
     */
    public function refresh()
    {
        $this->profileCache = [];
        $this->permissionCache = [];
    }

    /**
     * Do the real permissions check
     *
     * @param Resource $resource
     * @param ProfileSet $profiles
     * @param string $permission
     *
     * @return boolean
     */
    private function doCheck(Resource $resource, ProfileSet $profiles, $permission)
    {
        $type = $resource->getType();
//        $id   = $resource->getId();

        foreach ($this->entryStores as $store) {
            if ($store->supports($type)) {
                foreach ($profiles->getAll() as $profile) {

                    // Handles internal cache, at the profile level and not the
                    // profile set level, it help mutualizing if more than one
                    // profile set using the same profile(s) partially are being
                    // called
//                    $key = $profile->asString();
//                     if (isset($this->permissionCache[$type][$id][$key][$permission])) {
//                         return $this->permissionCache[$type][$id][$key][$permission];
//                     }

                    if ($list = $this->getEntryListFor($resource)) {
                        if ($list->hasPermissionFor($profile, $permission)) {
//                            return $this->permissionCache[$type][$id][$key][$permission] = true;
                              return true;
                        }
                    }

                    //$this->permissionCache[$type][$id][$key][$permission] = false;
                }
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
        $profiles = $this->expandProfile($profile);

        if (!$profiles) {
            return false;
        }

        $resource = $this->expandResource($resource);

        return $this->doCheck($resource, $profiles, $permission);
    }
}
