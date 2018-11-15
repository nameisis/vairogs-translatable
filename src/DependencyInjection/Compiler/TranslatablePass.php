<?php

namespace Vairogs\Utils\Translatable\DependencyInjection\Compiler;

use Vairogs\Utils\DependencyInjection\Component\Definable;
use Vairogs\Utils\VairogsBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class TranslatablePass implements CompilerPassInterface
{
    public const NAME = \KODE.'.'.Definable::TRANSLATABLE;

    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException
     */
    public function process(ContainerBuilder $container): void
    {
        if (VairogsBundle::isEnabled($container, Definable::TRANSLATABLE)) {
            $resources = $container->getParameter('twig.form.resources');

            $form = \sprintf('%s.template', self::NAME);
            $check = \sprintf('%s.enabled', self::NAME);

            if ($container->hasParameter($check) && $container->getParameter($check) === true && $container->hasParameter($form) && false !== ($template = $container->getParameter($form)) && !\in_array($template, $resources, false)) {
                $resources[] = $template;
            }

            $container->setParameter('twig.form.resources', $resources);
        }
    }
}
