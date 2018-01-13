<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Nwp\AssessmentBundle\Entity\ProjectUser;

/**
 * Nwp\AssessmentBundle\Entity\Project
 *
 * @ORM\Table(name="project")
 * @ORM\Entity(repositoryClass="Nwp\AssessmentBundle\Entity\ProjectRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Project
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
     * @var \DateTime $startDate
     *
     * @ORM\Column(name="start_date", type="date", nullable=true)
     */
    private $startDate;

    /**
     * @var \DateTime $endDate
     *
     * @ORM\Column(name="end_date", type="date", nullable=true)
     */
    private $endDate;
    


/*=========================================================================*/    
    
    
/* PROJECT USER ASSOCIATIVE TABLE */    

    /**
     *
     * @ORM\OneToMany(targetEntity="ProjectUser", mappedBy="project", cascade={"all"}, orphanRemoval=true)
     */
    protected $pu;

    
    protected $users;
    
    

    public function __construct()
    {
        
        $this->pu = new ArrayCollection();
        $this->users = new ArrayCollection();
              
    }

    
 
    
   
    public function getPu()
    {
        return $this->pu;
    }

    
    public function setPu($pu)
    {
        $this->pu = $pu;
    }
        
    
    public function getUser()
    {
        $users = new ArrayCollection();
        
        foreach($this->pu as $p)
        {
            $users[] = $p->getUser();
        }

        return $users;
    }
  
    
    public function setUser($users)
    {
        foreach($users as $u)
        {
            $pu = new ProjectUser();

            $pu->setProject($this);
            $pu->setUser($u);
            $pu->setRole($u);

            $this->addPu($pu);
        }
    }
    
    
    public function getProject()
    {
        return $this;
    }

    
    
    public function addPu($ProjectUser)
    {
        $this->pu[] = $ProjectUser;
    }
    
    
    
    public function removePu($ProjectUser)
    {
        return $this->pu->removeElement($ProjectUser);
    }
  
   
    
    
/*=========================================================================*/    
    

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
     * @return Project
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
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Project
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    
        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return Project
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    
        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }
    
    public function __toString()
    {
       if ($this->getId()) {
       		 return $this->name;
       } else {
       		return '';
       } 
    }
}
