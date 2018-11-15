<?php

namespace Vairogs\Utils\Translatable\Controller;

use Doctrine\DBAL\DBALException;
use InvalidArgumentException;
use Lexik\Bundle\TranslationBundle\Entity\TransUnit;
use Lexik\Bundle\TranslationBundle\Manager\TranslationInterface;
use PDOException;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Yaml\Dumper;

trait NonActionTrait
{
    /**
     * Execute a batch download
     *
     * @param ProxyQueryInterface $queryProxy
     *
     * @return StreamedResponse
     * @internal param ProxyQueryInterface $query
     * @throws InvalidArgumentException
     */
    public function batchActionDownload(ProxyQueryInterface $queryProxy): StreamedResponse
    {
        $flashType = 'success';

        $dumper = new Dumper(4);

        $response = new StreamedResponse(function() use ($queryProxy, &$flashType, $dumper) {
            try {
                /**
                 * @var TransUnit $transUnit
                 */
                $iterates = $queryProxy->getQuery()->iterate();
                /** @var $iterates array */
                foreach ($iterates as $pos => $object) {
                    /** @var $object array */
                    foreach ($object as $transUnit) {
                        $chunkPrefix = $transUnit->getDomain().'__'.$transUnit->getKey().'__'.$transUnit->getId().'__';
                        $chunk = [];
                        /** @var TranslationInterface $translation */
                        foreach ($transUnit->getTranslations() as $translation) {
                            $chunk[$chunkPrefix.$translation->getLocale()] = $translation->getContent();
                        }
                        echo $dumper->dump($chunk, 2);
                        \flush();
                    }
                }
            } catch (PDOException $e) {
                $flashType = 'error';
                \flush();
            } catch (DBALException $e) {
                $flashType = 'error';
                \flush();
            }
        });

        $this->addFlash('sonata_flash_'.$flashType, 'translations.flash_batch_download_'.$flashType);

        $response->headers->set('Content-Type', 'text/x-yaml');
        $response->headers->set('Cache-Control', '');
        $response->headers->set('Transfer-Encoding', 'chunked');
        $response->headers->set('Last-Modified', \gmdate('D, d M Y H:i:s'));
        $contentDisposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'translations.yml');
        $response->headers->set('Content-Disposition', $contentDisposition);

        return $response;
    }

    public function __toString()
    {
        return 'vairogs.utils.lexik.translation.crud.controller';
    }

    abstract protected function addFlash(string $type, string $message);

    protected function getManagedLocales()
    {
        return $this->container->getParameter('lexik_translation.managed_locales');
    }
}
