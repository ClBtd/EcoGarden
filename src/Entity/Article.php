<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
