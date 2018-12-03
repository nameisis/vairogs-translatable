<?php

namespace Vairogs\Utils\Translatable\DependencyInjection;

use JMS\I18nRoutingBundle\DependencyInjection\Configuration as JmsConfiguration;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Vairogs\Utils\DependencyInjection\Component\Prepandable;

class TranslatablePrepend implements Prepandable
{
    public const OVERRIDE = [
        'jms_i18n_routing',
    ];

    public function getConfig(ContainerBuilder $container, string $override, array &$config): ConfigurationInterface
    {
        $config = $container->getExtensionConfig($override);
        $config = $container->getParameterBag()->resolveValue($config);

        switch ($override) {
            case self::OVERRIDE[0]:
                $config[0]['default_locale'] = $config['default_locale'] ?? 'en';
                $config[0]['locales'] = !empty($config['locales']) ? $config['locales'] : ['en'];

                return new JmsConfiguration();
        }
    }

    public function setConfig(ContainerBuilder $container, string $override, array $config): void
    {
        $container->prependExtensionConfig($override, $config);
    }
}
