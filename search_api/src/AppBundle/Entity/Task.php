<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Task
{
    const STATUS_IN_PROGRESS = 1;
    const STATUS_DONE = 2;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"default": 0, "unsigned": true})
     */
    protected $status;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"default": 0, "unsigned": true})
     */
    protected $startId;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"default": 0, "unsigned": true})
     */
    protected $endId;

    /**
     * @var string
     *
     * @ORM\Column(length=32)
     *
     */
    protected $docType;

    /**
     * @var string
     *
     * @ORM\Column(length=32)
     *
     */
    protected $indexName;


    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updatedAt;

    public function __construct()
    {
        $this->status = self::STATUS_IN_PROGRESS;
        $this->createdAt = new \DateTime('now');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return int
     */
    public function getStartId()
    {
        return $this->startId;
    }

    /**
     * @param int $startId
     */
    public function setStartId($startId)
    {
        $this->startId = $startId;

        return $this;
    }

    /**
     * @return int
     */
    public function getEndId()
    {
        return $this->endId;
    }

    /**
     * @param int $endId
     */
    public function setEndId($endId)
    {
        $this->endId = $endId;

        return $this;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getDocType()
    {
        return $this->docType;
    }

    /**
     * @param string $docType
     */
    public function setDocType($docType)
    {
        $this->docType = $docType;

        return $this;
    }

    /**
     * @return string
     */
    public function getIndexName()
    {
        return $this->indexName;
    }

    /**
     * @param string $indexName
     */
    public function setIndexName($indexName)
    {
        $this->indexName = $indexName;

        return $this;
    }
}
