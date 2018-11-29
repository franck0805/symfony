<?php

namespace Jiwon\AuditBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="acer_audit_export")
 * @ORM\Entity(repositoryClass="Jiwon\AuditBundle\Repository\ExportRepository")
 */
class Export
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $folder;

    /**
     * @ORM\ManyToMany(targetEntity="Jiwon\AuditBundle\Entity\Crontab", inversedBy="exports")
     * @ORM\JoinColumn(name="id_crontab")
     */
    private $crontabs;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->crontabs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set nom
     *
     * @param string $nom
     *
     * @return Export
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
     * Set folder
     *
     * @param string $folder
     *
     * @return Export
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Get folder
     *
     * @return string
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Add crontab
     *
     * @param \Jiwon\AuditBundle\Entity\Crontab $crontab
     *
     * @return Export
     */
    public function addCrontab(\Jiwon\AuditBundle\Entity\Crontab $crontab)
    {
        $this->crontabs[] = $crontab;

        return $this;
    }

    /**
     * Remove crontab
     *
     * @param \Jiwon\AuditBundle\Entity\Crontab $crontab
     */
    public function removeCrontab(\Jiwon\AuditBundle\Entity\Crontab $crontab)
    {
        $this->crontabs->removeElement($crontab);
    }

    /**
     * Get crontabs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCrontabs()
    {
        return $this->crontabs;
    }

    public function __toString()
    {
        return $this->getNom();
    }
}
