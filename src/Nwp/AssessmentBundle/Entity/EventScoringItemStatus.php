<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * EventScoringItemStatus
 *
 * @ORM\Table(name="event_scoring_item_status")
 * @ORM\Entity
 */
class EventScoringItemStatus
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="scoring_round_number", type="integer", nullable=false)
     */
    private $scoringRoundNumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="read_number", type="integer", nullable=false)
     */
    private $readNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time_created", type="datetime", nullable=true)
     */
    private $timeCreated;

    /**
     * @var \EventScoringItem
     *
     * @ORM\ManyToOne(targetEntity="EventScoringItem")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_scoring_item_id", referencedColumnName="id")
     * })
     */
    private $eventScoringItem;

    /**
     * @var \ScoringItemStatus
     *
     * @ORM\ManyToOne(targetEntity="ScoringItemStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * })
     */
    private $status;

    /**
     * @var \CreatedBy
     *
     * @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     * })
     */
    private $createdBy;
    
    /**
     * @var \AssignedTo
     *
     * @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="assigned_to", referencedColumnName="id")
     * })
     */
    private $assignedTo;
    
    
      /**
     * @var integer
     *
     * @ORM\Column(name="max_scoring_item_score_status_id", type="integer", nullable=false)
     */
    private $maxScoringItemScoreStatus;


     /**
     * @var string $description
     *
     * @ORM\Column(name="comment", type="string", length=500, nullable=true)
     */
    private $comment;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set scoringRoundNumber
     *
     * @param integer $scoringRoundNumber
     * @return EventScoringItemStatus
     */
    public function setScoringRoundNumber($scoringRoundNumber)
    {
        $this->scoringRoundNumber = $scoringRoundNumber;
    
        return $this;
    }

    /**
     * Get scoringRoundNumber
     *
     * @return integer 
     */
    public function getScoringRoundNumber()
    {
        return $this->scoringRoundNumber;
    }

    /**
     * Set readNumber
     *
     * @param integer $readNumber
     * @return EventScoringItemStatus
     */
    public function setReadNumber($readNumber)
    {
        $this->readNumber = $readNumber;
    
        return $this;
    }

    /**
     * Get readNumber
     *
     * @return integer 
     */
    public function getReadNumber()
    {
        return $this->readNumber;
    }

    /**
     * Set timeCreated
     *
     * @param \DateTime $timeCreated
     * @return EventScoringItemStatus
     */
    public function setTimeCreated($timeCreated)
    {
        $this->timeCreated = $timeCreated;
    
        return $this;
    }

    /**
     * Get timeCreated
     *
     * @return \DateTime 
     */
    public function getTimeCreated()
    {
        return $this->timeCreated;
    }

    /**
     * Set eventScoringItem
     *
     * @param \Nwp\AssessmentBundle\Entity\EventScoringItem $eventScoringItem
     * @return EventScoringItemStatus
     */
    public function setEventScoringItem(\Nwp\AssessmentBundle\Entity\EventScoringItem $eventScoringItem = null)
    {
        $this->eventScoringItem = $eventScoringItem;
    
        return $this;
    }

    /**
     * Get eventScoringItem
     *
     * @return \Nwp\AssessmentBundle\Entity\EventScoringItem 
     */
    public function getEventScoringItem()
    {
        return $this->eventScoringItem;
    }

    /**
     * Set status
     *
     * @param \Nwp\AssessmentBundle\Entity\ScoringItemStatus $status
     * @return EventScoringItemStatus
     */
    public function setStatus(\Nwp\AssessmentBundle\Entity\ScoringItemStatus $status = null)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return \Nwp\AssessmentBundle\Entity\ScoringItemStatus 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set createdBy
     *
     * @param Application\Sonata\UserBundle\Entity\User $createdBy
     * @return EventScoringItemStatus
     */
    public function setCreatedBy(\Application\Sonata\UserBundle\Entity\User $createdBy = null)
    {
        $this->createdBy = $createdBy;
    
        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \Application\Sonata\UserBundle\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }
    
    /**
     * Set assignedTo
     *
     * @param Application\Sonata\UserBundle\Entity\User $assignedTo
     * @return EventScoringItemStatus
     */
    public function setAssignedTo(\Application\Sonata\UserBundle\Entity\User $assignedTo = null)
    {
        $this->assignedTo = $assignedTo;
    
        return $this;
    }

    /**
     * Get assignedTo
     *
     * @return \Application\Sonata\UserBundle\Entity\User
     */
    public function getAssignedTo()
    {
        return $this->assignedTo;
    }
    
     /**
     * Set maxScoringItemScoreStatus
     *
     * @param integer $maxScoringItemScoreStatus
     * @return EventScoringItemStatus
     */
    public function setMaxScoringItemScoreStatus($maxScoringItemScoreStatus = null)
    {
        $this->maxScoringItemScoreStatus = $maxScoringItemScoreStatus;
    
        return $this;
    }

    /**
     * Get $maxScoringItemScoreStatus
     *
     * @return integer 
     */
    public function getMaxScoringItemScoreStatus()
    {
        return $this->maxScoringItemScoreStatus;
    }
    
    /**
     * Set comment
     *
     * @param string $comment
     * @return Comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    
        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }

    
    public function __toString()
    {
        return $this->status->getName(); 
    }
    
     /*=========================================================================*/    
    
    /* SCORING ITEM SCORE ASSOCIATIVE TABLE */
     /**
     *
     * @ORM\OneToMany(targetEntity="ScoringItemScore", mappedBy="eventScoringItemStatus", cascade={"all"}, orphanRemoval=true)
     */
    
    protected $scores;

    public function __construct()
    {
        $this->scores = new ArrayCollection();
    }
    
    /**
     * Get scores
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getScores()
    {
        return $this->scores;
    }
    
}