<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ScoringItemStatusPathway
 *
 * @ORM\Table(name="scoring_item_status_pathway")
 * @ORM\Entity
 */
class ScoringItemStatusPathway
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
     * @var \ScoringItemStatus
     *
     * @ORM\ManyToOne(targetEntity="ScoringItemStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pathway_id", referencedColumnName="id")
     * })
     */
    private $pathway;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set pathway
     *
     * @param \Nwp\AssessmentBundle\Entity\ScoringItemStatus $pathway
     * @return ScoringItemStatusPathway
     */
    public function setPathway(\Nwp\AssessmentBundle\Entity\ScoringItemStatus $pathway = null)
    {
        $this->pathway = $pathway;
    
        return $this;
    }

    /**
     * Get pathway
     *
     * @return \Nwp\AssessmentBundle\Entity\ScoringItemStatus 
     */
    public function getPathway()
    {
        return $this->pathway;
    }

    /**
     * Set role
     *
     * @param \Nwp\AssessmentBundle\Entity\Role $role
     * @return ScoringItemStatusPathway
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
     * @return ScoringItemStatusPathway
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
}