<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * Nwp\AssessmentBundle\Entity\Grouping
 *
 * @ORM\Table(name="grouping")
 * @ORM\Entity
 */
class Grouping
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
     * @var \GroupingType
     *
     * @ORM\ManyToOne(targetEntity="GroupingType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grouping_type_id", referencedColumnName="id")
     * })
     */
    
     private $groupingType;
   
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
     * @return Grouping
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
     * Set groupingType
     *
     * @param \Nwp\AssessmentBundle\Entity\GroupingType $groupingType
     * @return Grouping
     */
    
    public function setGroupingType(\Nwp\AssessmentBundle\Entity\GroupingType $groupingType = null)
    {
        $this->groupingType = $groupingType;
    
        return $this;
    }

    /**
     * Get scoringItem
     *
     * @return \Nwp\AssessmentBundle\Entity\GroupingType 
     */
    public function getGroupingType()
    {
        return $this->groupingType;
    }

   
    public function __toString()
    {
        if ($this->getId()) {
		return $this->name;
        } else {
       		return '';
        } 
    }
    
    //Many to Many Unidirectional association between event_scoring_item and groupings tables
    
    // ...

    /**
     * @ORM\ManyToMany(targetEntity="EventUser")
     * @ORM\JoinTable(name="event_user_grouping",
     *      joinColumns={@ORM\JoinColumn(name="event_user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="grouping_id", referencedColumnName="id")}
     *      )
     */
    private $eus;
    

    // ...

    public function __construct() {
        $this->eus = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function getEventUsers()
    {
        return $this->eus;
    }

    
    public function setEventUsers($eus)
    {
        $this->eus = $eus;
    }
    
    
}
