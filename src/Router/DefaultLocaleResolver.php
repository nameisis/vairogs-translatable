<?php

namespace Vairogs\Utils\Translatable\Router;

use JMS\I18nRoutingBundle\Router\LocaleResolverInterface;
use Vairogs\Utils\Core\Router\LocaleResolverTrait;

class DefaultLocaleResolver implements LocaleResolverInterface
{
    use LocaleResolverTrait;

    public function __construct($cookieName, array $hostMap = [])
    {
        $this->cookieName = $cookieName;
        $this->hostMap = $hostMap;
    }
}
