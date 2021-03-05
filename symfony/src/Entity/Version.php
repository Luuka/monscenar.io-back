<?php

namespace App\Entity;

use App\Repository\VersionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VersionRepository::class)
 */
class Version
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
    * @ORM\Column(type="integer")
    */
    private $versionNumber;

    /**
     * @ORM\ManyToOne(targetEntity=Project::class)
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     */
    private $project;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $fountainText;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getVersionNumber()
    {
        return $this->versionNumber;
    }

    /**
     * @param mixed $versionNumber
     */
    public function setVersionNumber($versionNumber): void
    {
        $this->versionNumber = $versionNumber;
    }

    /**
     * @return mixed
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param mixed $project
     */
    public function setProject($project): void
    {
        $this->project = $project;
    }

    /**
     * @return mixed
     */
    public function getFountainText()
    {
        return $this->fountainText;
    }

    /**
     * @param mixed $fountainText
     */
    public function setFountainText($fountainText): void
    {
        $this->fountainText = $fountainText;
    }
}
