<?php

namespace Vairogs\Utils\Translatable\Event\Listener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

class TranslationMappingListener
{
    protected $tableName;

    /**
     * TranslationMappingListener constructor.
     *
     * @param $tableName
     */
    public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();
        $oldName = $classMetadata->getTableName();
        if ($oldName === 'vairogs_translation' && $this->tableName !== $oldName) {
            $classMetadata->setPrimaryTable(['name' => $this->tableName]);
        }
    }
}
