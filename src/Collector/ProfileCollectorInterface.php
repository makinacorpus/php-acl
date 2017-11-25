<?php

namespace MakinaCorpus\ACL\Collector;

/**
 * From a given undertermine object, give or take profiles
 */
interface ProfileCollectorInterface
{
    /**
     * Collect entries for resource
     */
    public function collectProfiles(ProfileSetBuilder $builder);
}
