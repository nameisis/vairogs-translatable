<?php

namespace Vairogs\Utils\Translatable\Entity;

abstract class BaseEntity
{
    public function getVariableByLocale($field, $locale)
    {
        return $this->{$field};
    }
}
