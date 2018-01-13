<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Nwp\AssessmentBundle\Entity\EventGradeLevelBlock
 *
 * @ORM\Table(name="event_grade_level_block")
 * @ORM\Entity
 */
class EventGradeLevelBlock
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
     * @var integer $blockId
     *
     * @ORM\Column(name="block_id", type="integer", nullable=true)
     */
    protected $blockId;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     * })
     */
    protected $event;

    /**
     * @var GradeLevel
     *
     * @ORM\ManyToOne(targetEntity="GradeLevel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grade_level_id", referencedColumnName="id")
     * })
     */
    protected $gradeLevel;

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
     * @var integer $target
     *
     * @ORM\Column(name="target", type="decimal", precision=5, scale=2, nullable=true)
     */
    protected $target;
    
     /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     */
    private $isActive;

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
     * Set blockId
     *
     * @param integer $blockId
     * @return EventGradeLevelBlock
     */
    public function setBlockId($blockId)
    {
        $this->blockId = $blockId;
    
        return $this;
    }

    /**
     * Get blockId
     *
     * @return integer 
     */
    public function getBlockId()
    {
        return $this->blockId;
    }

    /**
     * Set event
     *
     * @param Nwp\AssessmentBundle\Entity\Event $event
     * @return EventGradeLevelBlock
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
     * Set gradeLevel
     *
     * @param Nwp\AssessmentBundle\Entity\GradeLevel $gradeLevel
     * @return EventGradeLevelBlock
     */
    public function setGradeLevel(\Nwp\AssessmentBundle\Entity\GradeLevel $gradeLevel = null)
    {
        $this->gradeLevel = $gradeLevel;
    
        return $this;
    }

    /**
     * Get gradeLevel
     *
     * @return Nwp\AssessmentBundle\Entity\GradeLevel 
     */
    public function getGradeLevel()
    {
        return $this->gradeLevel;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return EventGradeLevelBlock
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
     * @return EventGradeLevelBlock
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
     * Set tableId
     *
     * @param integer $target
     * @return EventGradeLevelBlock
     */
    public function setTarget($target)
    {
        $this->target = $target;
    
        return $this;
    }

    /**
     * Get target
     *
     * @return integer 
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Nav
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    
        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }
    
       /*=========================================================================*/    
    
    
/* EVENT USER ASSOCIATIVE TABLE */    

    /**
     *
     * @ORM\OneToMany(targetEntity="EventGradeLevelBlockPrompt", mappedBy="eventGradeLevelBlock", cascade={"all"}, orphanRemoval=true)
     */
    protected $pu;

    
    protected $prompts;
    
    /**
     * Constructor
     */

    public function __construct()
    {
        
        $this->pu = new ArrayCollection();
        $this->prompts = new ArrayCollection();
          
    }

    
 
    
   
    public function getPu()
    {
        return $this->pu;
    }

    
    public function setPu($pu)
    {
        foreach($pu as $p)
        {
            $p = new EventGradeLevelBlockPrompt();

            $p->setEventGradeLevelBlock($this);
            $p->setPrompt($p->getPrompt());
            $p->setTableId($p->getTableId());
           

            $this->addPu($pu);
        }
        
        #$this->pu = $pu;
    }
        
    
    public function getPrompt()
    {
        $prompts = new ArrayCollection();
        
        foreach($this->pu as $p)
        {
            $prompts[] = $p->getPrompt();
        }

        return $prompts;
    }
  
    
    public function setPrompt($prompts)
    {
        foreach($prompts as $p)
        {
            $pu = new EventGradeLevelBlockPrompt();

            $eu->setEventGradeLevelBlockPrompt($this);
            $eu->setPrompt($p);
            $eu->setTable($p);
           

            $this->addPu($pu);
        }
    }
    
    public function addPu($EventGradeLevelBlockPrompt)
    {
        $this->pu[] = $EventGradeLevelBlockPrompt;
    }
    
    
    
    public function removePu($EventGradeLevelBlockPrompt)
    {
        return $this->pu->removeElement($EventGradeLevelBlockPrompt);
    }
    
    
    
    
  
   
    
    
/*=========================================================================*/ 
   
public function __toString()
    {
        if ($this->getId()) {
		return $this->getEvent()->getName().", ".$this->getGradeLevel()->getName().", ".$this->getBlockId(); 
	} else {
        	return '';
        }    
    }
    
}
