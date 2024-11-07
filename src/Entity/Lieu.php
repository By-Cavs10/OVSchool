<?php

namespace App\Entity;

use App\Repository\LieuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LieuRepository::class)]
#[ORM\UniqueConstraint(columns:['nom', 'ville_id'])]
#[UniqueEntity(fields: ['nom', 'ville'], message: "Un Lieu avec le même nom de la même Ville existe déjà en BDD.")]
class Lieu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Veuillez saisir un nom de Lieu.")]
    #[Assert\Length(min: 3, max: 30, minMessage: 'Ce nom de Lieu est trop court: il doit faire au moins {{ limit }} caractères.', maxMessage: 'Ce nom de Lieu est trop long: il doit faire au plus {{ limit }} caractères')]
    private ?string $nom = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Length(
        max: 30,
        maxMessage: "Le nom de la rue ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $rue = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(
        min: -90,
        max: 90,
        notInRangeMessage: "La latitude doit être comprise entre -90 et 90 degrés."
    )]
    #[Assert\Regex(
        pattern: "/^-?\d+(\.\d{1,7})?$/",
        message: "La latitude doit être un nombre décimal avec jusqu'à 7 chiffres après la virgule."
    )]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(
        min: -180,
        max: 180,
        notInRangeMessage: "La longitude doit être comprise entre -180 et 180 degrés."
    )]
    #[Assert\Regex(
        pattern: "/^-?\d+(\.\d{1,7})?$/",
        message: "La longitude doit être un nombre décimal avec jusqu'à 7 chiffres après la virgule."
    )]
    private ?float $longitude = null;

    #[ORM\ManyToOne(inversedBy: 'lieux')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "Veuillez saisir une ville.")]
    private ?Ville $ville = null;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'lieu')]
    private Collection $sorties;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
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

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(?string $rue): static
    {
        $this->rue = $rue;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getVille(): ?Ville
    {
        return $this->ville;
    }

    public function setVille(?Ville $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(Sortie $sorty): static
    {
        if (!$this->sorties->contains($sorty)) {
            $this->sorties->add($sorty);
            $sorty->setLieu($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): static
    {
        if ($this->sorties->removeElement($sorty)) {
            // set the owning side to null (unless already changed)
            if ($sorty->getLieu() === $this) {
                $sorty->setLieu(null);
            }
        }

        return $this;
    }
}
