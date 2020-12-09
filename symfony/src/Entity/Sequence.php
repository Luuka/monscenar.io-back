<?php

namespace App\Entity;

use App\Repository\SequenceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SequenceRepository::class)
 */
class Sequence
{
    const IGNORED_ATTRIBUTES = ['project'];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $orderIndex;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $fountainText;

    /**
     * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="sequences")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $project;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getOrderIndex(): ?int
    {
        return $this->orderIndex;
    }

    public function setOrderIndex(int $orderIndex): self
    {
        $this->orderIndex = $orderIndex;

        return $this;
    }

    public function getFountainText(): ?string
    {
        return $this->fountainText;
    }

    public function setFountainText(?string $fountainText): self
    {
        $this->fountainText = $fountainText;

        return $this;
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
}
