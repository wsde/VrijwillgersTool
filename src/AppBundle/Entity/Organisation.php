<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Organisation
 * @Assert\Callback({"AppBundle\Entity\organisation", "validateTelephone"})
 * @UniqueEntity(fields = "email", message = "organisation.email.already_used")
 * @UniqueEntity(fields = "telephone", message = "organisation.telephone.already_used")
 */
class Organisation extends EntityBase
{
    /**
     * @var int
    */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(message = "organisation.not_blank")
     * @Assert\Length(
     *      min = 4,
     *      max = 150,
     *      minMessage = "organisation.min_message",
     *      maxMessage = "organisation.max_message"
     * )
    */
    private $name;

    /**
     * @var string
     * @Assert\NotBlank(message = "organisation.not_blank")
     * @Assert\Length(
     *      min = 20,
     *      max = 2000,
     *      minMessage = "vacancy.min_message",
     *      maxMessage = "vacancy.max_message"
     * )
     * @Assert\NotEqualTo("nieuw")
     * )
    */
    private $description;

    /**
     * @var \AppBundle\Entity\Person
     */
    private $creator;


    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $administrators;


    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $likers;

    /**
     * @var string
     * @Assert\Email(
     *     message = "organisation.email.valid",
     *     checkHost = true
     * )
     */
    private $email;

    /**
     * @var string
     * @Assert\NotBlank(message = "organisation.not_blank")
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "organisation.max_message"
     * )
     */
    private $street;

    /**
     * @var int
     * @Assert\Regex(
     *     pattern = "/^[0-9]*$/",
     *     message = "organisation.not_numeric"
     * )
     * @Assert\Range(
     *      min = 0,
     *      max = 999999,
     *      minMessage = "organisation.not_positive"
     * )
     */
    private $number;

    /**
     * @var int
     * @Assert\Length(
     * 		min = 1,
     *      max = 6,
     *      minMessage = "organisation.min_message_one",
     *      maxMessage = "organisation.max_message"
     * )
     * @Assert\Regex(
     *     pattern = "/^[a-zA-Z0-9]{1,6}$/",
     *     message = "organisation.bus.valid"
     * )
     */
    private $bus;

    /**
     * @var int
     * @Assert\Regex(
     *     pattern = "/^[0-9]*$/",
     *     message = "organisation.not_numeric"
     * )
     * @Assert\Range(
     *      min = 1000,
     *      max = 9999,
     *      minMessage = "organisation.not_positive",
     *      maxMessage = "not_more_than"
     * )
     * @Assert\Length(
     *      min = 4,
     *      max = 4,
     *      exactMessage = "organisation.exact"
     * )
     */
    private $postalCode;

    /**
     * @var string
     * @Assert\Length(
     *      min = 1,
     *      max = 100,
     *      minMessage = "organisation.min_message",
     *      maxMessage = "organisation.max_message"
     * )
     */
    private $city;

    /**
     * @var string
     */
    private $urlid;


    /**
     * @var string
     */
    private $logoName;
    /**
     * @var string
     */
    private $slogan;

    /**
     * Set urlId
     *
     * @param string $urlId
     *
     * @return Organisation
     */
    public function setUrlId($urlId)
    {
        $this->urlid = $urlId;

        return $this;
    }

    /**
     * Get urlId
     *
     * @return string
     */
    public function getUrlId()
    {
        return $this->urlid;
    }

    /**
     * @var string
     * assert callback statement for telephone at top of class
     */
    private $telephone;

    public static function validateTelephone($org, ExecutionContextInterface  $context)
    {
        $telephone = str_replace(' ', '', $org->getTelephone());

        if (!ctype_digit($telephone)
        or !strlen($telephone) == 10)
        {
            $context->buildViolation("organisation.telephone.valid")
                ->atPath("telephone")
                ->addViolation();
        }
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $vacancies;

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Organisation
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
     * Set description
     *
     * @param string $description
     *
     * @return Organisation
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set lastUpdate
     *
     * @param \DateTime $lastUpdate
     *
     * @return Organisation
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * Get lastUpdate
     *
     * @return \DateTime
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
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
     * Set id
     *
     * @return Organisation
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set creator
     *
     * @param \AppBundle\Entity\Person $creator
     *
     * @return Organisation
     */
    public function setCreator(\AppBundle\Entity\Person $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \AppBundle\Entity\Person
     */
    public function getCreator()
    {
        return $this->creator;
    }


    /**
     * Add administator
     *
     * @param \AppBundle\Entity\Person $administator
     *
     * @return Person
     */
    public function addAdministrator(\AppBundle\Entity\Person $administator)
    {
        $this->administators[] = $administator;

        return $this;
    }

    /**
     * Remove administator
     *
      * @param \AppBundle\Entity\Person $administator
     *
     * @return Person
     */
    public function removeAdministator(\AppBundle\Entity\Person $administator)
    {
        $this->administators->removeElement($administator);

        return $this;
    }

    /**
     * Get administators
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdministators()
    {
        return $this->administators;
    }


    /**
     * Add liker
     *
     * @param \AppBundle\Entity\Person $liker
     *
     * @return Organisation
     */
    public function addLiker(\AppBundle\Entity\Person $liker)
    {
        $this->likers[] = $liker;

        return $this;
    }

    /**
     * Remove liker
     *
     * @param \AppBundle\Entity\Person $liker
     */
    public function removeLiker(\AppBundle\Entity\Person $liker)
    {
        $this->likers->removeElement($liker);
    }

    /**
     * Get likers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLikers()
    {
        return $this->likers;
    }


    /**
     * Set logoName
     *
     * @param string $logoName
     *
     * @return Organisation
     */
    public function setLogoName($logoName)
    {
        $this->logoName = $logoName;

        return $this;
    }

    /**
     * Get logoName
     *
     * @return string
     */
    public function getLogoName()
    {
        return $this->logoName;
    }


    /**
     * Set slogan
     *
     * @param string $slogan
     *
     * @return Organisation
     */
    public function setSlogan($slogan)
    {
        $this->slogan = $slogan;

        return $this;
    }

    /**
     * Get slogan
     *
     * @return string
     */
    public function getSlogan()
    {
        return $this->slogan;
    }

    
    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    function __toString()
    {
        return json_encode( array(
            "Entity" => $this->getClassName(),
            "Id" => $this->getId(),
            "Values" => array(
                "Name" => $this->getName(),
                "Description" => $this->getDescription(),
                "Email" => $this->getEmail(),
                "Street" => $this->getStreet(),
                "Number" => $this->getNumber(),
                "PostalCode" => $this->getpostalCode(),
                "Bus" => $this->getBus(),
                "City" => $this->getCity(),
                "Telephone" => $this->getTelephone()
            )
        ));
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Organisation
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
     * Set address
     *
     * @param string $address
     *
     * @return Organisation
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set telephone
     *
     * @param string $telephone
     *
     * @return Organisation
     */
    public function setTelephone($telephone)
    {
        $this->telephone = preg_replace("/\D/", "", $telephone);

        return $this;
    }

    /**
     * Get telephone
     *
     * @return string
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * Set street
     *
     * @param string $street
     *
     * @return Organisation
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set number
     *
     * @param \int $number
     *
     * @return Organisation
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return \int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set postalCode
     *
     * @param \int $postalCode
     *
     * @return Organisation
     */
    public function setpostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get postalCode
     *
     * @return \int
     */
    public function getpostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set bus
     *
     * @param \int $bus
     *
     * @return Organisation
     */
    public function setBus($bus)
    {
        $this->bus = $bus;

        return $this;
    }

    /**
     * Get bus
     *
     * @return \int
     */
    public function getBus()
    {
        return $this->bus;
    }


    /**
     * Set city
     *
     * @param string $city
     *
     * @return Organisation
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->vacancies = new \Doctrine\Common\Collections\ArrayCollection();
        $this->administrators = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add vacancy
     *
     * @param \AppBundle\Entity\Vacancy $vacancy
     *
     * @return Organisation
     */
    public function addVacancy(\AppBundle\Entity\Vacancy $vacancy)
    {
        $this->vacancies[] = $vacancy;

        return $this;
    }

    /**
     * Remove vacancy
     *
     * @param \AppBundle\Entity\Vacancy $vacancy
     */
    public function removeVacancy(\AppBundle\Entity\Vacancy $vacancy)
    {
        $this->vacancies->removeElement($vacancy);
    }

    /**
     * Get vacancies
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVacancies()
    {
        return $this->vacancies;
    }

    public function normaliseUrlId($em)
    {
        if (!is_null($this->getUrlId()))
        {
            $encoder = new UrlEncoder($em);
            $this->setUrlId($encoder->encode($this, $this->getName()));
        }
    }


}
