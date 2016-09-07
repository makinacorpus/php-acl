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
            return new ProfileSet([$object], $object->getObject());
        }

        return $this->collectProfileSetFor($object);
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

        foreach ($profiles->getAll() as $profile) {
            foreach ($this->voters as $voter) {
                if ($voter->supports($resource)) {
                    if ($voter->vote($resource, $profile, $permission)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
