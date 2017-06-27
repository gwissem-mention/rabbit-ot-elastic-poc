<?php

namespace AppBundle\Command;

use AppBundle\Entity\Task;
use Elastica\Document as ElasticDocument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\Document;

class PopulateCommand extends ContainerAwareCommand
{
    const BATCH_SIZE = 2;

    private $indexManager;

    private $configManager;

    private $mappingBuilder;

    private $entityManager;

    protected function configure()
    {
        $this
            ->setName('search:populate')
            ->setDescription('Populate indexes from sample files')
        ;
    }

    protected function init()
    {
        $this->indexManager = $this->getContainer()->get('fos_elastica.index_manager');
        $this->configManager = $this->getContainer()->get('fos_elastica.config_manager');
        $this->mappingBuilder = $this->getContainer()->get('fos_elastica.mapping_builder');
        $this->entityManager = $this->getContainer()->get('doctrine')->getManager();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init();

        $this->importAttributes();

        $this->importCategories();
    }

    protected function importAttributes()
    {
        $filePath = $this->getContainer()->getParameter('kernel.root_dir') . '/Resources/data/sample_attributes.csv';

        $index = $this->indexManager->getIndex('attributes_en');
        $docType = $index->getType('attribute');

        $this->createIndex($index);

        if (($handle = fopen($filePath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $attributeData = [
                    'Id' => $data[0],
                    'label' => $data[1],
                    'group' => $data[2],
                    'sort_order' => $data[3],
                    'group_order' => $data[4],
                    'type' => $data[5],
                ];

                $attribute = new ElasticDocument($data[0], $attributeData);
                $docType->addDocument($attribute);
            }

            $index->refresh();
            fclose($handle);
        }
    }

    protected function importCategories()
    {
        $row = 0;
        $startId = 0;

        $filePath = $this->getContainer()->getParameter('kernel.root_dir') . '/Resources/data/sample_categories.csv';

        if (($handle = fopen($filePath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $attributeData = [
                    'category_id' => $data[0],
                    'category_name' => $data[1],
                    'category_description' => $data[2],
                    'category_order' => $data[3],
                ];

                $elasticaDoc = new ElasticDocument($data[0], $attributeData);
                $document = $this->pushDocumentToDB($elasticaDoc);

                $startId = ($startId) ?: $document->getId();

                $row++;

                if ($row % self::BATCH_SIZE == 0) {
                    $this->createBulkIndexTask($document, $startId);

                }
            }

            /* Don't forget the last batch in the end ! */
            $this->createBulkIndexTask($document, $startId);

            fclose($handle);
        }
    }

    protected function pushDocumentToDb($elasticaDoc)
    {
        $document = new Document();
        $document
            ->setContent(serialize($elasticaDoc->toArray()))
            ->setIndexName('categories_en')
            ->setDocType('category')
        ;

        $this->entityManager->persist($document);
        $this->entityManager->flush();

        return $document;
    }

    protected function createBulkIndexTask($document, $startId)
    {
        $task = new Task();
        $task
            ->setStatus(Task::STATUS_IN_PROGRESS)
            ->setStartId($startId)
            ->setEndId($document->getId())
            ->setDocType('category')
            ->setIndexName('categories_en')
    ;

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $msg = [
            'task_id' => $task->getId(),
            'start_id' => $task->getStartId(),
            'end_id' => $task->getEndId(),
            'type' => $task->getDocType(),
            'index' => $task->getIndexName(),
        ];
        $this->getContainer()->get('bulk_document_producer')->publish(serialize($msg));
    }

    protected function createIndex($index)
    {
        $indexConfig = $this->configManager->getIndexConfiguration($index->getName());
        $mapping = $this->mappingBuilder->buildIndexMapping($indexConfig);

        if (!$index->exists()) {
            $index->create($mapping);
        } else {
            $index->delete();
            $index->create($mapping);
        }

        return;
    }
}