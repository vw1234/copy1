<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Nwp\AssessmentBundle\Entity\EventUser;

/**
 * Nwp\AssessmentBundle\Entity\Event
 *
 * @ORM\Table(name="event")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Event
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=false)
     */
    private $name;

    /**
     * @var \DateTime $startDate
     *
     * @ORM\Column(name="start_date", type="datetime", nullable=false)
     */
    private $startDate;

    /**
     * @var \DateTime $endDate
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=false)
     */
    private $endDate;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", length=250, nullable=true)
     */
    private $description;
    
     /**
     * @var string $location
     *
     * @ORM\Column(name="location", type="string", length=250, nullable=true)
     */
    private $location;
    
      /**
     * @var string $announcements
     *
     * @ORM\Column(name="announcements", type="string", length=250, nullable=true)
     */
    private $announcements;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Material", inversedBy="event")
     * @ORM\JoinTable(name="event_material",
     *   joinColumns={
     *     @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="material_id", referencedColumnName="id")
     *   }
     * )
     */
    private $material;

     /**
     * @var \EventType
     *
     * @ORM\ManyToOne(targetEntity="EventType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_type_id", referencedColumnName="id")
     * })
     */
    private $eventType;
    
    /**
     * @var ScoringRubric
     *
     * @ORM\ManyToOne(targetEntity="ScoringRubric")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="scoring_rubric_id", referencedColumnName="id")
     * })
     */
    private $scoringRubric;
    
     /**
     * @var integer $adjudicationTrigger
     *
     * @ORM\Column(name="adjudication_trigger", type="integer", nullable=true)
     */
    private $adjudicationTrigger;

     /**
     * @var integer $secondScoringTableTrigger
     *
     * @ORM\Column(name="second_scoring_table_trigger", type="integer", nullable=true)
     */
    private $secondScoringTableTrigger=2;
  
    /*=========================================================================*/    
    
    
/* EVENT USER ASSOCIATIVE TABLE */    

    /**
     *
     * @ORM\OneToMany(targetEntity="EventUser", mappedBy="event", cascade={"all"}, orphanRemoval=true)
     */
    protected $eu;

    
    protected $users;
    
    /**
     * Constructor
     */

    public function __construct()
    {
        
        $this->eu = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->material = new \Doctrine\Common\Collections\ArrayCollection();     
    }

    
 
    
   
    public function getEu()
    {
        return $this->eu;
    }

    
    public function setEu($eu)
    {
        $this->eu = $eu;
    }
        
    
    public function getUser()
    {
        $users = new ArrayCollection();
        
        foreach($this->eu as $e)
        {
            $users[] = $e->getUser();
        }

        return $users;
    }
  
    
    public function setUser($users)
    {
        foreach($users as $u)
        {
            $eu = new EventUser();

            $eu->setEvent($this);
            $eu->setGradeLevel($u);
            $eu->setScoringTable($u);
            $eu->setUser($u);
            $eu->setRole($u);

            $this->addEu($eu);
        }
    }
    
    
    public function getEvent()
    {
        return $this;
    }

    
    
    public function addEu($EventUser)
    {
        $this->eu[] = $EventUser;
    }
    
    
    
    public function removeEu($EventUser)
    {
        return $this->eu->removeElement($EventUser);
    }
  
   
    
    
/*=========================================================================*/    

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
     * Set name
     *
     * @param string $name
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Event
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    
        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return Event
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    
        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Event
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Set location
     *
     * @param string $location
     * @return Event
     */
    public function setLocation($location)
    {
        $this->location = $location;
    
        return $this;
    }

    /**
     * Get location
     *
     * @return string 
     */
    public function getLocation()
    {
        return $this->location;
    }
    
    /**
     * Set announcements
     *
     * @param string $announcements
     * @return Event
     */
    public function setAnnouncements($announcements)
    {
        $this->announcements = $announcements;
    
        return $this;
    }

    /**
     * Get announcements
     *
     * @return string 
     */
    public function getAnnouncements()
    {
        return $this->announcements;
    }

    /**
     * Add material
     *
     * @param Nwp\AssessmentBundle\Entity\Material $material
     * @return Event
     */
    public function addMaterial(\Nwp\AssessmentBundle\Entity\Material $material)
    {
        $this->material[] = $material;
    
        return $this;
    }

    /**
     * Remove material
     *
     * @param Nwp\AssessmentBundle\Entity\Material $material
     */
    public function removeMaterial(\Nwp\AssessmentBundle\Entity\Material $material)
    {
        $this->material->removeElement($material);
    }

    /**
     * Get material
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getMaterial()
    {
        return $this->material;
    }
    
    /**
     * Set eventType
     *
     * @param \Nwp\AssessmentBundle\Entity\EventType $eventType
     * @return Event
     */
    public function setEventType(\Nwp\AssessmentBundle\Entity\EventType $eventType = null)
    {
        $this->eventType = $eventType;
    
        return $this;
    }

    /**
     * Get eventType
     *
     * @return \Nwp\AssessmentBundle\Entity\eventType 
     */
    public function getEventType()
    {
        return $this->eventType;
    }


    /**
     * Set scoringRubric
     *
     * @param Nwp\AssessmentBundle\Entity\ScoringRubric $scoringRubric
     * @return Event
     */
    public function setScoringRubric(\Nwp\AssessmentBundle\Entity\ScoringRubric $scoringRubric = null)
    {
        $this->scoringRubric = $scoringRubric;
    
        return $this;
    }

    /**
     * Get scoringRubric
     *
     * @return Nwp\AssessmentBundle\Entity\ScoringRubric 
     */
    public function getScoringRubric()
    {
        return $this->scoringRubric;
    }
    
     /**
     * Set adjudicationTrigger
     *
     * @param integer $adjudicationTrigger
     * @return Event
     */
    public function setAdjudicationTrigger($adjudicationTrigger)
    {
        $this->adjudicationTrigger = $adjudicationTrigger;
    
        return $this;
    }

    /**
     * Get adjudicationTrigger
     *
     * @return integer 
     */
    public function getAdjudicationTrigger()
    {
        return $this->adjudicationTrigger;
    }
    
    /**
     * Set secondScoringTableTrigger
     *
     * @param integer $secondScoringTableTrigger
     * @return Event
     */
    public function setSecondScoringTableTrigger($secondScoringTableTrigger)
    {
        $this->secondScoringTableTrigger = $secondScoringTableTrigger;
    
        return $this;
    }

    /**
     * Get secondScoringTableTrigger
     *
     * @return integer 
     */
    public function getSecondScoringTableTrigger()
    {
        return $this->secondScoringTableTrigger;
    }
    
    public function __toString()
    {
        return $this->name; 
    }
    
    
}