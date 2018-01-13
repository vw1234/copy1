<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Nwp\AssessmentBundle\Entity\Material
 *
 * @ORM\Table(name="material")
 * @ORM\Entity
 */
class Material
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
     * @ORM\Column(name="name", type="string", length=45, nullable=false)
     */
    private $name;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Event", mappedBy="material")
     */
    private $event;

    /**
     * @var GradeLevel
     *
     * @ORM\ManyToOne(targetEntity="GradeLevel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grade_level_id", referencedColumnName="id")
     * })
     */
    private $gradeLevel;

    /**
     * @var MaterialType
     *
     * @ORM\ManyToOne(targetEntity="MaterialType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="material_type_id", referencedColumnName="id")
     * })
     */
    private $materialType;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->event = new \Doctrine\Common\Collections\ArrayCollection();
    }
    

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
     * @return Material
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
     * Add event
     *
     * @param Nwp\AssessmentBundle\Entity\Event $event
     * @return Material
     */
    public function addEvent(\Nwp\AssessmentBundle\Entity\Event $event)
    {
        $this->event[] = $event;
    
        return $this;
    }

    /**
     * Remove event
     *
     * @param Nwp\AssessmentBundle\Entity\Event $event
     */
    public function removeEvent(\Nwp\AssessmentBundle\Entity\Event $event)
    {
        $this->event->removeElement($event);
    }

    /**
     * Get event
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set gradeLevel
     *
     * @param Nwp\AssessmentBundle\Entity\GradeLevel $gradeLevel
     * @return Material
     */
    public function setGradeLevel(\Nwp\AssessmentBundle\Entity\GradeLevel $gradeLevel = null)
    {
        $this->gradeLevel = $gradeLevel;
    
        return $this;
    }

    /**
     * Get gradeLevel
     *
     * @return Nwp\AssessmentBundle\Entity\GradeLevel 
     */
    public function getGradeLevel()
    {
        return $this->gradeLevel;
    }

    /**
     * Set materialType
     *
     * @param Nwp\AssessmentBundle\Entity\MaterialType $materialType
     * @return Material
     */
    public function setMaterialType(\Nwp\AssessmentBundle\Entity\MaterialType $materialType = null)
    {
        $this->materialType = $materialType;
    
        return $this;
    }

    /**
     * Get materialType
     *
     * @return Nwp\AssessmentBundle\Entity\MaterialType 
     */
    public function getMaterialType()
    {
        return $this->materialType;
    }
}