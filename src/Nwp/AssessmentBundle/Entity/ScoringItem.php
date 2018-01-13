<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * ScoringItem
 *
 * @ORM\Table(name="scoring_item")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class ScoringItem
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
     * @ORM\Column(name="student_id", type="integer", nullable=false)
     */
    private $studentId;

    /**
     * @var string
     *
     * @ORM\Column(name="nces_id", type="string", length=12, nullable=true)
     * @Assert\Length(min = "12",max = "12", minMessage = "Nces Id must consist of 12 characters", maxMessage = "Nces Id must consist of 12 characters" )
     */
    private $ncesId;

    /**
     * @var string
     *
     * @ORM\Column(name="ps_id", type="string", length=8, nullable=true)
     * @Assert\Length(min = "8",max = "8", minMessage = "Ps Id must consist of 8 characters", maxMessage = "Ps Id must consist of 8 characters")
     */
    private $psId;

    /**
     * @var integer
     *
     * @ORM\Column(name="district_id", type="integer", nullable=true)
     */
    private $districtId;

    /**
     * @var integer
     *
     * @ORM\Column(name="ipeds_id", type="integer", nullable=true)
     */
    private $ipedsId;

    /**
     * @var string
     *
     * @ORM\Column(name="organization_name", type="string", length=255, nullable=true)
     */
    private $organizationName;

    /**
     * @var string
     *
     * @ORM\Column(name="classroom_id", type="string", length=15, nullable=true)
     */
    private $classroomId;

    /**
     * @var string
     *
     * @ORM\Column(name="teacher_id", type="string", length=15, nullable=true)
     */
    private $teacherId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_uploaded", type="datetime", nullable=false)
     */
    private $dateUploaded;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_to_remove", type="datetime", nullable=false)
     */
    private $dateToRemove;

    /**
     * @var \AdministrationTime
     *
     * @ORM\ManyToOne(targetEntity="AdministrationTime")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="administration_time_id", referencedColumnName="id")
     * })
     */
    private $administrationTime;

    /**
     * @var \Year
     *
     * @ORM\ManyToOne(targetEntity="Year")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="year_id", referencedColumnName="id")
     * })
     */
    private $year;

    /**
     * @var \Prompt
     *
     * @ORM\ManyToOne(targetEntity="Prompt")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prompt_id", referencedColumnName="id")
     * })
     */
    private $prompt;

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
     * @var \GradeLevel
     *
     * @ORM\ManyToOne(targetEntity="GradeLevel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grade_level_id", referencedColumnName="id")
     * })
     */
    private $gradeLevel;

    /**
     * @var \OrganizationType
     *
     * @ORM\ManyToOne(targetEntity="OrganizationType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="organization_type_id", referencedColumnName="id")
     * })
     */
    private $organizationType;

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
     * @var \State
     *
     * @ORM\ManyToOne(targetEntity="State")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="state_id", referencedColumnName="id")
     * })
     */
    private $state;

    /**
     * @var \FosUserUser
     *
     * @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;



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
     * Set studentId
     *
     * @param integer $studentId
     * @return ScoringItem
     */
    public function setStudentId($studentId)
    {
        $this->studentId = $studentId;
    
        return $this;
    }

    /**
     * Get studentId
     *
     * @return integer 
     */
    public function getStudentId()
    {
        return $this->studentId;
    }

    /**
     * Set ncesId
     *
     * @param string $ncesId
     * @return ScoringItem
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
     * @return ScoringItem
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
     * Set districtId
     *
     * @param integer $districtId
     * @return ScoringItem
     */
    public function setDistrictId($districtId)
    {
        $this->districtId = $districtId;
    
        return $this;
    }

    /**
     * Get districtId
     *
     * @return integer 
     */
    public function getDistrictId()
    {
        return $this->districtId;
    }

    /**
     * Set ipedsId
     *
     * @param integer $ipedsId
     * @return ScoringItem
     */
    public function setIpedsId($ipedsId)
    {
        $this->ipedsId = $ipedsId;
    
        return $this;
    }

    /**
     * Get ipedsId
     *
     * @return integer 
     */
    public function getIpedsId()
    {
        return $this->ipedsId;
    }

    /**
     * Set organizationName
     *
     * @param string $organizationName
     * @return ScoringItem
     */
    public function setOrganizationName($organizationName)
    {
        $this->organizationName = $organizationName;
    
        return $this;
    }

    /**
     * Get organizationName
     *
     * @return string 
     */
    public function getOrganizationName()
    {
        return $this->organizationName;
    }

    /**
     * Set classroomId
     *
     * @param string $classroomId
     * @return ScoringItem
     */
    public function setClassroomId($classroomId)
    {
        $this->classroomId = $classroomId;
    
        return $this;
    }

    /**
     * Get classroomId
     *
     * @return string 
     */
    public function getClassroomId()
    {
        return $this->classroomId;
    }

    /**
     * Set teacherId
     *
     * @param string $teacherId
     * @return ScoringItem
     */
    public function setTeacherId($teacherId)
    {
        $this->teacherId = $teacherId;
    
        return $this;
    }

    /**
     * Get teacherId
     *
     * @return string 
     */
    public function getTeacherId()
    {
        return $this->teacherId;
    }

    /**
     * Set originalFileName
     *
     * @param string $originalFileName
     * @return ScoringItem
     */
    public function setOriginalFileName($originalFileName)
    {
        $this->originalFileName = $originalFileName;
    
        return $this;
    }

    /**
     * Get originalFileName
     *
     * @return string 
     */
    public function getOriginalFileName()
    {
        return $this->originalFileName;
    }
    
    /**
     * Set fileId
     *
     * @param integer $fileId
     * @return ScoringItem
     */
    public function setFileId($fileId)
    {
        $this->fileId = $fileId;
        
        return $this;
    }

    /**
     * Get fileId
     *
     * @return integer 
     */
    public function getFileId()
    {
        return $this->fileId;
    }

    /**
     * Set fileType
     *
     * @param string $fileType
     * @return ScoringItem
     */
    public function setFileType($fileType)
    {
        $this->fileType =  $fileType;
    
        return $this;
    }

    /**
     * Get fileType
     *
     * @return string 
     */
    public function getFileType()
    {
        return $this->fileType;
    }

    /**
     * Set dateUploaded
     *
     * @param \DateTime $dateUploaded
     * @return ScoringItem
     */
    public function setDateUploaded($dateUploaded)
    {
        $this->dateUploaded = $dateUploaded;
    
        return $this;
    }

    /**
     * Get dateUploaded
     *
     * @return \DateTime 
     */
    public function getDateUploaded()
    {
        return $this->dateUploaded;
    }
    
    /**
     * Set dateToRemove
     *
     * @param \DateTime $dateToRemove
     * @return ScoringItem
     */
    public function setDateToRemove($dateToRemove)
    {
        $this->dateToRemove = $dateToRemove;
    
        return $this;
    }

    /**
     * Get dateToRemove
     *
     * @return \DateTime 
     */
    public function getDateToRemove()
    {
        return $this->dateToRemove;
    }

    /**
     * Set administrationTime
     *
     * @param \Nwp\AssessmentBundle\Entity\AdministrationTime $administrationTime
     * @return ScoringItem
     */
    public function setAdministrationTime(\Nwp\AssessmentBundle\Entity\AdministrationTime $administrationTime = null)
    {
        $this->administrationTime = $administrationTime;
    
        return $this;
    }

    /**
     * Get administrationTime
     *
     * @return \Nwp\AssessmentBundle\Entity\AdministrationTime 
     */
    public function getAdministrationTime()
    {
        return $this->administrationTime;
    }

    /**
     * Set year
     *
     * @param \Nwp\AssessmentBundle\Entity\Year $year
     * @return ScoringItem
     */
    public function setYear(\Nwp\AssessmentBundle\Entity\Year $year = null)
    {
        $this->year = $year;
    
        return $this;
    }

    /**
     * Get year
     *
     * @return \Nwp\AssessmentBundle\Entity\Year 
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set prompt
     *
     * @param \Nwp\AssessmentBundle\Entity\Prompt $prompt
     * @return ScoringItem
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
     * Set project
     *
     * @param \Nwp\AssessmentBundle\Entity\Project $project
     * @return ScoringItem
     */
    public function setProject(\Nwp\AssessmentBundle\Entity\Project $project = null)
    {
        $this->project = $project;
    
        return $this;
    }

    /**
     * Get project
     *
     * @return \Nwp\AssessmentBundle\Entity\Project 
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set gradeLevel
     *
     * @param \Nwp\AssessmentBundle\Entity\GradeLevel $gradeLevel
     * @return ScoringItem
     */
    public function setGradeLevel(\Nwp\AssessmentBundle\Entity\GradeLevel $gradeLevel = null)
    {
        $this->gradeLevel = $gradeLevel;
    
        return $this;
    }

    /**
     * Get gradeLevel
     *
     * @return \Nwp\AssessmentBundle\Entity\GradeLevel 
     */
    public function getGradeLevel()
    {
        return $this->gradeLevel;
    }

    /**
     * Set organizationType
     *
     * @param \Nwp\AssessmentBundle\Entity\OrganizationType $organizationType
     * @return ScoringItem
     */
    public function setOrganizationType(\Nwp\AssessmentBundle\Entity\OrganizationType $organizationType = null)
    {
        $this->organizationType = $organizationType;
    
        return $this;
    }

    /**
     * Get organizationType
     *
     * @return \Nwp\AssessmentBundle\Entity\OrganizationType 
     */
    public function getOrganizationType()
    {
        return $this->organizationType;
    }

    /**
     * Set county
     *
     * @param \Nwp\AssessmentBundle\Entity\County $county
     * @return ScoringItem
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
     * Set state
     *
     * @param \Nwp\AssessmentBundle\Entity\State $state
     * @return ScoringItem
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

    /**
     * Set user
     *
     * @param \Application\Sonata\UserBundle\Entity\User $user
     * @return ScoringItem
     */
    public function setUser(\Application\Sonata\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Application\Sonata\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
    
        
    /*=========================================================================*/    
    
    /* FILE UPLOAD
    
     /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    
    /**
     * @Assert\File(maxSize="2097152",mimeTypes = {"application/pdf", "application/x-pdf"},mimeTypesMessage = "Please upload a valid PDF file",maxSizeMessage="The file is too large ({{ size }}). Allowed maximum size is {{ limit }}",uploadErrorMessage="The file could not be uploaded")
     */
    public $file;

    // a property used temporarily while deleting
    private $filenameForRemove;
    
    /**
     * @var string
     *
     * @ORM\Column(name="original_file_name", type="string", length=100, nullable=true)
     */
    private $originalFileName;
    
     /**
     * @var integer
     *
     * @ORM\Column(name="file_id", type="integer", nullable=true)
     */
    public $fileId;
    
    /**
     * @var string
     *
     * @ORM\Column(name="file_type", type="string", length=4, nullable=true)
     */
    private $fileType;
    

    public function getAbsolutePath()
    {
        return null === $this->originalFileName
            ? null
            : $this->getUploadRootDir().'/'.$this->id.'.'.$this->fileType;
    }


    public function getWebPath()
    {
        return null === $this->originalFileName
            ? null
            : $this->getUploadDir().'/'.$this->id.".".$this->fileType;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../../app/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/papers';
    }


    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
    */
    
    public function preUpload()
    {
        if (isset($this->file)) {
            if (null !== $this->file) {
                $this->originalFileName = pathinfo($this->file->getClientOriginalName())['filename'];
                $this->fileId = $this->id;
            
                if (!pathinfo($this->file->getClientOriginalName())['extension'] ) {
                    $this->fileType = $this->file->guessExtension();
                } else {
                    $this->fileType = pathinfo($this->file->getClientOriginalName())['extension']; 
                }  
            }
        }
        
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (isset($this->file)) {
            if (null === $this->file) {
                return;
            }

            // you must throw an exception here if the file cannot be moved
            // so that the entity is not persisted to the database
            // which the UploadedFile move() method does
            $this->file->move(
                $this->getUploadRootDir(),
                $this->id.'.'.$this->file->guessExtension()
            );

            unset($this->file);
        }  
    }
    
    /**
     * @ORM\PreRemove()
     */
    public function storeFilenameForRemove()
    {
        $this->filenameForRemove = $this->getAbsolutePath();
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($this->filenameForRemove) {
            unlink($this->filenameForRemove);
        }
    }
    
    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }
    
    /*=========================================================================*/  
    
     public function __toString()
    {
        return (string) $this->id; 
    }
}