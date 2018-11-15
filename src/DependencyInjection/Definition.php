<?php

namespace Vairogs\Utils\Translatable\DependencyInjection;

use Vairogs\Utils\DependencyInjection\Component\Definable;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class Definition implements Definable
{
    private const ALLOWED = [
        Definable::TRANSLATABLE,
        Definable::LEXIK,
    ];

    public function getExtensionDefinition($extension): ArrayNodeDefinition
    {
        if (!\in_array($extension, self::ALLOWED, true)) {
            throw new InvalidConfigurationException(\sprintf('Invalid extension: %s', $extension));
        }

        switch ($extension) {
            case Definable::TRANSLATABLE:
                return $this->getTranslatableDefinition($extension);
            case Definable::LEXIK:
                return $this->getLexikDefinition($extension);
        }
    }

    private function getTranslatableDefinition($extension): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root($extension);
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

    private function getLexikDefinition($extension): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root($extension);
        /** @var ArrayNodeDefinition $node */

        // @formatter:off
        $node
            ->canBeEnabled()
            ->addDefaultsIfNotSet()
            ->children()

            ->end();
        // @formatter:on

        return $node;
    }
}
