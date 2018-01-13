<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Nwp\AssessmentBundle\Entity\EventUser
 *
 * @ORM\Table(name="event_user")
 * @ORM\Entity
 */
class EventUser
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
     * @var integer $tableId
     *
     * @ORM\Column(name="table_id", type="integer", nullable=true)
     */
    protected $tableId;

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
     * @var GradeLevel
     *
     * @ORM\ManyToOne(targetEntity="GradeLevel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grade_level_id", referencedColumnName="id")
     * })
     */
    protected $gradeLevel;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    protected $user;

    /**
     * @var Role
     *
     * @ORM\ManyToOne(targetEntity="Role", inversedBy="eu")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     * })
     */
    protected $role;

     /**
     * @var integer $target
     *
     * @ORM\Column(name="target", type="decimal", precision=5, scale=2, nullable=true)
     */
    protected $target;
    
      /**
     * @var integer $maxBlock
     *
     * @ORM\Column(name="max_block", type="integer", nullable=true)
     */
    protected $maxBlock;
    
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
     * Set tableId
     *
     * @param integer $tableId
     * @return EventUser
     */
    public function setTableId($tableId)
    {
        $this->tableId = $tableId;
    
        return $this;
    }

    /**
     * Get tableId
     *
     * @return integer 
     */
    public function getTableId()
    {
        return $this->tableId;
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
     * Set gradeLevel
     *
     * @param Nwp\AssessmentBundle\Entity\GradeLevel $gradeLevel
     * @return EventUser
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
     * Set user
     *
     * @param Application\Sonata\UserBundle\Entity\User $user
     * @return EventUser
     */
    public function setUser(\Application\Sonata\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return Application\Sonata\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set role
     *
     * @param Nwp\AssessmentBundle\Entity\Role $role
     * @return EventUser
     */
    public function setRole(\Nwp\AssessmentBundle\Entity\Role $role = null)
    {
        $this->role = $role;
    
        return $this;
    }

    /**
     * Get role
     *
     * @return Nwp\AssessmentBundle\Entity\Role 
     */
    public function getRole()
    {
        return $this->role;
    }
    
    /**
     * Set target
     *
     * @param integer $target
     * @return EventUser
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
     * Set maxBlock
     *
     * @param integer $maxBlock
     * @return EventUser
     */
    public function setMaxBlock($maxBlock)
    {
        $this->maxBlock = $maxBlock;
    
        return $this;
    }

    /**
     * Get maxBLock
     *
     * @return integer 
     */
    public function getMaxBlock()
    {
        return $this->maxBlock;
    }
    
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

   // public function __construct()
   // {
        
       # $this->eu = new ArrayCollection();
       # $this->users = new ArrayCollection();
       
            
   // }

     
   
    public function getEu()
    {
        return $this->eu;
    }

    
    public function setEu($eu)
    {
        $this->eu = $eu;
    }
        
    
    public function getEuUser()
    {
        $users = new ArrayCollection();
        
        foreach($this->eu as $e)
        {
            $users[] = $e->getEuUser();
        }

        return $users;
    }
  
    
    public function setEuUser($users)
    {
        foreach($users as $u)
        {
            $eu = new EventUser();

            $eu->setEuEvent($this);
            
            $eu->setGradeLevel($u);
            $eu->setScoringTable($u);
            $eu->setEuUser($u);
            $eu->setRole($u);

            $this->addEu($eu);
        }
    }
    
    
    public function getEuEvent()
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
    
  
   public function __toString()
    {
	if ($this->getId()) {
		return $this->getUser()->getUsername();
    	} else {
      		return  '';
   	}
    }
    
    
/*=========================================================================*/  
    
    /*=========================================================================*/ 
    
    //Many to Many Unidirectional association between event_user and groupings tables
    
    // ...

    /**
     * @ORM\ManyToMany(targetEntity="Grouping")
     * @ORM\JoinTable(name="event_user_grouping",
     *      joinColumns={@ORM\JoinColumn(name="event_user_id", referencedColumnName="id")},
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
