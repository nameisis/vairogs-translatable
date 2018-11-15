<?php

namespace Vairogs\Utils\Translatable\Admin;

use Doctrine\ORM\Query;
use Lexik\Bundle\TranslationBundle\Entity\File;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\Form\Extension\Core\Type;

class ORMTranslationAdmin extends TranslationAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine')->getManagerForClass(File::class);

        $domains = [];
        $domainsQueryResult = $entityManager->createQueryBuilder()->select('DISTINCT t.domain')->from(File::class, 't')->getQuery()->getResult(Query::HYDRATE_ARRAY);

        \array_walk_recursive($domainsQueryResult, function($domain) use (&$domains) {
            $domains[$domain] = $domain;
        });
        \ksort($domains);

        // @formatter:off
        $filter
            ->add('locale', 'doctrine_orm_callback', $this->getLocaleOptions())
            ->add('show_non_translated_only', 'doctrine_orm_callback', $this->getNonOptions())
            ->add('key', 'doctrine_orm_string')->add('domain', 'doctrine_orm_choice', $this->getKeyOptions($domains))
            ->add('content', 'doctrine_orm_callback', $this->getContentOptions());
        // @formatter:on
    }

    private function getLocaleOptions(): array
    {
        return [
            'callback' => function(ProxyQuery $queryBuilder, $alias, $field, $options) {
                if (!isset($options['value']) || empty($options['value'])) {
                    return;
                }
                // use on to filter locales
                $this->joinTranslations($queryBuilder, $alias, $options['value']);
            },
            'field_options' => [
                'choices' => $this->formatLocales($this->managedLocales),
                'required' => false,
                'multiple' => true,
                'expanded' => false,
            ],
            'field_type' => Type\ChoiceType::class,
        ];
    }

    /**
     * @param ProxyQuery $queryBuilder
     * @param String $alias
     * @param array|null $locales
     */
    private function joinTranslations(ProxyQuery $queryBuilder, $alias, array $locales = null): void
    {
        $alreadyJoined = false;
        $joins = $queryBuilder->getDQLPart('join');
        if (\array_key_exists($alias, $joins)) {
            $joins = $joins[$alias];
            /** @var $joins array */
            foreach ($joins as $join) {
                if (\strpos($join->__toString(), "$alias.translations ") !== false) {
                    $alreadyJoined = true;
                }
            }
        }
        if (!$alreadyJoined) {
            if ($locales) {
                $queryBuilder->leftJoin(\sprintf('%s.translations', $alias), 'translations', 'WITH', 'translations.locale in (:locales)');
                $queryBuilder->setParameter('locales', $locales);
            } else {
                $queryBuilder->leftJoin(\sprintf('%s.translations', $alias), 'translations');
            }
        }
    }

    /**
     * @param array $locales
     *
     * @return array
     */
    private function formatLocales(array $locales): array
    {
        $formattedLocales = [];
        \array_walk_recursive($locales, function($language) use (&$formattedLocales) {
            $formattedLocales[$language] = $language;
        });

        return $formattedLocales;
    }

    private function getNonOptions(): array
    {
        return [
            'callback' => function(ProxyQuery $queryBuilder, $alias, $field, $options) {
                if (!isset($options['value']) || empty($options['value']) || false === $options['value']) {
                    return;
                }
                $this->joinTranslations($queryBuilder, $alias);

                foreach ($this->getEmptyFieldPrefixes() as $prefix) {
                    if (empty($prefix)) {
                        $queryBuilder->orWhere('translations.content IS NULL');
                    } else {
                        $queryBuilder->orWhere('translations.content LIKE :content')->setParameter('content', $prefix.'%');
                    }
                }
            },
            'field_options' => [
                'required' => true,
                'value' => $this->getNonTranslatedOnly(),
            ],
            'field_type' => Type\CheckboxType::class,
        ];
    }

    private function getKeyOptions($domains): array
    {
        return [
            'field_options' => [
                'choices' => $domains,
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'empty_data' => 'all',
            ],
            'field_type' => Type\ChoiceType::class,
        ];
    }

    private function getContentOptions(): array
    {
        return [
            'callback' => function(ProxyQuery $queryBuilder, $alias, $field, $options) {
                if (!isset($options['value']) || empty($options['value'])) {
                    return;
                }
                $this->joinTranslations($queryBuilder, $alias);
                $queryBuilder->andWhere('translations.content LIKE :content')->setParameter('content', '%'.$options['value'].'%');
            },
            'field_type' => Type\TextType::class,
            'label' => 'content',
        ];
    }
}
