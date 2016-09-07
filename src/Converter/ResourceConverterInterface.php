<?php

namespace MakinaCorpus\ACL\Converter;

interface ResourceConverterInterface
{
    /**
     * Convert the object
     *
     * @param mixed object
     *
     * @return Resource
     */
    public function convert($object);
}
