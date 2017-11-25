<?php

namespace MakinaCorpus\ACL\Converter;

interface ResourceConverterInterface
{
    /**
     * Convert the object
     *
     * @return null|\MakinaCorpus\ACL\Resource
     */
    public function convert($object);
}
