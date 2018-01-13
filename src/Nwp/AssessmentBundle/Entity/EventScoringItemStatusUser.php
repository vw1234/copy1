<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventScoringItemStatusUser
 *
 * @ORM\Table(name="event_scoring_item_status_user")
 * @ORM\Entity
 */
class EventScoringItemStatusUser
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
     * @var \DateTime
     *
     * @ORM\Column(name="time_created", type="datetime", nullable=true)
     */
    private $timeCreated;

    /**
     * @var \EventScoringItemStatus
     *
     * @ORM\ManyToOne(targetEntity="EventScoringItemStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_scoring_item_status_id", referencedColumnName="id")
     * })
     */
    private $eventScoringItemStatus;

    /**
     * @var \Subrole
     *
     * @ORM\ManyToOne(targetEntity="Subrole")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="subrole_id", referencedColumnName="id")
     * })
     */
    private $subrole;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;



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
     * Set timeCreated
     *
     * @param \DateTime $timeCreated
     * @return EventScoringItemStatusUser
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
     * Set eventScoringItemStatus
     *
     * @param \Nwp\AssessmentBundle\Entity\EventScoringItemStatus $eventScoringItemStatus
     * @return EventScoringItemStatusUser
     */
    public function setEventScoringItemStatus(\Nwp\AssessmentBundle\Entity\EventScoringItemStatus $eventScoringItemStatus = null)
    {
        $this->eventScoringItemStatus = $eventScoringItemStatus;
    
        return $this;
    }

    /**
     * Get eventScoringItemStatus
     *
     * @return \Nwp\AssessmentBundle\Entity\EventScoringItemStatus 
     */
    public function getEventScoringItemStatus()
    {
        return $this->eventScoringItemStatus;
    }

    /**
     * Set subrole
     *
     * @param \Nwp\AssessmentBundle\Entity\Subrole $subrole
     * @return EventScoringItemStatusUser
     */
    public function setSubrole(\Nwp\AssessmentBundle\Entity\Subrole $subrole = null)
    {
        $this->subrole = $subrole;
    
        return $this;
    }

    /**
     * Get subrole
     *
     * @return \Nwp\AssessmentBundle\Entity\Subrole 
     */
    public function getSubrole()
    {
        return $this->subrole;
    }

    /**
     * Set user
     *
     * @param Application\Sonata\UserBundle\Entity\User $user
     * @return EventScoringItemStatusUser
     */
    public function setUser(\Application\Sonata\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Application\Sonata\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}