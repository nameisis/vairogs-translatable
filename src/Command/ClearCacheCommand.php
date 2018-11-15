<?php

namespace Vairogs\Utils\Translatable\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCacheCommand extends ContainerAwareCommand
{
    public function configure(): void
    {
        $this->setName('vairogs:translatable:clear');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $locales = $this->getManagedLocales();
        $output->writeln('Remove cache for translations in: '.\implode(', ', $locales));
        $this->getContainer()->get('translator')->removeLocalesCacheFiles($locales);
    }

    /**
     * @return array
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \LogicException
     */
    protected function getManagedLocales(): array
    {
        return $this->getContainer()->getParameter('lexik_translation.managed_locales');
    }
}
