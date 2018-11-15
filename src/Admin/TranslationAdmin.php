<?php

namespace Vairogs\Utils\Translatable\Admin;

use Lexik\Bundle\TranslationBundle\Manager\TransUnitManagerInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\Form\Extension\Core\Type;

abstract class TranslationAdmin extends AbstractAdmin
{
    /**
     * @var TransUnitManagerInterface
     */
    protected $transUnitManager;

    /**
     * @var array
     */
    protected $editableOptions = [
        'type' => 'textarea',
        'emptytext' => 'Empty',
    ];

    /**
     * @var array
     */
    protected $defaultSelections = [
        'nonTranslatedOnly' => false,
    ];

    /**
     * @var array
     */
    protected $emptyFieldPrefixes = [
        '__',
        'new_',
        '',
    ];

    /**
     * @var array
     */
    protected $filterLocales = [];

    /**
     * @var array
     */
    protected $managedLocales = [];

    /**
     * @param array $options
     */
    public function setEditableOptions(array $options): void
    {
        $this->editableOptions = $options;
    }

    /**
     * @param TransUnitManagerInterface $translationManager
     */
    public function setTransUnitManager(TransUnitManagerInterface $translationManager): void
    {
        $this->transUnitManager = $translationManager;
    }

    /**
     * @param array $managedLocales
     */
    public function setManagedLocales(array $managedLocales): void
    {
        $this->managedLocales = $managedLocales;
    }

    /**
     * @return array
     */
    public function getEmptyFieldPrefixes(): array
    {
        return $this->emptyFieldPrefixes;
    }

    /**
     * @return bool
     */
    public function getNonTranslatedOnly(): bool
    {
        return \array_key_exists('non_translated_only', $this->getDefaultSelections()) && (bool)$this->defaultSelections['nonTranslatedOnly'];
    }

    /**
     * @return array
     */
    public function getDefaultSelections(): array
    {
        return $this->defaultSelections;
    }

    /**
     * @param array $selections
     */
    public function setDefaultSelections(array $selections): void
    {
        $this->defaultSelections = $selections;
    }

    /**
     * @param array $prefixes
     */
    public function setEmptyPrefixes(array $prefixes): void
    {
        $this->emptyFieldPrefixes = $prefixes;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDatagrid()
    {
        if ($this->datagrid) {
            return;
        }

        $filterParameters = $this->getFilterParameters();

        // transform _sort_by from a string to a FieldDescriptionInterface for the datagrid.
        if (isset($filterParameters['locale']) && \is_array($filterParameters['locale'])) {
            $this->filterLocales = \array_key_exists('value', $filterParameters['locale']) ? $filterParameters['locale']['value'] : $this->managedLocales;
        }

        parent::buildDatagrid();
    }

    /**
     * @return array
     * @throws InvalidArgumentException
     */
    public function getFilterParameters(): array
    {
        if ($this->getDefaultDomain()) {
            $this->datagridValues = \array_merge([
                'domain' => [
                    'value' => $this->getDefaultDomain(),
                ],
            ], $this->datagridValues

            );
        }

        return parent::getFilterParameters();
    }

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getDefaultDomain(): string
    {
        return \VAIROGS;
    }

    /**
     * @param FormMapper $form
     *
     * @throws InvalidArgumentException
     */
    protected function configureFormFields(FormMapper $form)
    {
        $subject = $this->getSubject();

        if (null === $subject->getId()) {
            $subject->setDomain($this->getDefaultDomain());
        }

        $form->add('key', Type\TextareaType::class)->add('domain', Type\TextareaType::class);
    }

    /**
     * @param ListMapper $list
     *
     * @throws RuntimeException
     */
    protected function configureListFields(ListMapper $list)
    {
        unset($this->listModes['mosaic']);

        $list->add('id', Type\IntegerType::class)->add('key', Type\TextType::class)->add('domain', Type\TextType::class);

        $localesToShow = \count($this->filterLocales) > 0 ? $this->filterLocales : $this->managedLocales;

        foreach ($localesToShow as $locale) {
            $fieldDescription = $this->modelManager->getNewFieldDescriptionInstance($this->getClass(), $locale);
            $fieldDescription->setTemplate('Lexik/CRUD/base_inline_translation_field.html.twig');
            $fieldDescription->setOption('locale', $locale);
            $fieldDescription->setOption('editable', $this->editableOptions);
            $list->add($fieldDescription);
        }
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('clear_cache')->add('create_trans_unit');
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        $actions['download'] = [
            'label' => $this->trans($this->getLabelTranslatorStrategy()->getLabel('download', 'batch', 'Vairogs')),
            'ask_confirmation' => false,
        ];

        return $actions;
    }

    public function initialize()
    {
        parent::initialize();
        $this->managedLocales = $this->getContainer()->getParameter('lexik_translation.managed_locales');
        $this->setTemplate('list', 'Lexik/CRUD/list.html.twig');
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->getConfigurationPool()->getContainer();
    }
}
