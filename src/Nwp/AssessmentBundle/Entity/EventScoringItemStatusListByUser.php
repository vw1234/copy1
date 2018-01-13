<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventScoringItemStatusListByUser
 *
 * @ORM\Table(name="event_scoring_item_status_byuser_final")
 * @ORM\Entity(readOnly=true)
 */
class EventScoringItemStatusListByUser
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=true)
     * @ORM\Id
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
     * @var \ScoringItem
     *
     * @ORM\ManyToOne(targetEntity="ScoringItem")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="scoring_item_id", referencedColumnName="id")
     * })
     */
    private $scoringItem;
    
   /**
     * @var \ScoringItemType
     *
     * @ORM\ManyToOne(targetEntity="ScoringItemType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="scoring_item_type_id", referencedColumnName="id")
     * })
     */
    private $scoringItemType;

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
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="eu")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     * })
     */
    protected $event;
    
    
       /**
     * @var Component
     *
     * @ORM\ManyToOne(targetEntity="Component")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="component_id", referencedColumnName="id")
     * })
     */
    protected $component;
    
     /**
     * @var GradeLevelId
     *
     * @ORM\ManyToOne(targetEntity="GradeLevel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grade_level_id", referencedColumnName="id")
     * })
     */
    protected $gradeLevelId;
    
     /**
     * @var GradeLevelCreated
     *
     * @ORM\ManyToOne(targetEntity="GradeLevel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grade_level_id_created", referencedColumnName="id")
     * })
     */
    protected $gradeLevelCreated;
    
        /**
     * @var GradeLevelAssigned
     *
     * @ORM\ManyToOne(targetEntity="GradeLevel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grade_level_id_assigned", referencedColumnName="id")
     * })
     */
    protected $gradeLevelAssigned;
   
    
     /**
     * @var integer $tableIdCreated
     *
     * @ORM\Column(name="table_id_created", type="integer", nullable=true)
     */
    protected $tableIdCreated;
    
    
    /**
     * @var integer $roleIdCreated
     *
     * @ORM\Column(name="role_id_created", type="integer", nullable=true)
     */
    protected $roleIdCreated;
    
     /**
     * @var integer $tableIdAssigned
     *
     * @ORM\Column(name="table_id_assigned", type="integer", nullable=true)
     */
    protected $tableIdAssigned;
    

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
     * Set ScoringItem
     *
     * @param \Nwp\AssessmentBundle\Entity\EventScoringItem $scoringItem
     * @return ScoringItem
     */
    public function setScoringItem(\Nwp\AssessmentBundle\Entity\ScoringItem $scoringItem = null)
    {
        $this->scoringItem = $scoringItem;
    
        return $this;
    }

    /**
     * Get scoringItem
     *
     * @return \Nwp\AssessmentBundle\Entity\ScoringItem 
     */
    public function getScoringItem()
    {
        return $this->scoringItem;
    }
    
    /**
     * Set scoringItemType
     *
     * @param \Nwp\AssessmentBundle\Entity\ScoringItemType $scoringItemType
     * @return EventScoringItem
     */
    public function setScoringItemType(\Nwp\AssessmentBundle\Entity\ScoringItemType $scoringItemType = null)
    {
        $this->scoringItemType = $scoringItemType;
    
        return $this;
    }

    /**
     * Get scoringItemType
     *
     * @return \Nwp\AssessmentBundle\Entity\ScoringItemType 
     */
    public function getScoringItemType()
    {
        return $this->scoringItemType;
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
     * Set event
     *
     * @param Nwp\AssessmentBundle\Entity\Event $event
     * @return EventUser
     */
    public function setEvent(\Nwp\AssessmentBundle\Entity\Event $event = null)
    {
        $this->event = $event;
    
        return $this;
    }

    /**
     * Get event
     *
     * @return Nwp\AssessmentBundle\Entity\Event 
     */
    public function getEvent()
    {
        return $this->event;
    }
    
    /**
     * Set event
     *
     * @param Nwp\AssessmentBundle\Entity\Compoennt $component
     * @return EventUser
     */
    public function setComponent(\Nwp\AssessmentBundle\Entity\Component $component = null)
    {
        $this->component = $component;
    
        return $this;
    }

    /**
     * Get component
     *
     * @return Nwp\AssessmentBundle\Entity\Component 
     */
    public function getComponent()
    {
        return $this->component;
    }
    
        
    /**
     * Set gradeLevelId
     *
     * @param Nwp\AssessmentBundle\Entity\GradeLevel $gradeLevel
     * @return ScoringItem
     */
    public function setGradeLevelId(\Nwp\AssessmentBundle\Entity\GradeLevel $gradeLevel = null)
    {
        $this->gradeLevelId = $gradeLevelId;
    
        return $this;
    }
    /**
     * Get gradeLevelId
     *
     * @return Nwp\AssessmentBundle\Entity\GradeLevel 
     */
    public function getGradeLevelId()
    {
        return $this->gradeLevelId;
    }


     /**
     * Set gradeLevelCreated
     *
     * @param Nwp\AssessmentBundle\Entity\GradeLevel $gradeLevel
     * @return EventUser
     */
    public function setGradeLevelCreated(\Nwp\AssessmentBundle\Entity\GradeLevel $gradeLevel = null)
    {
        $this->gradeLevelCreated = $gradeLevelCreated;
    
        return $this;
    }
    
    /**
     * Get gradeLevelCreated
     *
     * @return Nwp\AssessmentBundle\Entity\GradeLevel 
     */
    public function getGradeLevelCreated()
    {
        return $this->gradeLevelCreated;
    }
    

    
    /**
     * Set gradeLevelAssigned
     *
     * @param Nwp\AssessmentBundle\Entity\GradeLevel $gradeLevel
     * @return EventUser
     */
    public function setGradeLevelAssigned(\Nwp\AssessmentBundle\Entity\GradeLevel $gradeLevel = null)
    {
        $this->gradeLevelAssigned = $gradeLevelAssigned;
    
        return $this;
    }

    /**
     * Get gradeLevelAssigned
     *
     * @return Nwp\AssessmentBundle\Entity\GradeLevel 
     */
    public function getGradeLevelAssigned()
    {
        return $this->gradeLevelAssigned;
    }
    
   
    
    /**
     * Set tableIdCreated
     *
     * @param integer $tableIdCreated
     * @return EventUser
     */
    public function setTableIdCreated($tableIdCreated)
    {
        $this->tableIdCreated = $tableIdCreated;
    
        return $this;
    }

    /**
     * Get tableIdCreated
     *
     * @return integer 
     */
    public function getTableIdCreated()
    {
        return $this->tableIdCreated;
    }
    
    
    
    /**
     * Set roleIdCreated
     *
     * @param integer $roleIdCreated
     * @return EventUser
     */
    public function setRoleIdCreated($roleIdCreated)
    {
        $this->roleIdCreated = $roleIdCreated;
    
        return $this;
    }

    /**
     * Get roleIdCreated
     *
     * @return integer 
     */
    public function getRoleIdCreated()
    {
        return $this->roleIdCreated;
    }
    
      /**
     * Set tableIdAssigned
     *
     * @param integer $tableIdAssigned
     * @return EventUser
     */
    public function setTableIdAssigned($tableIdAssigned)
    {
        $this->tableIdAssigned = $tableIdAssigned;
    
        return $this;
    }

    /**
     * Get tableIdAssigned
     *
     * @return integer 
     */
    public function getTableIdAssigned()
    {
        return $this->tableIdAssigned;
    }
    
 
   
    
    /*=========================================================================*/
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

    
}