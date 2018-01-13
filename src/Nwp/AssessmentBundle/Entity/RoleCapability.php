<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoleCapability
 *
 * @ORM\Table(name="role_capability")
 * @ORM\Entity
 */
class RoleCapability
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
     * @var \Role
     *
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     * })
     */
    private $role;

    /**
     * @var \SystemEntity
     *
     * @ORM\ManyToOne(targetEntity="SystemEntity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entity_id", referencedColumnName="id")
     * })
     */
    private $entity;

    /**
     * @var \SystemAction
     *
     * @ORM\ManyToOne(targetEntity="SystemAction")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="action_id", referencedColumnName="id")
     * })
     */
    private $action;

    /**
     * @var \Structure
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
     * Set role
     *
     * @param \Nwp\AssessmentBundle\Entity\Role $role
     * @return RoleCapability
     */
    public function setRole(\Nwp\AssessmentBundle\Entity\Role $role = null)
    {
        $this->role = $role;
    
        return $this;
    }

    /**
     * Get role
     *
     * @return \Nwp\AssessmentBundle\Entity\Role 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set entity
     *
     * @param \Nwp\AssessmentBundle\Entity\SystemEntity $entity
     * @return RoleCapability
     */
    public function setEntity(\Nwp\AssessmentBundle\Entity\SystemEntity $entity = null)
    {
        $this->entity = $entity;
    
        return $this;
    }

    /**
     * Get entity
     *
     * @return \Nwp\AssessmentBundle\Entity\SystemEntity 
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set action
     *
     * @param \Nwp\AssessmentBundle\Entity\SystemAction $action
     * @return RoleCapability
     */
    public function setAction(\Nwp\AssessmentBundle\Entity\SystemAction $action = null)
    {
        $this->action = $action;
    
        return $this;
    }

    /**
     * Get action
     *
     * @return \Nwp\AssessmentBundle\Entity\SystemAction 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set structure
     *
     * @param \Nwp\AssessmentBundle\Entity\Structure $structure
     * @return RoleCapability
     */
    public function setStructure(\Nwp\AssessmentBundle\Entity\Structure $structure = null)
    {
        $this->structure = $structure;
    
        return $this;
    }

    /**
     * Get structure
     *
     * @return \Nwp\AssessmentBundle\Entity\Structure 
     */
    public function getStructure()
    {
        return $this->structure;
    }
}