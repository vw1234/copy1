<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NwpSchoolType
 *
 * @ORM\Table(name="nwp_school_type")
 * @ORM\Entity
 */
class NwpSchoolType
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
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    private $name;


}
