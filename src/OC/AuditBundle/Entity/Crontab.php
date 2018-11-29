<?php

namespace Jiwon\AuditBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="acer_audit_crontab")
 * @ORM\Entity(repositoryClass="Jiwon\AuditBundle\Repository\CrontabRepository")
 */
class Crontab
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
    private $recurrence;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $heure;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enable;

    /**
     * @ORM\ManyToOne(targetEntity="Jiwon\AuditBundle\Entity\Template")
     * @ORM\JoinColumn(name="id_template")
     */
    private $id_template;

    /**
     * @ORM\ManyToMany(targetEntity="Jiwon\AuditBundle\Entity\Export", mappedBy="crontabs")
     * @ORM\JoinColumn(name="id_export")
     */
    private $exports;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $csv;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->exports = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set recurrence
     *
     * @param string $recurrence
     *
     * @return Crontab
     */
    public function setRecurrence($recurrence)
    {
        $this->recurrence = $recurrence;

        return $this;
    }

    /**
     * Get recurrence
     *
     * @return string
     */
    public function getRecurrence()
    {
        return $this->recurrence;
    }

    /**
     * Set heure
     *
     * @param string $heure
     *
     * @return Crontab
     */
    public function setHeure($heure)
    {
        $this->heure = $heure;

        return $this;
    }

    /**
     * Get heure
     *
     * @return string
     */
    public function getHeure()
    {
        return $this->heure;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Crontab
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
     * Set enable
     *
     * @param boolean $enable
     *
     * @return Crontab
     */
    public function setEnable($enable)
    {
        $this->enable = $enable;

        return $this;
    }

    /**
     * Get enable
     *
     * @return boolean
     */
    public function getEnable()
    {
        return $this->enable;
    }

    /**
     * Set idTemplate
     *
     * @param \Jiwon\AuditBundle\Entity\Template $idTemplate
     *
     * @return Crontab
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

    /**
     * Add export
     *
     * @param \Jiwon\AuditBundle\Entity\Export $export
     *
     * @return Crontab
     */
    public function addExport(\Jiwon\AuditBundle\Entity\Export $export)
    {
        $this->exports[] = $export;

        return $this;
    }

    /**
     * Remove export
     *
     * @param \Jiwon\AuditBundle\Entity\Export $export
     */
    public function removeExport(\Jiwon\AuditBundle\Entity\Export $export)
    {
        $this->exports->removeElement($export);
    }

    /**
     * Get exports
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getExports()
    {
        return $this->exports;
    }

    /**
     * Set csv
     *
     * @param string $csv
     *
     * @return Crontab
     */
    public function setCsv($csv)
    {
        $this->csv = $csv;

        return $this;
    }

    /**
     * Get csv
     *
     * @return string
     */
    public function getCsv()
    {
        return $this->csv;
    }
}
