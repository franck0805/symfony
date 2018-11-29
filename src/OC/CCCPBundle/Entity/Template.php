<?php

namespace Jiwon\CCCPBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="cccp_template")
 * @ORM\Entity(repositoryClass="Jiwon\CCCPBundle\Repository\TemplateRepository")
 * @UniqueEntity("nom")
 */
class Template
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $nom;

    /**
     * @ORM\ManyToOne(targetEntity="Jiwon\CCCPBundle\Entity\Categorie")
     * @ORM\JoinColumn(nullable=false, name="id_categorie")
     */
    private $id_categorie;

    /**
     * @ORM\ManyToOne(targetEntity="Jiwon\AdminBundle\Entity\NewAssociation")
     * @ORM\JoinColumn(name="id_association")
     */
    private $id_association;

    /**
     * @ORM\ManyToOne(targetEntity="Jiwon\AdminBundle\Entity\Constructeur")
     * @ORM\JoinColumn(name="id_constructeur")
     */
    private $id_constructeur;

    /**
     * @ORM\ManyToOne(targetEntity="Jiwon\AdminBundle\Entity\Model")
     * @ORM\JoinColumn(name="id_model")
     */
    private $id_model;

    /**
     * @ORM\ManyToOne(targetEntity="Jiwon\CCCPBundle\Entity\Type")
     * @ORM\JoinColumn(name="id_type")
     */
    private $id_type;

    /**
     * @ORM\Column(type="datetime")
     * @ORM\JoinColumn(name="date")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="Jiwon\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="id_user")
     */
    private $id_user;

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
     * Set nom
     *
     * @param string $nom
     *
     * @return Template
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set idAssociation
     *
     * @param \Jiwon\AdminBundle\Entity\NewAssociation $idAssociation
     *
     * @return Template
     */
    public function setIdAssociation(\Jiwon\AdminBundle\Entity\NewAssociation $idAssociation = null)
    {
        $this->id_association = $idAssociation;

        return $this;
    }

    /**
     * Get idAssociation
     *
     * @return \Jiwon\AdminBundle\Entity\NewAssociation
     */
    public function getIdAssociation()
    {
        return $this->id_association;
    }

    /**
     * Set idConstructeur
     *
     * @param \Jiwon\AdminBundle\Entity\Constructeur $idConstructeur
     *
     * @return Template
     */
    public function setIdConstructeur(\Jiwon\AdminBundle\Entity\Constructeur $idConstructeur = null)
    {
        $this->id_constructeur = $idConstructeur;

        return $this;
    }

    /**
     * Get idConstructeur
     *
     * @return \Jiwon\AdminBundle\Entity\Constructeur
     */
    public function getIdConstructeur()
    {
        return $this->id_constructeur;
    }

    /**
     * Set idModel
     *
     * @param \Jiwon\AdminBundle\Entity\Model $idModel
     *
     * @return Template
     */
    public function setIdModel(\Jiwon\AdminBundle\Entity\Model $idModel = null)
    {
        $this->id_model = $idModel;

        return $this;
    }

    /**
     * Get idModel
     *
     * @return \Jiwon\AdminBundle\Entity\Model
     */
    public function getIdModel()
    {
        return $this->id_model;
    }

    /**
     * Set idCategorie
     *
     * @param \Jiwon\CCCPBundle\Entity\Categorie $idCategorie
     *
     * @return Template
     */
    public function setIdCategorie(\Jiwon\CCCPBundle\Entity\Categorie $idCategorie)
    {
        $this->id_categorie = $idCategorie;

        return $this;
    }

    /**
     * Get idCategorie
     *
     * @return \Jiwon\CCCPBundle\Entity\Categorie
     */
    public function getIdCategorie()
    {
        return $this->id_categorie;
    }

    /**
     * Set idType
     *
     * @param \Jiwon\CCCPBundle\Entity\Type $idType
     *
     * @return Template
     */
    public function setIdType(\Jiwon\CCCPBundle\Entity\Type $idType = null)
    {
        $this->id_type = $idType;

        return $this;
    }

    /**
     * Get idType
     *
     * @return \Jiwon\CCCPBundle\Entity\Type
     */
    public function getIdType()
    {
        return $this->id_type;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Template
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set idUser
     *
     * @param \Jiwon\UserBundle\Entity\User $idUser
     *
     * @return Template
     */
    public function setIdUser(\Jiwon\UserBundle\Entity\User $idUser = null)
    {
        $this->id_user = $idUser;

        return $this;
    }

    /**
     * Get idUser
     *
     * @return \Jiwon\UserBundle\Entity\User
     */
    public function getIdUser()
    {
        return $this->id_user;
    }
}
