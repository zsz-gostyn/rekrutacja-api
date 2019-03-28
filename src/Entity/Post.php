<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 */
class Post implements \JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $ordinal;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $topic;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creation_date;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="posts")
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     */
    private $updates_count = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $notifications_count = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrdinal(): ?int
    {
        return $this->ordinal;
    }

    public function setOrdinal(int $ordinal): self
    {
        $this->ordinal = $ordinal;

        return $this;
    }

    public function getTopic(): ?string
    {
        return $this->topic;
    }

    public function setTopic(string $topic): self
    {
        $this->topic = $topic;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creation_date;
    }

    public function setCreationDate(\DateTimeInterface $creation_date = null): self
    {
        $this->creation_date = $creation_date;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

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

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'ordinal' => $this->ordinal,
            'topic' => $this->topic,
            'content' => $this->content,
            'active' => $this->active,
            'user' => $this->user->getId(),
            'creation_date' => $this->creation_date,
            'updates_count' => $this->updates_count,
            'notifications_count' => $this->notifications_count,
        ];
    }

    public function getUpdatesCount(): ?int
    {
        return $this->updates_count;
    }

    public function setUpdatesCount(int $updates_count): self
    {
        $this->updates_count = $updates_count;

        return $this;
    }

    public function incrementUpdatesCount(): self
    {
        $this->updates_count += 1;

        return $this;
    }

    public function getNotificationsCount(): ?int
    {
        return $this->notifications_count;
    }

    public function setNotificationsCount(int $notifications_count): self
    {
        $this->notifications_count = $notifications_count;

        return $this;
    }

    public function incrementNotificationsCount(): self
    {
        $this->notifications_count += 1;

        return $this;
    }
}
