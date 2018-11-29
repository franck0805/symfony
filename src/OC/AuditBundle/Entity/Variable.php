<?php

namespace Jiwon\AuditBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="acer_audit_variable")
 * @ORM\Entity(repositoryClass="Jiwon\AuditBundle\Repository\VariableRepository")
 */
class Variable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $balise;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $valeur;

    /**
     * @ORM\ManyToMany(targetEntity="Jiwon\AdminBundle\Entity\Ne")
     */
    private $nes;

    /**
     * @ORM\ManyToOne(targetEntity="Jiwon\AuditBundle\Entity\Template")
     * @ORM\JoinColumn(name="id_template", nullable=true)
     */
    private $id_template;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->nes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set balise
     *
     * @param string $balise
     *
     * @return Variable
     */
    public function setBalise($balise)
    {
        $this->balise = $balise;

        return $this;
    }

    /**
     * Get balise
     *
     * @return string
     */
    public function getBalise()
    {
        return $this->balise;
    }

    /**
     * Set valeur
     *
     * @param string $valeur
     *
     * @return Variable
     */
    public function setValeur($valeur)
    {
        $this->valeur = $valeur;

        return $this;
    }

    /**
     * Get valeur
     *
     * @return string
     */
    public function getValeur()
    {
        return $this->valeur;
    }

    /**
     * Add ne
     *
     * @param \Jiwon\AdminBundle\Entity\Ne $ne
     *
     * @return Variable
     */
    public function addNe(\Jiwon\AdminBundle\Entity\Ne $ne)
    {
        $this->nes[] = $ne;

        return $this;
    }

    /**
     * Remove ne
     *
     * @param \Jiwon\AdminBundle\Entity\Ne $ne
     */
    public function removeNe(\Jiwon\AdminBundle\Entity\Ne $ne)
    {
        $this->nes->removeElement($ne);
    }

    /**
     * Get nes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNes()
    {
        return $this->nes;
    }

    /**
     * Set idTemplate
     *
     * @param \Jiwon\AuditBundle\Entity\Template $idTemplate
     *
     * @return Variable
     */
    public function setIdTemplate(\Jiwon\AuditBundle\Entity\Template $idTemplate = null)
    {
        $this->id_template = $idTemplate;

        return $this;
    }

    /**
     * Get idTemplate
     *
     * @return \Jiwon\AuditBundle\Entity\Template
     */
    public function getIdTemplate()
    {
        return $this->id_template;
    }
}
