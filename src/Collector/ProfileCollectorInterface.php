<?php

namespace MakinaCorpus\ACL\Collector;

use MakinaCorpus\ACL\Resource;

/**
 * From a given undertermine object, give or take profiles
 */
interface ProfileCollectorInterface
{
    /**
     * Collect entries for resource
     *
     * @param ProfileSetBuilder $builder
     */
    public function collectProfiles(ProfileSetBuilder $builder);
}
