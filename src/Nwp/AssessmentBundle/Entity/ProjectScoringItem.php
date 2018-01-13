<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProjectScoringItem
 *
 * @ORM\Table(name="project_scoring_item")
 * @ORM\Entity
 */
class ProjectScoringItem
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
     * @var \Project
     *
     * @ORM\ManyToOne(targetEntity="Project")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     * })
     */
    private $project;

    /**
     * @var \ScoringItem
     *
     * @ORM\ManyToOne(targetEntity="ScoringItem")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="scoring_item_id", referencedColumnName="id")
     * })
     */
    private $scoringItem;

    /**
     * @var \GradeLevel
     *
     * @ORM\ManyToOne(targetEntity="GradeLevel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grade_level_id", referencedColumnName="id")
     * })
     */
    private $gradeLevel;


}
