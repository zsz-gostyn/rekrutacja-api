<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchoolRepository")
 */
class School implements \JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Subscriber", mappedBy="school")
     */
    private $subscribers;

    public function __construct()
    {
        $this->subscribers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name = null): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Subscriber[]
     */
    public function getSubscribers(): Collection
    {
        return $this->subscribers;
    }

    public function addSubscriber(Subscriber $subscriber): self
    {
        if (!$this->subscribers->contains($subscriber)) {
            $this->subscribers[] = $subscriber;
            $subscriber->setSchool($this);
        }

        return $this;
    }

    public function removeSubscriber(Subscriber $subscriber): self
    {
        if ($this->subscribers->contains($subscriber)) {
            $this->subscribers->removeElement($subscriber);
            // set the owning side to null (unless already changed)
            if ($subscriber->getSchool() === $this) {
                $subscriber->setSchool(null);
            }
        }

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }
}
