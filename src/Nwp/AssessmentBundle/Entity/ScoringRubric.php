<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ScoringRubric
 *
 * @ORM\Table(name="scoring_rubric")
 * @ORM\Entity
 */
class ScoringRubric
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
     * @ORM\Column(name="name", type="string", length=45, nullable=false)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="min_score", type="integer", nullable=false)
     */
    private $minScore;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_score", type="integer", nullable=false)
     */
    private $maxScore;



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
     * @return ScoringRubric
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
     * Set minScore
     *
     * @param integer $minScore
     * @return ScoringRubric
     */
    public function setMinScore($minScore)
    {
        $this->minScore = $minScore;
    
        return $this;
    }

    /**
     * Get minScore
     *
     * @return integer 
     */
    public function getMinScore()
    {
        return $this->minScore;
    }

    /**
     * Set maxScore
     *
     * @param integer $maxScore
     * @return ScoringRubric
     */
    public function setMaxScore($maxScore)
    {
        $this->maxScore = $maxScore;
    
        return $this;
    }

    /**
     * Get maxScore
     *
     * @return integer 
     */
    public function getMaxScore()
    {
        return $this->maxScore;
    }
    
     public function __toString()
    {
        return  $this->name; 
    }
}