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

    #[ORM\Column(nullable: true)]
    #[Assert\All([
        new Assert\Type(type: 'integer', message: 'Le mois doit être indiqué au format numérique.'),
        new Assert\Range(min: 1, max: 12, notInRangeMessage: 'Le nombre indiqué doit être compris entre 1 et 12.'),
    ])]
    private ?array $months = null;

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

    public function getMonths(): ?array
    {
        return $this->months;
    }

    public function setMonths(?array $months): static
    {
        $this->months = $months;

        return $this;
    }

}
