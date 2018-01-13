<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventScoringItem
 *
 * @ORM\Table(name="event_scoring_item")
 * @ORM\Entity
 */
class EventScoringItem
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
     * @var boolean
     *
     * @ORM\Column(name="is_random", type="boolean", nullable=false)
     */
    private $isRandom;
    
     /**
     * @var integer
     *
     * @ORM\Column(name="scoring_round_number", type="integer", nullable=true)
     */
    private $scoringRoundNumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="read_number", type="integer", nullable=true)
     */
    private $readNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_updated", type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @var \Event
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     * })
     */
    private $event;

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
     * @var Component
     *
     * @ORM\ManyToOne(targetEntity="Component")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="component_id", referencedColumnName="id")
     * })
     */
    private $component;
    
    
    
    /**
     * @var \MaxEventScoringItemStatus
     *
     * @ORM\ManyToOne(targetEntity="EventScoringItemStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="max_event_scoring_item_status_id", referencedColumnName="id")
     * })
     */
    private $maxEventScoringItemStatus;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set isRandom
     *
     * @param boolean $isRandom
     * @return EventScoringItem
     */
    public function setIsRandom($isRandom)
    {
        $this->isRandom = $isRandom;
    
        return $this;
    }

    /**
     * Get isRandom
     *
     * @return boolean 
     */
    public function getIsRandom()
    {
        return $this->isRandom;
    }
    
    /**
     * Set scoringRoundNumber
     *
     * @param integer $scoringRoundNumber
     * @return EventScoringItem
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
     * @return EventScoringItem
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
     * Set dateUpdated
     *
     * @param \DateTime $dateUpdated
     * @return EventScoringItem
     */
    public function setDateUpdated($dateUpdated)
    {
        $this->dateUpdated = $dateUpdated;
    
        return $this;
    }

    /**
     * Get dateUpdated
     *
     * @return \DateTime 
     */
    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }

    /**
     * Set event
     *
     * @param \Nwp\AssessmentBundle\Entity\Event $event
     * @return EventScoringItem
     */
    public function setEvent(\Nwp\AssessmentBundle\Entity\Event $event = null)
    {
        $this->event = $event;
    
        return $this;
    }

    /**
     * Get event
     *
     * @return \Nwp\AssessmentBundle\Entity\Event 
     */
    public function getEvent()
    {
        return $this->event;
    }
    
    /**
     * Set component
     *
     * @param Nwp\AssessmentBundle\Entity\Component $component
     * @return Event
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
     * Set scoringItem
     *
     * @param \Nwp\AssessmentBundle\Entity\ScoringItem $scoringItem
     * @return EventScoringItem
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
     * Set maxEventScoringItemStatus
     *
     * @param \Nwp\AssessmentBundle\Entity\EventScoringItemUser $maxEventScoringItemStatus
     * @return EventScoringItem
     */
    public function setMaxEventScoringItemStatus(\Nwp\AssessmentBundle\Entity\EventScoringItemStatus $maxEventScoringItemStatus = null)
    {
        $this->maxEventScoringItemStatus = $maxEventScoringItemStatus;
    
        return $this;
    }

    /**
     * Get maxEventScoringItemStatus
     *
     * @return \Nwp\AssessmentBundle\Entity\EventScoringItemStatus 
     */
    public function getMaxEventScoringItemStatus()
    {
        return $this->maxEventScoringItemStatus;
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
     * @return EventScoringItem
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
    
     public function __toString()
    {
        return $this->status->getName(); 
    }
    
    /*=========================================================================*/ 
    
    //Many to Many Unidirectional association between event_scoring_item and groupings tables
    
    // ...

    /**
     * @ORM\ManyToMany(targetEntity="Grouping")
     * @ORM\JoinTable(name="event_scoring_item_grouping",
     *      joinColumns={@ORM\JoinColumn(name="event_scoring_item_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="grouping_id", referencedColumnName="id")}
     *      )
     */
    private $groupings;

    // ...

    public function __construct() {
        $this->groupings = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function getGroupings()
    {
        return $this->groupings;
    }

    
    public function setGroupings($groupings)
    {
        $this->groupings = $groupings;
    }
    
    /*=========================================================================*/ 
}