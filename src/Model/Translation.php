<?php

namespace Vairogs\Utils\Translatable\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Translation Model
 * @ORM\MappedSuperclass()
 */
abstract class Translation
{
    /**
     * @var string
     * @ORM\Column(name="locale", type="string", length=8, nullable=false)
     */
    protected $locale;

    /**
     * @var string
     * @ORM\Column(name="object_class", type="string", nullable=false)
     */
    protected $objectClass;

    /**
     * @var string
     * @ORM\Column(name="field", type="string", nullable=false)
     */
    protected $field;

    /**
     * @var int
     * @ORM\Column(name="foreign_key", type="integer", nullable=false)
     */
    protected $foreignKey;

    /**
     * @var string
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    protected $content;

    /**
     * Get content.
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get field.
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Set field.
     *
     * @param string $field
     *
     * @return $this
     */
    public function setField($field): self
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get foreignKey.
     * @return int
     */
    public function getForeignKey(): int
    {
        return $this->foreignKey;
    }

    /**
     * Set foreignKey.
     *
     * @param int $foreignKey
     *
     * @return $this
     */
    public function setForeignKey($foreignKey): self
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    /**
     * Get locale.
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Set locale.
     *
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale($locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get objectClass.
     * @return string
     */
    public function getObjectClass(): string
    {
        return $this->objectClass;
    }

    /**
     * Set objectClass.
     *
     * @param string $objectClass
     *
     * @return $this
     */
    public function setObjectClass($objectClass): self
    {
        $this->objectClass = $objectClass;

        return $this;
    }
}
