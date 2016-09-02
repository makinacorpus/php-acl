<?php

namespace MakinaCorpus\ACL;

/**
 * Most objects (voters, checkers) may or may not restrict their usage by
 * resource or profile type.
 */
interface SupportiveInterface
{
    /**
     * Does this object supports the given resource type
     *
     * @param string $type
     *
     * @return boolean
     */
    public function supports(Resource $resource);
}
