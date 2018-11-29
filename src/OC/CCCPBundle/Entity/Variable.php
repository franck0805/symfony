<?php

namespace Jiwon\CCCPBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="cccp_variable")
 * @ORM\Entity(repositoryClass="Jiwon\CCCPBundle\Repository\VariableRepository")
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
     * @ORM\ManyToOne(targetEntity="Jiwon\CCCPBundle\Entity\Template")
     * @ORM\JoinColumn(name="id_template", nullable=true)
     */
    private $id_template;

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
     * Set idTemplate
     *
     * @param \Jiwon\CCCPBundle\Entity\Template $idTemplate
     *
     * @return Variable
     */
    public function setIdTemplate(\Jiwon\CCCPBundle\Entity\Template $idTemplate = null)
    {
        $this->id_template = $idTemplate;

        return $this;
    }

    /**
     * Get idTemplate
     *
     * @return \Jiwon\CCCPBundle\Entity\Template
     */
    public function getIdTemplate()
    {
        return $this->id_template;
    }
}
