<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * County
 *
 * @ORM\Table(name="county")
 * @ORM\Entity
 */
class County
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=80, nullable=true)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="county_fips_id", type="integer", nullable=true)
     */
    private $countyFipsId;

    /**
     * @var \State
     *
     * @ORM\ManyToOne(targetEntity="State")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="state_id", referencedColumnName="id")
     * })
     */
    private $state;



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
     * @return County
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
     * Set countyFipsId
     *
     * @param integer $countyFipsId
     * @return County
     */
    public function setCountyFipsId($countyFipsId)
    {
        $this->countyFipsId = $countyFipsId;
    
        return $this;
    }

    /**
     * Get countyFipsId
     *
     * @return integer 
     */
    public function getCountyFipsId()
    {
        return $this->countyFipsId;
    }

    /**
     * Set state
     *
     * @param \Nwp\AssessmentBundle\Entity\State $state
     * @return County
     */
    public function setState(\Nwp\AssessmentBundle\Entity\State $state = null)
    {
        $this->state = $state;
    
        return $this;
    }

    /**
     * Get state
     *
     * @return \Nwp\AssessmentBundle\Entity\State 
     */
    public function getState()
    {
        return $this->state;
    }
    
    public function __toString()
    {
        return $this->name; 
    }
}