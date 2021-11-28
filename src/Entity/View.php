<?php

namespace App\Entity;

use App\Repository\ViewRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ViewRepository::class)
 */
class View
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Video::class, inversedBy="views")
     * @ORM\JoinColumn(nullable=false)
     */
    private $video;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $IP;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVideo(): ?Video
    {
        return $this->video;
    }

    public function setVideo(?Video $video): self
    {
        $this->video = $video;

        return $this;
    }

    public function getIP(): ?string
    {
        return $this->IP;
    }

    public function setIP(string $IP): self
    {
        $this->IP = $IP;

        return $this;
    }
}
