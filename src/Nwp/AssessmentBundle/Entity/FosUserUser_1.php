<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Nwp\AssessmentBundle\Entity\FosUserUser
 *
 * @ORM\Table(name="fos_user_user")
 * @ORM\Entity
 */
class FosUserUser
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
     * @var string $username
     *
     * @ORM\Column(name="username", type="string", length=255, nullable=false)
     */
    private $username;

    /**
     * @var string $usernameCanonical
     *
     * @ORM\Column(name="username_canonical", type="string", length=255, nullable=false)
     */
    private $usernameCanonical;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var string $emailCanonical
     *
     * @ORM\Column(name="email_canonical", type="string", length=255, nullable=false)
     */
    private $emailCanonical;

    /**
     * @var boolean $enabled
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled;

    /**
     * @var string $salt
     *
     * @ORM\Column(name="salt", type="string", length=255, nullable=false)
     */
    private $salt;

    /**
     * @var string $password
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     */
    private $password;

    /**
     * @var \DateTime $lastLogin
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @var boolean $locked
     *
     * @ORM\Column(name="locked", type="boolean", nullable=false)
     */
    private $locked;

    /**
     * @var boolean $expired
     *
     * @ORM\Column(name="expired", type="boolean", nullable=false)
     */
    private $expired;

    /**
     * @var \DateTime $expiresAt
     *
     * @ORM\Column(name="expires_at", type="datetime", nullable=true)
     */
    private $expiresAt;

    /**
     * @var string $confirmationToken
     *
     * @ORM\Column(name="confirmation_token", type="string", length=255, nullable=true)
     */
    private $confirmationToken;

    /**
     * @var \DateTime $passwordRequestedAt
     *
     * @ORM\Column(name="password_requested_at", type="datetime", nullable=true)
     */
    private $passwordRequestedAt;

    /**
     * @var array $roles
     *
     * @ORM\Column(name="roles", type="array", nullable=false)
     */
    private $roles;

    /**
     * @var boolean $credentialsExpired
     *
     * @ORM\Column(name="credentials_expired", type="boolean", nullable=false)
     */
    private $credentialsExpired;

    /**
     * @var \DateTime $credentialsExpireAt
     *
     * @ORM\Column(name="credentials_expire_at", type="datetime", nullable=true)
     */
    private $credentialsExpireAt;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     */
    private $updatedAt;

    /**
     * @var \DateTime $dateOfBirth
     *
     * @ORM\Column(name="date_of_birth", type="datetime", nullable=true)
     */
    private $dateOfBirth;

    /**
     * @var string $firstname
     *
     * @ORM\Column(name="firstname", type="string", length=64, nullable=true)
     */
    private $firstname;

    /**
     * @var string $lastname
     *
     * @ORM\Column(name="lastname", type="string", length=64, nullable=true)
     */
    private $lastname;

    /**
     * @var string $website
     *
     * @ORM\Column(name="website", type="string", length=64, nullable=true)
     */
    private $website;

    /**
     * @var string $biography
     *
     * @ORM\Column(name="biography", type="string", length=255, nullable=true)
     */
    private $biography;

    /**
     * @var string $gender
     *
     * @ORM\Column(name="gender", type="string", length=1, nullable=true)
     */
    private $gender;

    /**
     * @var string $locale
     *
     * @ORM\Column(name="locale", type="string", length=8, nullable=true)
     */
    private $locale;

    /**
     * @var string $timezone
     *
     * @ORM\Column(name="timezone", type="string", length=64, nullable=true)
     */
    private $timezone;

    /**
     * @var string $phone
     *
     * @ORM\Column(name="phone", type="string", length=64, nullable=true)
     */
    private $phone;

    /**
     * @var string $facebookUid
     *
     * @ORM\Column(name="facebook_uid", type="string", length=255, nullable=true)
     */
    private $facebookUid;

    /**
     * @var string $facebookName
     *
     * @ORM\Column(name="facebook_name", type="string", length=255, nullable=true)
     */
    private $facebookName;

    /**
     * @var string $facebookData
     *
     * @ORM\Column(name="facebook_data", type="text", nullable=true)
     */
    private $facebookData;

    /**
     * @var string $twitterUid
     *
     * @ORM\Column(name="twitter_uid", type="string", length=255, nullable=true)
     */
    private $twitterUid;

    /**
     * @var string $twitterName
     *
     * @ORM\Column(name="twitter_name", type="string", length=255, nullable=true)
     */
    private $twitterName;

    /**
     * @var string $twitterData
     *
     * @ORM\Column(name="twitter_data", type="text", nullable=true)
     */
    private $twitterData;

    /**
     * @var string $gplusUid
     *
     * @ORM\Column(name="gplus_uid", type="string", length=255, nullable=true)
     */
    private $gplusUid;

    /**
     * @var string $gplusName
     *
     * @ORM\Column(name="gplus_name", type="string", length=255, nullable=true)
     */
    private $gplusName;

    /**
     * @var string $gplusData
     *
     * @ORM\Column(name="gplus_data", type="text", nullable=true)
     */
    private $gplusData;

    /**
     * @var string $token
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @var string $twoStepCode
     *
     * @ORM\Column(name="two_step_code", type="string", length=255, nullable=true)
     */
    private $twoStepCode;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="FosUserGroup", inversedBy="user")
     * @ORM\JoinTable(name="fos_user_user_group",
     *   joinColumns={
     *     @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     *   }
     * )
     */
    private $group;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->group = new \Doctrine\Common\Collections\ArrayCollection();
    }
    

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
     * Set username
     *
     * @param string $username
     * @return FosUserUser
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set usernameCanonical
     *
     * @param string $usernameCanonical
     * @return FosUserUser
     */
    public function setUsernameCanonical($usernameCanonical)
    {
        $this->usernameCanonical = $usernameCanonical;
    
        return $this;
    }

    /**
     * Get usernameCanonical
     *
     * @return string 
     */
    public function getUsernameCanonical()
    {
        return $this->usernameCanonical;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return FosUserUser
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set emailCanonical
     *
     * @param string $emailCanonical
     * @return FosUserUser
     */
    public function setEmailCanonical($emailCanonical)
    {
        $this->emailCanonical = $emailCanonical;
    
        return $this;
    }

    /**
     * Get emailCanonical
     *
     * @return string 
     */
    public function getEmailCanonical()
    {
        return $this->emailCanonical;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return FosUserUser
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    
        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return FosUserUser
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    
        return $this;
    }

    /**
     * Get salt
     *
     * @return string 
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return FosUserUser
     */
    public function setPassword($password)
    {
        $this->password = $password;
    
        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set lastLogin
     *
     * @param \DateTime $lastLogin
     * @return FosUserUser
     */
    public function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;
    
        return $this;
    }

    /**
     * Get lastLogin
     *
     * @return \DateTime 
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Set locked
     *
     * @param boolean $locked
     * @return FosUserUser
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
    
        return $this;
    }

    /**
     * Get locked
     *
     * @return boolean 
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * Set expired
     *
     * @param boolean $expired
     * @return FosUserUser
     */
    public function setExpired($expired)
    {
        $this->expired = $expired;
    
        return $this;
    }

    /**
     * Get expired
     *
     * @return boolean 
     */
    public function getExpired()
    {
        return $this->expired;
    }

    /**
     * Set expiresAt
     *
     * @param \DateTime $expiresAt
     * @return FosUserUser
     */
    public function setExpiresAt($expiresAt)
    {
        $this->expiresAt = $expiresAt;
    
        return $this;
    }

    /**
     * Get expiresAt
     *
     * @return \DateTime 
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * Set confirmationToken
     *
     * @param string $confirmationToken
     * @return FosUserUser
     */
    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;
    
        return $this;
    }

    /**
     * Get confirmationToken
     *
     * @return string 
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * Set passwordRequestedAt
     *
     * @param \DateTime $passwordRequestedAt
     * @return FosUserUser
     */
    public function setPasswordRequestedAt($passwordRequestedAt)
    {
        $this->passwordRequestedAt = $passwordRequestedAt;
    
        return $this;
    }

    /**
     * Get passwordRequestedAt
     *
     * @return \DateTime 
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    /**
     * Set roles
     *
     * @param array $roles
     * @return FosUserUser
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    
        return $this;
    }

    /**
     * Get roles
     *
     * @return array 
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set credentialsExpired
     *
     * @param boolean $credentialsExpired
     * @return FosUserUser
     */
    public function setCredentialsExpired($credentialsExpired)
    {
        $this->credentialsExpired = $credentialsExpired;
    
        return $this;
    }

    /**
     * Get credentialsExpired
     *
     * @return boolean 
     */
    public function getCredentialsExpired()
    {
        return $this->credentialsExpired;
    }

    /**
     * Set credentialsExpireAt
     *
     * @param \DateTime $credentialsExpireAt
     * @return FosUserUser
     */
    public function setCredentialsExpireAt($credentialsExpireAt)
    {
        $this->credentialsExpireAt = $credentialsExpireAt;
    
        return $this;
    }

    /**
     * Get credentialsExpireAt
     *
     * @return \DateTime 
     */
    public function getCredentialsExpireAt()
    {
        return $this->credentialsExpireAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return FosUserUser
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return FosUserUser
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set dateOfBirth
     *
     * @param \DateTime $dateOfBirth
     * @return FosUserUser
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;
    
        return $this;
    }

    /**
     * Get dateOfBirth
     *
     * @return \DateTime 
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return FosUserUser
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    
        return $this;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return FosUserUser
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    
        return $this;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set website
     *
     * @param string $website
     * @return FosUserUser
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    
        return $this;
    }

    /**
     * Get website
     *
     * @return string 
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set biography
     *
     * @param string $biography
     * @return FosUserUser
     */
    public function setBiography($biography)
    {
        $this->biography = $biography;
    
        return $this;
    }

    /**
     * Get biography
     *
     * @return string 
     */
    public function getBiography()
    {
        return $this->biography;
    }

    /**
     * Set gender
     *
     * @param string $gender
     * @return FosUserUser
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    
        return $this;
    }

    /**
     * Get gender
     *
     * @return string 
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return FosUserUser
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    
        return $this;
    }

    /**
     * Get locale
     *
     * @return string 
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set timezone
     *
     * @param string $timezone
     * @return FosUserUser
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    
        return $this;
    }

    /**
     * Get timezone
     *
     * @return string 
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return FosUserUser
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    
        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set facebookUid
     *
     * @param string $facebookUid
     * @return FosUserUser
     */
    public function setFacebookUid($facebookUid)
    {
        $this->facebookUid = $facebookUid;
    
        return $this;
    }

    /**
     * Get facebookUid
     *
     * @return string 
     */
    public function getFacebookUid()
    {
        return $this->facebookUid;
    }

    /**
     * Set facebookName
     *
     * @param string $facebookName
     * @return FosUserUser
     */
    public function setFacebookName($facebookName)
    {
        $this->facebookName = $facebookName;
    
        return $this;
    }

    /**
     * Get facebookName
     *
     * @return string 
     */
    public function getFacebookName()
    {
        return $this->facebookName;
    }

    /**
     * Set facebookData
     *
     * @param string $facebookData
     * @return FosUserUser
     */
    public function setFacebookData($facebookData)
    {
        $this->facebookData = $facebookData;
    
        return $this;
    }

    /**
     * Get facebookData
     *
     * @return string 
     */
    public function getFacebookData()
    {
        return $this->facebookData;
    }

    /**
     * Set twitterUid
     *
     * @param string $twitterUid
     * @return FosUserUser
     */
    public function setTwitterUid($twitterUid)
    {
        $this->twitterUid = $twitterUid;
    
        return $this;
    }

    /**
     * Get twitterUid
     *
     * @return string 
     */
    public function getTwitterUid()
    {
        return $this->twitterUid;
    }

    /**
     * Set twitterName
     *
     * @param string $twitterName
     * @return FosUserUser
     */
    public function setTwitterName($twitterName)
    {
        $this->twitterName = $twitterName;
    
        return $this;
    }

    /**
     * Get twitterName
     *
     * @return string 
     */
    public function getTwitterName()
    {
        return $this->twitterName;
    }

    /**
     * Set twitterData
     *
     * @param string $twitterData
     * @return FosUserUser
     */
    public function setTwitterData($twitterData)
    {
        $this->twitterData = $twitterData;
    
        return $this;
    }

    /**
     * Get twitterData
     *
     * @return string 
     */
    public function getTwitterData()
    {
        return $this->twitterData;
    }

    /**
     * Set gplusUid
     *
     * @param string $gplusUid
     * @return FosUserUser
     */
    public function setGplusUid($gplusUid)
    {
        $this->gplusUid = $gplusUid;
    
        return $this;
    }

    /**
     * Get gplusUid
     *
     * @return string 
     */
    public function getGplusUid()
    {
        return $this->gplusUid;
    }

    /**
     * Set gplusName
     *
     * @param string $gplusName
     * @return FosUserUser
     */
    public function setGplusName($gplusName)
    {
        $this->gplusName = $gplusName;
    
        return $this;
    }

    /**
     * Get gplusName
     *
     * @return string 
     */
    public function getGplusName()
    {
        return $this->gplusName;
    }

    /**
     * Set gplusData
     *
     * @param string $gplusData
     * @return FosUserUser
     */
    public function setGplusData($gplusData)
    {
        $this->gplusData = $gplusData;
    
        return $this;
    }

    /**
     * Get gplusData
     *
     * @return string 
     */
    public function getGplusData()
    {
        return $this->gplusData;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return FosUserUser
     */
    public function setToken($token)
    {
        $this->token = $token;
    
        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set twoStepCode
     *
     * @param string $twoStepCode
     * @return FosUserUser
     */
    public function setTwoStepCode($twoStepCode)
    {
        $this->twoStepCode = $twoStepCode;
    
        return $this;
    }

    /**
     * Get twoStepCode
     *
     * @return string 
     */
    public function getTwoStepCode()
    {
        return $this->twoStepCode;
    }

    /**
     * Add group
     *
     * @param Nwp\AssessmentBundle\Entity\FosUserGroup $group
     * @return FosUserUser
     */
    public function addGroup(\Nwp\AssessmentBundle\Entity\FosUserGroup $group)
    {
        $this->group[] = $group;
    
        return $this;
    }

    /**
     * Remove group
     *
     * @param Nwp\AssessmentBundle\Entity\FosUserGroup $group
     */
    public function removeGroup(\Nwp\AssessmentBundle\Entity\FosUserGroup $group)
    {
        $this->group->removeElement($group);
    }

    /**
     * Get group
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getGroup()
    {
        return $this->group;
    }
}