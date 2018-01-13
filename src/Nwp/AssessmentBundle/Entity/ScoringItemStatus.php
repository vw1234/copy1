<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ScoringItemStatus
 *
 * @ORM\Table(name="scoring_item_status")
 * @ORM\Entity
 */
class ScoringItemStatus
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
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="action_name", type="string", length=50, nullable=true)
     */
    private $actionName;
    
     /**
     * @var integer
     *
     * @ORM\Column(name="order_id", type="integer", nullable=true)
     */
    private $orderId;
    
      /**
     * @var boolean
     *
     * @ORM\Column(name="is_review", type="boolean", nullable=false)
     */
    private $isReview;
    
      /**
     * @var boolean
     *
     * @ORM\Column(name="is_alert", type="boolean", nullable=false)
     */
    private $isAlert;

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
     * @return ScoringItemStatus
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
     * @param string $actionName
     * @return ScoringItemStatus
     */
    public function setActionName($actionName)
    {
        $this->actionName = $actionName;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getActionName()
    {
        return $this->actionName;
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
     * Set isReview
     *
     * @param boolean $isReview
     * @return ScoringItemStatus
     */
    public function setIsReview($isReview)
    {
        $this->isReview = $isReview;
    
        return $this;
    }

    /**
     * Get isReview
     *
     * @return boolean 
     */
    public function getIsReview()
    {
        return $this->isReview;
    }
    
    /**
     * Set isAlert
     *
     * @param boolean $isAlert
     * @return ScoringItemStatus
     */
    public function setIsAlert($isAlert)
    {
        $this->isAlert = $isAlert;
    
        return $this;
    }

    /**
     * Get isAlert
     *
     * @return boolean 
     */
    public function getIsAlert()
    {
        return $this->isAlert;
    }
    
     public function __toString()
    {
        return $this->name;
    }
}