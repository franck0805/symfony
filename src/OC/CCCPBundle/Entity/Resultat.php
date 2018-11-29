<?php

namespace Jiwon\CCCPBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="cccp_resultat")
 * @ORM\Entity(repositoryClass="Jiwon\CCCPBundle\Repository\ResultatRepository")
 */
class Resultat
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Jiwon\CCCPBundle\Entity\Template")
     * @ORM\JoinColumn(name="id_template", nullable=false)
     */
    private $id_template;

    /**
     * @ORM\ManyToOne(targetEntity="Jiwon\AdminBundle\Entity\NewAssociation")
     * @ORM\JoinColumn(name="id_association", nullable=false)
     */
    private $id_association;

    /**
     * @ORM\ManyToOne(targetEntity="Jiwon\AdminBundle\Entity\Model")
     * @ORM\JoinColumn(name="id_model", nullable=false)
     */
    private $id_model;

    /**
     * @ORM\Column(type="integer")
     */
    private $success;

    /**
     * @ORM\Column(type="integer")
     */
    private $failed;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

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
     * Set success
     *
     * @param integer $success
     *
     * @return Resultat
     */
    public function setSuccess($success)
    {
        $this->success = $success;

        return $this;
    }

    /**
     * Get success
     *
     * @return integer
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * Set failed
     *
     * @param integer $failed
     *
     * @return Resultat
     */
    public function setFailed($failed)
    {
        $this->failed = $failed;

        return $this;
    }

    /**
     * Get failed
     *
     * @return integer
     */
    public function getFailed()
    {
        return $this->failed;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Resultat
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
     * Set idTemplate
     *
     * @param \Jiwon\CCCPBundle\Entity\Template $idTemplate
     *
     * @return Resultat
     */
    public function setIdTemplate(\Jiwon\CCCPBundle\Entity\Template $idTemplate)
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

    /**
     * Set idAssociation
     *
     * @param \Jiwon\AdminBundle\Entity\NewAssociation $idAssociation
     *
     * @return Resultat
     */
    public function setIdAssociation(\Jiwon\AdminBundle\Entity\NewAssociation $idAssociation)
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
     * Set idModel
     *
     * @param \Jiwon\AdminBundle\Entity\Model $idModel
     *
     * @return Resultat
     */
    public function setIdModel(\Jiwon\AdminBundle\Entity\Model $idModel)
    {
        $this->id_modele = $idModel;

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
}
