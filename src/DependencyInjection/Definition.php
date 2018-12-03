<?php

namespace Vairogs\Utils\Translatable\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Vairogs\Utils\DependencyInjection\Component\Definable;

class Definition implements Definable
{
    private const ALLOWED = [
        Definable::TRANSLATABLE,
    ];

    public function getExtensionDefinition($extension): ArrayNodeDefinition
    {
        if (!\in_array($extension, self::ALLOWED, true)) {
            throw new InvalidConfigurationException(\sprintf('Invalid extension: %s', $extension));
        }

        switch ($extension) {
            case Definable::TRANSLATABLE:
                return $this->getTranslatableDefinition($extension);
        }
    }

    private function getTranslatableDefinition($extension): ArrayNodeDefinition
    {
        $node = (new TreeBuilder())->root($extension);
        /** @var ArrayNodeDefinition $node */

        // @formatter:off
        $node
            ->canBeEnabled()
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('locales')
                    ->defaultValue(['en_US'])
                    ->beforeNormalization()
                        ->ifString()
                            ->then(function($v) {
                                return \preg_split('/\s*,\s*/', $v);
                            })
                        ->end()
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('default_locale')
                    ->defaultValue('en')
                ->end()
                ->scalarNode('table')
                    ->defaultValue('vairogs_translation')
                ->end()
                ->scalarNode('manager')
                    ->defaultValue('default')
                ->end()
                ->scalarNode('template')
                    ->defaultValue('Translatable/translations.html.twig')
                ->end()
            ->end();
        // @formatter:on

        return $node;
    }
}
