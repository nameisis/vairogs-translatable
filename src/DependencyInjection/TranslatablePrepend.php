<?php

namespace Vairogs\Utils\Translatable\DependencyInjection;

use JMS\I18nRoutingBundle\DependencyInjection\Configuration as JmsConfiguration;
use Lexik\Bundle\TranslationBundle\DependencyInjection\Configuration as LexikConfiguration;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Vairogs\Utils\DependencyInjection\Component\Prepandable;

class TranslatablePrepend implements Prepandable
{
    public const OVERRIDE = [
        'jms_i18n_routing',
        'lexik_translation',
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
            case self::OVERRIDE[1]:
                $config[0]['fallback_locale'] = $config['default_locale'] ?? 'en';
                $config[0]['managed_locales'] = !empty($config['locales']) ? $config['locales'] : ['en'];

                return new LexikConfiguration();
        }
    }

    public function setConfig(ContainerBuilder $container, string $override, array $config): void
    {
        if ($override === self::OVERRIDE[1] && null === $config['auto_cache_clean_interval']) {
            $config['auto_cache_clean_interval'] = 0;
        }

        $container->prependExtensionConfig($override, $config);
    }
}
