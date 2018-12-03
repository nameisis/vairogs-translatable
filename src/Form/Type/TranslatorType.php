<?php

namespace Vairogs\Utils\Translatable\Form\Type;

use Doctrine\Common\Annotations\Reader;
use Locale;
use ReflectionException;
use ReflectionProperty;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Vairogs\Utils\Translatable\Annotation\TranslatableField;
use Vairogs\Utils\Translatable\Helper\Manager;

class TranslatorType extends AbstractType
{
    protected const DEFAULT_CLASS = '';
    protected const DEFAULT_TYPE = TextType::class;
    protected $manager;
    protected $container;
    protected $reader;

    /**
     * @var array
     */
    private $locales;
    private $userLocale;

    public function __construct($locales, Manager $manager, TranslatorInterface $translator, $container, Reader $reader)
    {
        $this->manager = $manager;
        $this->locales = $locales;
        $this->userLocale = $translator->getLocale();
        $this->container = $container;
        $this->reader = $reader;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     *
     * @throws InvalidConfigurationException
     * @throws ReflectionException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var AbstractAdmin $admin */
        $admin = $options['sonata_field_description']->getAdmin();
        $admin->setTemplate('edit', 'Translatable/CRUD/edit.html.twig');
        $admin->tabbed = true;
        $className = $admin->getClass();

        $id = $admin->getSubject()->getId();
        $fieldName = $builder->getName();

        $reflectionProperty = new ReflectionProperty($className, $fieldName);
        $annotation = $this->reader->getPropertyAnnotation($reflectionProperty, TranslatableField::class);

        if ($annotation === null) {
            throw new InvalidConfigurationException(\sprintf('Element has to have %s annotation in order to use %s', TranslatableField::class, TranslatorType::class));
        }
        /** @var TranslatableField $annotation */

        $fieldType = $annotation->getType() ?? self::DEFAULT_TYPE;
        $class = $annotation->getClass() ?? self::DEFAULT_CLASS;
        $required = $options['required'];

        if (!$id) {
            $translations = $this->manager->getNewTranslatedFields($fieldName, $this->locales);
        } else {
            $translations = $this->manager->getTranslatedFields($className, $fieldName, $id, $this->locales);
        }

        $this->addPreSetDataListener($builder, $fieldName, $translations, $fieldType, $class, $required, $className, $id);
        $this->addPostSubmitListener($builder, $fieldName, $className, $id);
    }

    private function addPreSetDataListener(FormBuilderInterface $builder, $fieldName, $translations, $fieldType, $class, $required, $className, $id): void
    {
        // 'populate' fields by *hook on form generation
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($fieldName, $translations, $fieldType, $class, $required, $className, $id) {
            $form = $event->getForm();
            foreach ($this->locales as $locale) {
                $data = (\array_key_exists($locale, $translations) && \array_key_exists($fieldName, $translations[$locale])) ? $translations[$locale][$fieldName] : null;
                $form->add($locale, $fieldType, [
                    'label' => false,
                    'data' => $data,
                    'required' => $required,
                    'attr' => [
                        'class' => $class,
                        'data-locale' => $locale,
                        'data-class' => $className,
                        'data-id' => $id,
                    ],
                ]);
            }

            // extra field for twig rendering
            $form->add('currentFieldName', HiddenType::class, ['data' => $fieldName]);
        });
    }

    private function addPostSubmitListener(FormBuilderInterface $builder, $fieldName, $className, $id): void
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) use ($fieldName, $className, $id) {
            $form = $event->getForm();
            $this->manager->persistTranslations($form, $className, $fieldName, $id, $this->locales);
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        // pass some variables for field rendering
        $view->vars['locales'] = $this->locales;
        $view->vars['currentlocale'] = $this->userLocale;
        $view->vars['translatedtablocales'] = $this->getTabTranslations();
    }

    private function getTabTranslations(): array
    {
        $translatedLocaleCodes = [];
        foreach ($this->locales as $locale) {
            $translatedLocaleCodes[$locale] = $this->getTranslatedLocalCode($locale);
        }

        return $translatedLocaleCodes;
    }

    private function getTranslatedLocalCode($locale): string
    {
        return Locale::getDisplayLanguage($locale, $this->userLocale);
    }

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $defaults = [
            'mapped' => false,
            'required' => false,
            'by_reference' => false,
        ];
        $resolver->setDefaults($defaults);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): ?string
    {
        return 'translations';
    }
}
