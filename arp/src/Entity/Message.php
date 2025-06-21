<?php


namespace App\Entity;


use App\Repository\MessageRepository;

use Doctrine\DBAL\Types\Types;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: MessageRepository::class)]

class Message

{

    #[ORM\Id]

    #[ORM\GeneratedValue]

    #[ORM\Column]

    private ?int $id = null;


    #[ORM\Column(length: 255)]

    private ?string $content = null;


    #[ORM\Column(length: 255)]

    private ?string $sender = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]

    private ?\DateTimeInterface $timestamp = null;

    #[ORM\Column]
    private ?int $roomId = null;


    public function getId(): ?int

    {

        return $this->id;

    }


    public function getContent(): ?string

    {

        return $this->content;

    }


    public function setContent(string $content): static

    {

        $this->content = $content;


        return $this;

    }


    public function getSender(): ?string

    {

        return $this->sender;

    }


    public function setSender(string $sender): static

    {

        $this->sender = $sender;


        return $this;

    }


    public function getTimestamp(): ?\DateTimeInterface

    {

        return $this->timestamp;

    }


    public function setTimestamp(\DateTimeInterface $timestamp): static

    {

        $this->timestamp = $timestamp;


        return $this;

    }

    public function getRoomId(): ?int
    {
        return $this->roomId;
    }

    public function setRoomId(int $roomId): static
    {
        $this->roomId = $roomId;

        return $this;
    }

}