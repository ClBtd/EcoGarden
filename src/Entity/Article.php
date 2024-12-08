<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "L'article doit avoir un contenu.'")]
    #[Assert\Length(min: 10, minMessage: "L'article doit faire au moins {{ limit }} caractères")]
    private ?string $content = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\NotBlank(message: "L'article doit avoir un contenu.'")]
    #[Assert\Range(min:1, max:12, notInRangeMessage:"Le mois doit être indiqué par un nombre compris entre 1 et 12.")]
    private ?int $month = null;

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

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function setMonth(int $month): static
    {
        $this->month = $month;

        return $this;
    }
}
