<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Nwp\AssessmentBundle\Entity\Rolegroup
 *
 * @ORM\Table(name="rolegroup")
 * @ORM\Entity
 */
class Rolegroup
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
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    private $name;

    /**
     * @var Structure
     *
     * @ORM\ManyToOne(targetEntity="Structure")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="structure_id", referencedColumnName="id")
     * })
     */
    private $structure;



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
     * @return Rolegroup
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
     * Set structure
     *
     * @param Nwp\AssessmentBundle\Entity\Structure $structure
     * @return Rolegroup
     */
    public function setStructure(\Nwp\AssessmentBundle\Entity\Structure $structure = null)
    {
        $this->structure = $structure;
    
        return $this;
    }

    /**
     * Get structure
     *
     * @return Nwp\AssessmentBundle\Entity\Structure 
     */
    public function getStructure()
    {
        return $this->structure;
    }
    
    /**
     * Get rolegroup
     *
     */
    public function getRolegroup()
    {
        return $this;
    }

    
    public function __toString()
    {
        return $this->name; 
    }

}