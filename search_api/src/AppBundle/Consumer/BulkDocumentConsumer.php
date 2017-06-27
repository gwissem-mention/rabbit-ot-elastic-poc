<?php
/**
 * Created by PhpStorm.
 * User: wissem
 * Date: 27/06/17
 * Time: 11:40
 */

namespace AppBundle\Consumer;

use FOS\ElasticaBundle\Configuration\ConfigManager;
use FOS\ElasticaBundle\Index\IndexManager;
use FOS\ElasticaBundle\Index\MappingBuilder;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\Task;
use Elastica\Document;

class BulkDocumentConsumer implements ConsumerInterface
{
    private $entityManager;

    private $configManager;

    private $mappingBuilder;

    private $indexManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        IndexManager $indexManager,
        MappingBuilder $mappingBuilder,
        ConfigManager $configManager
    )
    {
        $this->entityManager = $entityManager;
        $this->indexManager = $indexManager;
        $this->mappingBuilder = $mappingBuilder;
        $this->configManager = $configManager;
    }

    public function execute(AMQPMessage $msg)
    {
        $payload = unserialize($msg->getBody());
        $task = $this->getTask($payload['task_id']);

        if (!$task || $task->getStatus() == Task::STATUS_DONE) {
            return false;
        }

        echo "Found the task ! \n";
        $documents = $this->getTaskDocuments($task);
        echo sprintf("Got %d documents to index \n", count($documents));
        $this->bulkIndex($documents);

        $this->markTaskAsDone($task);

        return true;
    }

    protected function getTask($taskId)
    {
        return $this->entityManager->getRepository('AppBundle:Task')
            ->find($taskId);
    }

    protected function getTaskDocuments(Task $task)
    {
        $query = $this->entityManager->getRepository('AppBundle:Document')->createQueryBuilder('d')
            ->where('d.id >= :start_id')
            ->andWhere('d.id <= :end_id')
            ->andWhere('d.docType = :doc_type')
            ->andWhere('d.indexName = :index_name')
            ->setParameter('start_id', $task->getStartId())
            ->setParameter('end_id', $task->getEndId())
            ->setParameter('doc_type', $task->getDocType())
            ->setParameter('index_name', $task->getIndexName())
            ->getQuery()
        ;

        return $query->getResult();
    }

    protected function bulkIndex($documents)
    {
        $index = $this->indexManager->getIndex('categories_en');
        $docType = $index->getType('attribute');

        $this->createIndex($index);
        echo "Start bulk index \n";
        foreach ($documents as $document) {
            $data = unserialize($document->getContent());
            $elasticaDoc = new Document($data['_id'], $data['_source']);
            $docType->addDocument($elasticaDoc);
        }

        echo "Refresh index \n";
        $index->refresh();
    }

    protected function markTaskAsDone(Task $task)
    {
        $task
            ->setStatus(Task::STATUS_DONE)
            ->setUpdatedAt(new \DateTime('now'))
        ;

        $this->entityManager->persist($task);
        $this->entityManager->flush();
    }

    protected function createIndex($index)
    {
        echo "Create index \n";
        $indexConfig = $this->configManager->getIndexConfiguration($index->getName());
        $mapping = $this->mappingBuilder->buildIndexMapping($indexConfig);

        if (!$index->exists()) {
            $index->create($mapping);
        } else {
            $index->delete();
            $index->create($mapping);
        }

        echo "Index Created \n";

        return;
    }
}