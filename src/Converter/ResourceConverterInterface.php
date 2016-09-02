<?php

namespace MakinaCorpus\ACL\Converter;

interface ResourceConverterInterface
{
    /**
     * Can this converter convert the given object
     *
     * @param mixed object
     *
     * @return Resource
     */
    public function canConvertAsResource($object);

    /**
     * Convert the object
     *
     * @param mixed object
     *
     * @return Resource
     */
    public function asResource($object);
}
