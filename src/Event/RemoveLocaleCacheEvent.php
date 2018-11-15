<?php

namespace Vairogs\Utils\Translatable\Event;

use Symfony\Component\EventDispatcher\Event;

class RemoveLocaleCacheEvent extends Event
{
    /**
     * @const String
     */
    public const PRE_REMOVE_LOCAL_CACHE = 'pre_remove_local_cache.event';

    /**
     * @const String
     */
    public const POST_REMOVE_LOCAL_CACHE = 'post_remove_local_cache.event';

    /**
     * @var array
     */
    private $managedLocales;

    public function __construct(array $managedLocales)
    {
        $this->managedLocales = $managedLocales;
    }

    /**
     * @return array
     */
    public function getManagedLocales(): array
    {
        return $this->managedLocales;
    }
}