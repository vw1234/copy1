<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ScoringRubricAttribute
 *
 * @ORM\Table(name="scoring_rubric_attribute")
 * @ORM\Entity
 */
class ScoringRubricAttribute
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
     * @var \ScoringRubric
     *
     * @ORM\ManyToOne(targetEntity="ScoringRubric")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="rubric_id", referencedColumnName="id")
     * })
     */
    private $rubric;

    /**
     * @var \ScoringAttribute
     *
     * @ORM\ManyToOne(targetEntity="ScoringAttribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="attribute_id", referencedColumnName="id")
     * })
     */
    private $attribute;



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
     * Set rubric
     *
     * @param \Nwp\AssessmentBundle\Entity\ScoringRubric $rubric
     * @return ScoringRubricAttribute
     */
    public function setRubric(\Nwp\AssessmentBundle\Entity\ScoringRubric $rubric = null)
    {
        $this->rubric = $rubric;
    
        return $this;
    }

    /**
     * Get rubric
     *
     * @return \Nwp\AssessmentBundle\Entity\ScoringRubric 
     */
    public function getRubric()
    {
        return $this->rubric;
    }

    /**
     * Set attribute
     *
     * @param \Nwp\AssessmentBundle\Entity\ScoringAttribute $attribute
     * @return ScoringRubricAttribute
     */
    public function setAttribute(\Nwp\AssessmentBundle\Entity\ScoringAttribute $attribute = null)
    {
        $this->attribute = $attribute;
    
        return $this;
    }

    /**
     * Get attribute
     *
     * @return \Nwp\AssessmentBundle\Entity\ScoringAttribute 
     */
    public function getAttribute()
    {
        return $this->attribute;
    }
    public function __toString()
    {
        return $this->attribute->getName(); 
    }
}