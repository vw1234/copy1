<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * District
 *
 * @ORM\Table(name="district")
 * @ORM\Entity
 */
class District
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
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="lea_type", type="integer", nullable=true)
     */
    private $leaType;

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
     * @return District
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
     * Set leaType
     *
     * @param integer $leaType
     * @return District
     */
    public function setLeaType($leaType)
    {
        $this->leaType = $leaType;
    
        return $this;
    }

    /**
     * Get leaType
     *
     * @return integer 
     */
    public function getLeaType()
    {
        return $this->leaType;
    }

    /**
     * Set state
     *
     * @param \Nwp\AssessmentBundle\Entity\State $state
     * @return District
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
}