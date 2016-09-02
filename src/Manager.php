<?php

namespace MakinaCorpus\ACL;

final class Manager
{
    private $voters = [];
    private $profileConverters = [];
    private $resourceConverters = [];

    /**
     * Default constructor
     *
     * @param VoterInterface[] $voters
     * @param ProfileConverterInterface[] $profileConverters
     * @param ResourceConverterInterface[] $resourceConverters
     */
    public function __construct(array $voters, array $profileConverters, array $resourceConverters)
    {
        $this->voters = $voters;
        $this->profileConverters = $profileConverters;
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
            if ($converter->canConvertAsResource($object)) {
                return $converter->asResource($object);
            }
        }

        throw new \InvalidArgumentException("cannot convert object to resource");
    }

    /**
     * Convert object to profile
     *
     * @param mixed $object
     *
     * @return Profile
     */
    private function expandProfile($object)
    {
        if ($object instanceof ProfileSet) {
            return $object->getAll();
        }
        if ($object instanceof Profile) {
            return [$object];
        }

        foreach ($this->profileConverters as $converter) {
            if ($converter->canConvertAsProfile($object)) {
                return $converter->asProfile($object);
            }
        }

        throw new \InvalidArgumentException("cannot convert object to profile");
    }

    /**
     * Is profile granted to
     *
     * @param mixed $resource
     * @param mixed|mixed[]|Profile|Profile[]|ProfileSet $profile
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

        foreach ($profiles as $profile) {
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
