<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Nwp\AssessmentBundle\Entity\EventGradeLevelBlockPrompt
 *
 * @ORM\Table(name="event_grade_level_block_prompt")
 * @ORM\Entity
 */
class EventGradeLevelBlockPrompt
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
     * @var \EventGradeLevelBlock
     *
     * @ORM\ManyToOne(targetEntity="EventGradeLevelBlock", inversedBy="pu")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_grade_level_block_id", referencedColumnName="id")
     * })
     */
    private $eventGradeLevelBlock;

    /**
     * @var integer $tableId
     *
     * @ORM\Column(name="table_id", type="integer", nullable=true)
     */
    protected $tableId;

     /**
     * @var \Prompt
     *
     * @ORM\ManyToOne(targetEntity="Prompt", inversedBy="pu")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prompt_id", referencedColumnName="id")
     * })
     */
    protected $prompt;

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
     * Set tableId
     *
     * @param integer $tableId
     * @return EventGradeLevelBlockPrompt
     */
    public function setTableId($tableId)
    {
        $this->tableId = $tableId;
    
        return $this;
    }

    /**
     * Get tableId
     *
     * @return integer 
     */
    public function getTableId()
    {
        return $this->tableId;
    }
    
    /**
     * Set prompt
     *
     * @param \Nwp\AssessmentBundle\Entity\Prompt $prompt
     * @return EventGradeLevelBlockPrompt
     */
    public function setPrompt(\Nwp\AssessmentBundle\Entity\Prompt $prompt = null)
    {
        $this->prompt = $prompt;
    
        return $this;
    }

    /**
     * Get prompt
     *
     * @return \Nwp\AssessmentBundle\Entity\Prompt 
     */
    public function getPrompt()
    {
        return $this->prompt;
    }
    
     /**
     * Set eventGradeLevelBlock
     *
     * @param \Nwp\AssessmentBundle\Entity\EventGradeLevelBlock $eventGradeLevelBlock
     * @return EventGradeLevelBlockPrompt
     */
    public function setEventGradeLevelBlock(\Nwp\AssessmentBundle\Entity\EventGradeLevelBlock $eventGradeLevelBlock = null)
    {
        $this->eventGradeLevelBlock = $eventGradeLevelBlock;
    
        return $this;
    }

    /**
     * Get eventGradeLevelBlock
     *
     * @return \Nwp\AssessmentBundle\Entity\EventGradeLevelBlock 
     */
    public function getEventGradeLevelBlock()
    {
        return $this->eventGradeLevelBlock;
    }
    
    public function __toString()
    {
	if ($this->getId()) {
        	return $this->getEventGradeLevelBlock()->getEvent()->getName().", ".$this->getEventGradeLevelBlock()->getGradeLevel()->getName().", ".$this->getEventGradeLevelBlock()->getBlockId(); 
	} else {
        	return '';
        }    
}

   

}
