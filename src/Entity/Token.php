<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TokenRepository")
 */
class Token
{
    public const HTTP_HEADER_FIELD_NAME = 'X-AUTH-TOKEN';
    public const VALID_TIME = 600; // Valid time in seconds

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $value;

    /**
     * @ORM\Column(type="datetime")
     */
    private $valid_to;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="tokens")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getValidTo(): ?\DateTimeInterface
    {
        return $this->valid_to;
    }

    public function setValidTo(\DateTimeInterface $valid_to): self
    {
        $this->valid_to = $valid_to;

        return $this;
    }
    
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function renew(): self
    {
        $date = new \DateTime();
        $date->add(new \DateInterval('PT' . self::VALID_TIME . 'S'));
        $this->valid_to = $date;

        return $this;
    }

    public function isValid(): bool
    {
        $currentDateTime = new \DateTime();
        return $currentDateTime <= $this->valid_to;
    }
}
