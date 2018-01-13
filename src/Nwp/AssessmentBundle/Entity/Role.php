<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Nwp\AssessmentBundle\Entity\Role
 *
 * @ORM\Table(name="role")
 * @ORM\Entity
 */
class Role
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
     * @var string $name
     *
     * @ORM\Column(name="display_name", type="string", length=45, nullable=true)
     */
    private $displayName;

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
     * @return Role
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
     * Set name
     *
     * @param string $displayName
     * @return Role
     */
    public function setDisplayName($dislayName)
    {
        $this->displayName = $displayName;
    
        return $this;
    }

    /**
     * Get displayName
     *
     * @return string 
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Set structure
     *
     * @param Nwp\AssessmentBundle\Entity\Structure $structure
     * @return Role
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
     * Get role
     *
     */
    public function getRole()
    {
        return $this;
    }

    
    public function __toString()
    {
        return $this->name; 
    }
    
    /*=========================================================================*/    
    
    
    /*  ROLE EVENT USER ASSOCIATIVE TABLE */    

    
    /**
     * @ORM\OneToMany(targetEntity="EventUser", mappedBy="role", cascade={"all"})
     * */
    protected $eu;
    
     /*=========================================================================*/    
    
    
    /*  ROLE PROJECT USER ASSOCIATIVE TABLE */    

    
    /**
     * @ORM\OneToMany(targetEntity="ProjectUser", mappedBy="role", cascade={"all"})
     * */
    protected $pu;
}