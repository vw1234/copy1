<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventScoringItemStatusList
 *
 * @ORM\Table(name="event_scoring_item_status_list_final")
 * @ORM\Entity(readOnly=true)
 */
class EventScoringItemStatusList
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
     * @ORM\Column(name="max_scoring_round_number", type="integer", nullable=false)
     */
    private $maxScoringRoundNumber;

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
     * @var \ScoringItemStatus
     *
     * @ORM\ManyToOne(targetEntity="ScoringItemStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="max_status_id", referencedColumnName="id")
     * })
     */
    private $maxStatus;

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
     * @var \MaxScoreStatusId
     *
     * @ORM\ManyToOne(targetEntity="ScoringItemStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="max_score_status_id", referencedColumnName="id")
     * })
     */
    private $maxScoreStatusId;
    
    
       /**
     * @var \MaxScoreCreatedBy
     *
     * @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="max_score_created_by", referencedColumnName="id")
     * })
     */
    private $maxScoreCreatedBy;
    
    /**
     * @var \MaxScoreAssignedTo
     *
     * @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="max_score_assigned_to", referencedColumnName="id")
     * })
     */
    private $maxScoreAssignedTo;


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
     * @var GradeLevelScored
     *
     * @ORM\ManyToOne(targetEntity="GradeLevel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grade_level_id_scored", referencedColumnName="id")
     * })
     */
    protected $gradeLevelScored;
    
     /**
     * @var integer $tableIdCreated
     *
     * @ORM\Column(name="table_id_created", type="integer", nullable=true)
     */
    protected $tableIdCreated;
    
     /**
     * @var integer $tableIdAssigned
     *
     * @ORM\Column(name="table_id_assigned", type="integer", nullable=true)
     */
    protected $tableIdAssigned;
    
      /**
     * @var integer $tableIdScored
     *
     * @ORM\Column(name="table_id_scored", type="integer", nullable=true)
     */
    protected $tableIdScored; 
    
       /**
     * @var \StatusAssignedCreatedBy
     *
     * @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_assigned_created_by", referencedColumnName="id")
     * })
     */
    private $statusAssignedCreatedBy;
    
    /**
     * @var \StatusAssignedAssignedTo
     *
     * @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_assigned_assigned_to", referencedColumnName="id")
     * })
     */
    private $statusAssignedAssignedTo;
    
       /**
     * @var \ScoredBy
     *
     * @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="scored_by", referencedColumnName="id")
     * })
     */
    private $scoredBy;
    
     /**
     * @var integer
     *
     * @ORM\Column(name="is_alert", type="integer", nullable=false)
     */
    protected $isAlert;

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
     * @var \Prompt
     *
     * @ORM\ManyToOne(targetEntity="Prompt")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prompt_id", referencedColumnName="id")
     * })
     */
    protected $prompt;

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
     * Get maxScoringRoundNumber
     *
     * @return integer 
     */
    public function getMaxScoringRoundNumber()
    {
        return $this->maxScoringRoundNumber;
    }
    
     /**
     * Set maxScoringRoundNumber
     *
     * @param integer $maxScoringRoundNumber
     * @return EventScoringItemStatus
     */
    public function setMaxScoringRoundNumber($maxScoringRoundNumber)
    {
        $this->maxScoringRoundNumber = $maxScoringRoundNumber;
    
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
     * Set maxStatus
     *
     * @param \Nwp\AssessmentBundle\Entity\ScoringItemStatus $maxStatus
     * @return EventScoringItemStatus
     */
    public function setMaxStatus(\Nwp\AssessmentBundle\Entity\ScoringItemStatus $maxStatus = null)
    {
        $this->maxStatus = $maxStatus;
    
        return $this;
    }

    /**
     * Get maxStatus
     *
     * @return \Nwp\AssessmentBundle\Entity\ScoringItemStatus 
     */
    public function getMaxStatus()
    {
        return $this->maxStatus;
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
     * Set maxScoreStatusId
     *
     * @param \Nwp\AssessmentBundle\Entity\ScoringItemStatus $maxScoreStatusId
     * @return EventScoringItemStatusList
     */
    public function setMaxScoreStatusId(\Nwp\AssessmentBundle\Entity\ScoringItemStatus $maxScoreStatusId = null)
    {
        $this->maxScoreStatusId = $maxScoreStatusId;
    
        return $this;
    }

    /**
     * Get $maxScoreStatusId
     *
     * @return \Nwp\AssessmentBundle\Entity\ScoringItemStatus 
     */
    public function getMaxScoreStatusId()
    {
        return $this->maxScoreStatusId;
    }
    
    /**
     * Set maxScoreCreatedBy
     *
     * @param Application\Sonata\UserBundle\Entity\User $maxScoreCreatedBy
     * @return EventScoringItemStatusList
     */
    public function setMaxScoreCreatedBy(\Application\Sonata\UserBundle\Entity\User $maxScoreCreatedBy = null)
    {
        $this->maxScoreCreatedBy = $maxScoreCreatedBy;
    
        return $this;
    }

    /**
     * Get maxScoreCreatedBy
     *
     * @return \Application\Sonata\UserBundle\Entity\User
     */
    public function getMaxScoreCreatedBy()
    {
        return $this->maxScoreCreatedBy;
    }
    
    /**
     * Set maxScoreAssignedTo
     *
     * @param Application\Sonata\UserBundle\Entity\User $maxScoreAssignedTo
     * @return EventScoringItemStatusList
     */
    public function setMaxScoreAssignedTo(\Application\Sonata\UserBundle\Entity\User $maxScoreAssignedTo = null)
    {
        $this->maxScoreAssignedTo = $maxScoreAssignedTo;
    
        return $this;
    }

    /**
     * Get maxScoreAssignedTo
     *
     * @return \Application\Sonata\UserBundle\Entity\User
     */
    public function getMaxScoreAssignedTo()
    {
        return $this->maxScoreAssignedTo;
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
     * Set component
     *
     * @param Nwp\AssessmentBundle\Entity\Component $component
     * @return Component
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
     * Set gradeLevelScored
     *
     * @param Nwp\AssessmentBundle\Entity\GradeLevel $gradeLevel
     * @return EventUser
     */
    public function setGradeLevelScored(\Nwp\AssessmentBundle\Entity\GradeLevel $gradeLevelScored = null)
    {
        $this->gradeLevelScored = $gradeLevelScored;
    
        return $this;
    }

    /**
     * Get gradeLevelScored
     *
     * @return Nwp\AssessmentBundle\Entity\GradeLevel 
     */
    public function getGradeLevelScored()
    {
        return $this->gradeLevelScored;
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
    
      /**
     * Set tableIdScored
     *
     * @param integer $tableIdScored
     * @return EventUser
     */
    public function setTableIdScored($tableIdScored)
    {
        $this->tableIdScored = $tableIdScored;
    
        return $this;
    }

    /**
     * Get tableIdAssigned
     *
     * @return integer 
     */
    public function getTableIdScored()
    {
        return $this->tableIdScored;
    }
    
   /**
     * Set statusAssignedCreatedBy
     *
     * @param Application\Sonata\UserBundle\Entity\User $statusAssignedCreatedBy
     * @return EventScoringItemStatusList
     */
    public function setStatusAssignedCreatedBy(\Application\Sonata\UserBundle\Entity\User $statusAssignedCreatedBy = null)
    {
        $this->statusAssignedCreatedBy = $statusAssignedCreatedBy;
    
        return $this;
    }

    /**
     * Get statusAssignedCreatedBy
     *
     * @return \Application\Sonata\UserBundle\Entity\User
     */
    public function getStatusAssignedCreatedBy()
    {
        return $this->statusAssignedCreatedBy;
    }
    
    /**
     * Set statusAssignedAssignedTo
     *
     * @param Application\Sonata\UserBundle\Entity\User $statusAssignedAssignedTo
     * @return EventScoringItemStatusList
     */
    public function setStatusAssignedAssignedTo(\Application\Sonata\UserBundle\Entity\User $statusAssignedAssignedTo = null)
    {
        $this->statusAssignedAssignedTo = $statusAssignedAssignedTo;
    
        return $this;
    }

    /**
     * Get statusAssignedAssignedTo
     *
     * @return \Application\Sonata\UserBundle\Entity\User
     */
    public function getStatusAssignedAssignedTo()
    {
        return $this->statusAssignedAssignedTo;
    } 
    
     /**
     * Set statusScoredBy
     *
     * @param Application\Sonata\UserBundle\Entity\User $scoredBy
     * @return EventScoringItemStatusList
     */
    public function setScoredBy(\Application\Sonata\UserBundle\Entity\User $scoredBy = null)
    {
        $this->scoredBy = $scoredBy;
    
        return $this;
    }

    /**
     * Get scoredBy
     *
     * @return \Application\Sonata\UserBundle\Entity\User
     */
    public function getScoredBy()
    {
        return $this->scoredBy;
    } 
    
    /**
     * Set prompt
     *
     * @param \Nwp\AssessmentBundle\Entity\Prompt $prompt
     * @return EventGradeLevelBlockPrompt
     */
    public function setPrompt(\Nwp\AssessmentBundle\Entity\Prompt $prompt = null)
    {
        $this->prompt = $prompt;
    
        return $this;
    }

    /**
     * Get prompt
     *
     * @return \Nwp\AssessmentBundle\Entity\Prompt 
     */
    public function getPrompt()
    {
        return $this->prompt;
    }
    
     /**
     * Set isAlert
     *
     * @param Nwp\AssessmentBundle\Entity\ScoringItemStatus $isAlert
     * @return ScoringItemStatus
     */
    public function setIsAlert(\Nwp\AssessmentBundle\Entity\ScoringItemStatus $isAlert = null)
    {
        $this->isAlert = $isAlert;
    
        return $this;
    }
    /**
     * Get isAlert
     *
     * @return Nwp\AssessmentBundle\Entity\ScoringItemStatus 
     */
    public function getIsAlert()
    {
        return $this->isAlert;
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