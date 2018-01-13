<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Prompt
 *
 * @ORM\Table(name="prompt")
 * @ORM\Entity
 */
class Prompt
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
     * @ORM\Column(name="name", type="string", length=300, nullable=false)
     */
    private $name;

    /**
     * @var \Project
     *
     * @ORM\ManyToOne(targetEntity="Project")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     * })
     */
    private $project;



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
     * @return Prompt
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
     * Set project
     *
     * @param \Nwp\AssessmentBundle\Entity\Project $project
     * @return Prompt
     */
    public function setProject(\Nwp\AssessmentBundle\Entity\Project $project = null)
    {
        $this->project = $project;
    
        return $this;
    }

    /**
     * Get project
     *
     * @return \Nwp\AssessmentBundle\Entity\Project 
     */
    public function getProject()
    {
        return $this->project;
    }
    
    public function __toString()
    {
        return $this->name; 
    }
}