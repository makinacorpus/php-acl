<?php

namespace MakinaCorpus\ACL;

use MakinaCorpus\ACL\Collector\ProfileCollectorInterface;
use MakinaCorpus\ACL\Collector\ProfileSetBuilder;
use MakinaCorpus\ACL\Converter\ResourceConverterInterface;
use MakinaCorpus\ACL\Voter\VoterInterface;

final class Manager
{
    private $voters = [];
    private $profileCollectors = [];
    private $resourceConverters = [];
    private $profileCache = [];
    private $permissionCache = [];

    /**
     * Default constructor
     *
     * @param VoterInterface[] $voters
     * @param ProfileCollectorInterface[] $profileCollectors
     * @param ResourceConverterInterface[] $resourceConverters
     */
    public function __construct(array $voters, array $profileCollectors = [], array $resourceConverters = [])
    {
        $this->voters = $voters;
        $this->profileCollectors = $profileCollectors;
        $this->resourceConverters = $resourceConverters;
    }

    /**
     * Convert object to resource
     *
     * @param mixed $object
     *
     * @return Resource
     */
    private function getResource($object)
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
        foreach ($profiles->getAll() as $profile) {
            foreach ($this->voters as $voter) {
                if ($voter->supports($resource->getType())) {
                    if ($voter->vote($resource, $profile, $permission)) {
                        return true;
                    }
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
        $type = $resources->getType();

        foreach ($this->voters as $voter) {
            if ($voter->supports($type)) {
                $voter->preload($resources);
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

        $resource = $this->getResource($resource);

        $cid  = $profiles->getCacheIdentifier();
        $type = $resource->getType();
        $id   = $resource->getId();

        if (isset($this->permissionCache[$cid][$type][$id][$permission])) {
            return $this->permissionCache[$cid][$type][$id][$permission];
        }

        return $this->permissionCache[$cid][$type][$id][$permission] = $this->doCheck($resource, $profiles, $permission);
    }
}
