<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 */
class Comment
{
    const IGNORED_ATTRIBUTES = ['project', 'version'];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Project
     * @ORM\ManyToOne(targetEntity=Project::class)
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $project;

    /**
     * @var Version
     * @ORM\ManyToOne(targetEntity=Version::class)
     * @ORM\JoinColumn(name="version_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $version;

    /**
     * @ORM\Column(type="integer")
     */
    private $blockIndex;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $text;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $username;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param Project $project
     */
    public function setProject(Project $project): void
    {
        $this->project = $project;
    }

    /**
     * @return Version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param Version $version
     */
    public function setVersion(Version $version): void
    {
        $this->version = $version;
    }

    /**
     * @return mixed
     */
    public function getBlockIndex()
    {
        return $this->blockIndex;
    }

    /**
     * @param mixed $blockIndex
     */
    public function setBlockIndex($blockIndex): void
    {
        $this->blockIndex = $blockIndex;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }
}
