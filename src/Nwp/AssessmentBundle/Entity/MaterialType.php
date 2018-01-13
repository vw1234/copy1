<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Nwp\AssessmentBundle\Entity\MaterialType
 *
 * @ORM\Table(name="material_type")
 * @ORM\Entity
 */
class MaterialType
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
     * @var MaterialCategory
     *
     * @ORM\ManyToOne(targetEntity="MaterialCategory")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="material_category_id", referencedColumnName="id")
     * })
     */
    private $materialCategory;


}
