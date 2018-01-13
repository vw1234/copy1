<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ScoringItemScore
 *
 * @ORM\Table(name="scoring_item_score")
 * @ORM\Entity
 */
class ScoringItemScore
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
     * @var integer
     *
     * @ORM\Column(name="score", type="integer", nullable=true)
     */
    private $score;

    /**
     * @var \EventScoringItemStatus
     *
     * @ORM\ManyToOne(targetEntity="EventScoringItemStatus",inversedBy="scores")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_scoring_item_status_id", referencedColumnName="id")
     * })
     */
    private $eventScoringItemStatus;

    /**
     * @var \ScoringRubricAttribute
     *
     * @ORM\ManyToOne(targetEntity="ScoringRubricAttribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="scoring_rubric_attribute_id", referencedColumnName="id")
     * })
     */
    private $scoringRubricAttribute;
    
     /**
     * @var string $comment
     *
     * @ORM\Column(name="comment", type="string", length=2000, nullable=true)
     */
    private $comment;
    
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
     * Set score
     *
     * @param integer $score
     * @return ScoringItemScore
     */
    public function setScore($score)
    {
        $this->score = $score;
    
        return $this;
    }

    /**
     * Get score
     *
     * @return integer 
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set eventScoringItemStatus
     *
     * @param \Nwp\AssessmentBundle\Entity\EventScoringItemStatus $eventScoringItemStatus
     * @return ScoringItemScore
     */
    public function setEventScoringItemStatus(\Nwp\AssessmentBundle\Entity\EventScoringItemStatus $eventScoringItemStatus = null)
    {
        $this->eventScoringItemStatus = $eventScoringItemStatus;
    
        return $this;
    }

    /**
     * Get eventScoringItemStatus
     *
     * @return \Nwp\AssessmentBundle\Entity\EventScoringItemStatus 
     */
    public function getEventScoringItemStatus()
    {
        return $this->eventScoringItemStatus;
    }

    /**
     * Set scoringRubricAttribute
     *
     * @param \Nwp\AssessmentBundle\Entity\ScoringRubricAttribute $scoringRubricAttribute
     * @return ScoringItemScore
     */
    public function setScoringRubricAttribute(\Nwp\AssessmentBundle\Entity\ScoringRubricAttribute $scoringRubricAttribute = null)
    {
        $this->scoringRubricAttribute = $scoringRubricAttribute;
    
        return $this;
    }

    /**
     * Get scoringRubricAttribute
     *
     * @return \Nwp\AssessmentBundle\Entity\ScoringRubricAttribute 
     */
    public function getScoringRubricAttribute()
    {
        return $this->scoringRubricAttribute;
    }
    
    /**
     * Set comment
     *
     * @param string $comment
     * @return Comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    
        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }
    
   
}