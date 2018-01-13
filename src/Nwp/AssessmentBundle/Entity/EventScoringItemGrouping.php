<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Nwp\AssessmentBundle\Entity\EventScoringItemGrouping
 *
 * @ORM\Table(name="event_scoring_item_grouping")
 * @ORM\Entity
 */
class EventScoringItemGrouping
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
     * @var EventScoringItem
     *
     * @ORM\ManyToOne(targetEntity="EventScoringItem")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_scoring_item_id", referencedColumnName="id")
     * })
     */
    private $eventScoringItem;
    
    /**
     * @var Grouping
     *
     * @ORM\ManyToOne(targetEntity="Grouping")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grouping_id", referencedColumnName="id")
     * })
     */
    private $Grouping;


}
