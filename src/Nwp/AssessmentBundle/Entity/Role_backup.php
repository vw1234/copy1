<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use NWP\AssessmentBundle\Entity\ProjectUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="role")
 * @ORM\HasLifecycleCallbacks()
 */
class RoleBackup
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
    
     
}