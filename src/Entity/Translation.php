<?php

namespace Vairogs\Utils\Translatable\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vairogs\Utils\Translatable\Model\Translation as Model;

/**
 * @ORM\Table(name="vairogs_translation",indexes={
 *     @ORM\Index(name="translations_lookup_idx", columns={"locale", "object_class", "foreign_key"}),
 *     @ORM\Index(name="lookup_unique_idx", columns={"locale", "object_class", "foreign_key", "field"})
 * })
 * @ORM\Entity()
 */
class Translation extends Model
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
