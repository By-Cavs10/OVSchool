<?php

namespace App\Entity;

use App\Repository\VilleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VilleRepository::class)]
#[ORM\UniqueConstraint(columns:['nom', 'code_postal'])]
#[UniqueEntity(fields: ['nom', 'codePostal'], message: "Une Ville avec le même nom et le même code postal existe déjà en BDD.")]
class Ville
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Veuillez saisir un nom.")]
    #[Assert\Length(min: 3, max: 30, minMessage: 'Ce nom de Ville est trop court: il doit faire au moins {{ limit }} caractères.', maxMessage: 'Ce nom de Ville est trop long: il doit faire au plus {{ limit }} caractères')]
    private ?string $nom = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: "Veuillez saisir un code postal.")]
    #[Assert\Length(
        max: 10,
        maxMessage: "Le code postal ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^\d{5}(\s+)?$/",
        message: "Le code postal doit contenir exactement 5 chiffres."
    )]
    private ?string $codePostal = null;

    /**
     * @var Collection<int, Lieu>
     */
    #[ORM\OneToMany(targetEntity: Lieu::class, mappedBy: 'ville', orphanRemoval: true)]
    private Collection $lieux;

    public function __construct()
    {
        $this->lieux = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * @return Collection<int, Lieu>
     */
    public function getLieux(): Collection
    {
        return $this->lieux;
    }

    public function addLieux(Lieu $lieux): static
    {
        if (!$this->lieux->contains($lieux)) {
            $this->lieux->add($lieux);
            $lieux->setVille($this);
        }

        return $this;
    }

    public function removeLieux(Lieu $lieux): static
    {
        if ($this->lieux->removeElement($lieux)) {
            // set the owning side to null (unless already changed)
            if ($lieux->getVille() === $this) {
                $lieux->setVille(null);
            }
        }

        return $this;
    }
}
