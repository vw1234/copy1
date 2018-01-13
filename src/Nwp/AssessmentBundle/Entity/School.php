<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * School
 *
 * @ORM\Table(name="school")
 * @ORM\Entity
 */
class School
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

    /**
     * @var string
     *
     * @ORM\Column(name="nces_id", type="string", length=12, nullable=true)
     */
    private $ncesId;

    /**
     * @var string
     *
     * @ORM\Column(name="ps_id", type="string", length=6, nullable=true)
     */
    private $psId;

    /**
     * @var string
     *
     * @ORM\Column(name="zip", type="string", length=12, nullable=true)
     */
    private $zip;

    /**
     * @var \County
     *
     * @ORM\ManyToOne(targetEntity="County")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="county_id", referencedColumnName="id")
     * })
     */
    private $county;

    /**
     * @var \District
     *
     * @ORM\ManyToOne(targetEntity="District")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="district_id", referencedColumnName="id")
     * })
     */
    private $district;

    /**
     * @var \NwpSchoolType
     *
     * @ORM\ManyToOne(targetEntity="NwpSchoolType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="nwp_school_type_id", referencedColumnName="id")
     * })
     */
    private $nwpSchoolType;

    /**
     * @var \State
     *
     * @ORM\ManyToOne(targetEntity="State")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="state_id", referencedColumnName="id")
     * })
     */
    private $state;



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
     * @return School
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
     * Set ncesId
     *
     * @param string $ncesId
     * @return School
     */
    public function setNcesId($ncesId)
    {
        $this->ncesId = $ncesId;
    
        return $this;
    }

    /**
     * Get ncesId
     *
     * @return string 
     */
    public function getNcesId()
    {
        return $this->ncesId;
    }

    /**
     * Set psId
     *
     * @param string $psId
     * @return School
     */
    public function setPsId($psId)
    {
        $this->psId = $psId;
    
        return $this;
    }

    /**
     * Get psId
     *
     * @return string 
     */
    public function getPsId()
    {
        return $this->psId;
    }

    /**
     * Set zip
     *
     * @param string $zip
     * @return School
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    
        return $this;
    }

    /**
     * Get zip
     *
     * @return string 
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set county
     *
     * @param \Nwp\AssessmentBundle\Entity\County $county
     * @return School
     */
    public function setCounty(\Nwp\AssessmentBundle\Entity\County $county = null)
    {
        $this->county = $county;
    
        return $this;
    }

    /**
     * Get county
     *
     * @return \Nwp\AssessmentBundle\Entity\County 
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * Set district
     *
     * @param \Nwp\AssessmentBundle\Entity\District $district
     * @return School
     */
    public function setDistrict(\Nwp\AssessmentBundle\Entity\District $district = null)
    {
        $this->district = $district;
    
        return $this;
    }

    /**
     * Get district
     *
     * @return \Nwp\AssessmentBundle\Entity\District 
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * Set nwpSchoolType
     *
     * @param \Nwp\AssessmentBundle\Entity\NwpSchoolType $nwpSchoolType
     * @return School
     */
    public function setNwpSchoolType(\Nwp\AssessmentBundle\Entity\NwpSchoolType $nwpSchoolType = null)
    {
        $this->nwpSchoolType = $nwpSchoolType;
    
        return $this;
    }

    /**
     * Get nwpSchoolType
     *
     * @return \Nwp\AssessmentBundle\Entity\NwpSchoolType 
     */
    public function getNwpSchoolType()
    {
        return $this->nwpSchoolType;
    }

    /**
     * Set state
     *
     * @param \Nwp\AssessmentBundle\Entity\State $state
     * @return School
     */
    public function setState(\Nwp\AssessmentBundle\Entity\State $state = null)
    {
        $this->state = $state;
    
        return $this;
    }

    /**
     * Get state
     *
     * @return \Nwp\AssessmentBundle\Entity\State 
     */
    public function getState()
    {
        return $this->state;
    }
    
    
}