<?php

namespace Vairogs\Utils\Translatable\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Enum;
use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class TranslatableField
{
    /**
     * @var string
     *
     * @Enum({"titled", "slugged", "ck_item"})
     */
    public $class;

    /**
     * @var string
     *
     * @Enum({
     *     Symfony\Component\Form\Extension\Core\Type\TextType::class,
     *     Symfony\Component\Form\Extension\Core\Type\TextareaType::class
     * })
     * @Required()
     */
    public $type;

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
