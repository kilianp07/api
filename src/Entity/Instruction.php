<?php

namespace App\Entity;

use App\Repository\InstructionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InstructionRepository::class)]
class Instruction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::ARRAY)]
    #[Groups(['instruction:read', 'recette:read'])]
    private array $instructionList = [];

    #[ORM\Column]
    private ?bool $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInstructionList(): array
    {
        return $this->instructionList;
    }

    public function setInstructionList(array $instructionList): self
    {
        $this->instructionList = $instructionList;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }
}
