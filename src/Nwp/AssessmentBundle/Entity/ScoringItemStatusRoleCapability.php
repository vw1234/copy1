<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ScoringItemStatusRoleCapability
 *
 * @ORM\Table(name="scoring_item_status_role_capability")
 * @ORM\Entity
 */
class ScoringItemStatusRoleCapability
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
     * @var \Component
     *
     * @ORM\ManyToOne(targetEntity="Component")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="component_id", referencedColumnName="id")
     * })
     */
    private $component;

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
     * @var \Role
     *
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     * })
     */
    private $role;

    /**
     * @var \ScoringItemStatus
     *
     * @ORM\ManyToOne(targetEntity="ScoringItemStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * })
     */
    private $status;

    /**
     * @var \Subrole
     *
     * @ORM\ManyToOne(targetEntity="Subrole")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="subrole_id", referencedColumnName="id")
     * })
     */
    private $subrole;
    
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
     * Set action
     *
     * @param \Nwp\AssessmentBundle\Entity\SystemAction $action
     * @return ScoringItemStatusRoleCapability
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
     * Set component
     *
     * @param \Nwp\AssessmentBundle\Entity\Component $component
     * @return ScoringItemStatusRoleCapability
     */
    public function setComponent(\Nwp\AssessmentBundle\Entity\Component $component = null)
    {
        $this->role = $component;
    
        return $this;
    }

    /**
     * Get component
     *
     * @return \Nwp\AssessmentBundle\Entity\Component 
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * Set role
     *
     * @param \Nwp\AssessmentBundle\Entity\Role $role
     * @return ScoringItemStatusRoleCapability
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
     * Set status
     *
     * @param \Nwp\AssessmentBundle\Entity\ScoringItemStatus $status
     * @return ScoringItemStatusRoleCapability
     */
    public function setStatus(\Nwp\AssessmentBundle\Entity\ScoringItemStatus $status = null)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return \Nwp\AssessmentBundle\Entity\ScoringItemStatus 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set subrole
     *
     * @param \Nwp\AssessmentBundle\Entity\Subrole $subrole
     * @return ScoringItemStatusRoleCapability
     */
    public function setSubrole(\Nwp\AssessmentBundle\Entity\Subrole $subrole = null)
    {
        $this->subrole = $subrole;
    
        return $this;
    }

    /**
     * Get subrole
     *
     * @return \Nwp\AssessmentBundle\Entity\Subrole 
     */
    public function getSubrole()
    {
        return $this->subrole;
    }
    
    /**
     * Set subrole
     *
     * @param \Nwp\AssessmentBundle\Entity\Structure $structure
     * @return ScoringItemStatusRoleCapability
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