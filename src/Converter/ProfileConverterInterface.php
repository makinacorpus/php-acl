<?php

namespace MakinaCorpus\ACL\Converter;

interface ProfileConverterInterface
{
    /**
     * Can this converter convert the given object
     *
     * @param mixed object
     *
     * @return Profile
     */
    public function canConvertAsProfile($object);

    /**
     * Convert the object
     *
     * @param mixed object
     *
     * @return Profile
     */
    public function asProfile($object);
}
