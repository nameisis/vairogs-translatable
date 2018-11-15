<?php

namespace Vairogs\Utils\Translatable\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

abstract class BaseAdmin extends AbstractAdmin
{
    public function getTranslationFilter(ProxyQuery $queryBuilder, $alias, $field, $value): bool
    {
        if (!isset($value['value'])) {
            return false;
        }
        $queryBuilder->leftJoin('Vairogs:Translation', 't', 'WITH', 't.foreignKey = '.$alias.'.id');
        $queryBuilder->andWhere("t.field = '$field'");
        $queryBuilder->andWhere("t.objectClass = '".$this->getClass()."'");
        $queryBuilder->andWhere("t.content LIKE '%".$value['value']."%'");
        $queryBuilder->setFirstResult(0);

        return true;
    }
}
