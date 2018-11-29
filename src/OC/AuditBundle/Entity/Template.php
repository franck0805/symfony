<?php

namespace Jiwon\AuditBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="acer_audit_template")
 * @ORM\Entity(repositoryClass="Jiwon\AuditBundle\Repository\TemplateRepository")
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
     * @ORM\Column(type="string", length=255)
     */
    private $version;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="Jiwon\AdminBundle\Entity\Association")
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
     * @ORM\JoinColumn(name="id_modele")
     */
    private $id_modele;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $commentaire;

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
     * Set version
     *
     * @param string $version
     *
     * @return Template
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Template
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set idAssociation
     *
     * @param \Jiwon\AdminBundle\Entity\Association $idAssociation
     *
     * @return Template
     */
    public function setIdAssociation(\Jiwon\AdminBundle\Entity\Association $idAssociation = null)
    {
        $this->id_association = $idAssociation;

        return $this;
    }

    /**
     * Get idAssociation
     *
     * @return \Jiwon\AdminBundle\Entity\Association
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
     * Set idModele
     *
     * @param \Jiwon\AdminBundle\Entity\Model $idModele
     *
     * @return Template
     */
    public function setIdModele(\Jiwon\AdminBundle\Entity\Model $idModele = null)
    {
        $this->id_modele = $idModele;

        return $this;
    }

    /**
     * Get idModele
     *
     * @return \Jiwon\AdminBundle\Entity\Model
     */
    public function getIdModele()
    {
        return $this->id_modele;
    }

    /**
     * Set commentaire
     *
     * @param string $commentaire
     *
     * @return Template
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }
}
