<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Nwp\AssessmentBundle\Entity\RolegroupRole
 *
 * @ORM\Table(name="rolegroup_role")
 * @ORM\Entity
 */
class RolegroupRole
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
     * @var Rolegroup
     *
     * @ORM\ManyToOne(targetEntity="Rolegroup")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="rolegroup_id", referencedColumnName="id")
     * })
     */
    private $rolegroup;

    /**
     * @var Role
     *
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     * })
     */
    private $role;

 
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
     * Set rolegroup
     *
     * @param Nwp\AssessmentBundle\Entity\Rolegroup $rolegroup
     * @return Rolegroup
     */
    public function setRolegroup(\Nwp\AssessmentBundle\Entity\Rolegroup $rolegroup = null)
    {
        $this->rolegroup = $rolegroup;
    
        return $this;
    }

    /**
     * Get rolegroup
     *
     * @return Nwp\AssessmentBundle\Entity\Rolegroup 
     */
    public function getRolegroup()
    {
        return $this->rolegroup;
    }

    
    /**
     * Set role
     *
     * @param Nwp\AssessmentBundle\Entity\Role $role
     * @return ProjectUser
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
    
   
    
    public function __toString()
    {
        return $this->rolename->getName(); 
    }
    
   
}