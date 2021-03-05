<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 *
 * @UniqueEntity(
 *  fields={"email"},
 * )
 * @UniqueEntity(
 *  fields={"username"},
 * )
 */
class User implements UserInterface
{
    const ROLE_USER = 'ROLE_USER';

    const IGNORED_ATTRIBUTES = ['password', 'plainPassword', 'roles', 'apiToken', 'salt', 'projects', 'resetToken'];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true, nullable=false)
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=false)
     */
    private $password;

    /**
     * @var string $plainPassword
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $plainPassword;

    /**
    * @ORM\Column(type="string", unique=true, nullable=true)
    */
    private $apiToken;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    private $resetToken;

    /**
     * @ORM\OneToMany(targetEntity=Project::class, mappedBy="owner")
     */
    private $projects;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }


    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     */
    public function setPlainPassword(string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return mixed
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * @param mixed $apiToken
     */
    public function setApiToken($apiToken): void
    {
        $this->apiToken = $apiToken;
    }

    public function getSalt()
    {
        // Useless as we use Bcrypt
    }

    /**
     * @return Project[]|null
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * @param Project|null $projects
     * @return $this
     */
    public function setProjects(?Project $projects): self
    {
        $this->projects = $projects;

        return $this;
    }

    /**
     * @param Project $project
     */
    public function addProject(Project $project) {
        $this->projects[] = $project;
    }

    /**
     * @return mixed
     */
    public function getResetToken()
    {
        return $this->resetToken;
    }

    /**
     * @param mixed $resetToken
     */
    public function setResetToken($resetToken): void
    {
        $this->resetToken = $resetToken;
    }
}
