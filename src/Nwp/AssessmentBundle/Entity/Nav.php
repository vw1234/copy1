<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Nav
 *
 * @ORM\Table(name="nav")
 * @ORM\Entity
 */
class Nav
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
     * @ORM\Column(name="name", type="string", length=30, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=100, nullable=true)
     */
    private $path;

    /**
     * @var integer
     *
     * @ORM\Column(name="level_id", type="integer", nullable=true)
     */
    private $levelId;

    /**
     * @var integer
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    private $parentId;

    /**
     * @var integer
     *
     * @ORM\Column(name="order_id", type="integer", nullable=true)
     */
    private $orderId;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     */
    private $isActive;

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
     * @var \SystemEntity
     *
     * @ORM\ManyToOne(targetEntity="SystemEntity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entity_id", referencedColumnName="id")
     * })
     */
    private $entity;

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
     * Set name
     *
     * @param string $name
     * @return Nav
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
     * Set path
     *
     * @param string $path
     * @return Nav
     */
    public function setPath($path)
    {
        $this->path = $path;
    
        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set levelId
     *
     * @param integer $levelId
     * @return Nav
     */
    public function setLevelId($levelId)
    {
        $this->levelId = $levelId;
    
        return $this;
    }

    /**
     * Get levelId
     *
     * @return integer 
     */
    public function getLevelId()
    {
        return $this->levelId;
    }

    /**
     * Set parentId
     *
     * @param integer $parentId
     * @return Nav
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    
        return $this;
    }

    /**
     * Get parentId
     *
     * @return integer 
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set orderId
     *
     * @param integer $orderId
     * @return Nav
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    
        return $this;
    }

    /**
     * Get orderId
     *
     * @return integer 
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Nav
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    
        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set action
     *
     * @param \Nwp\AssessmentBundle\Entity\SystemAction $action
     * @return Nav
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
     * Set entity
     *
     * @param \Nwp\AssessmentBundle\Entity\SystemEntity $entity
     * @return Nav
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
     * Set structure
     *
     * @param \Nwp\AssessmentBundle\Entity\Structure $structure
     * @return Nav
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