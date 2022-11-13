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
    private array $instructionList = [];

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
}
