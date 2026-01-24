<?php

namespace App\Entity;

use App\Repository\ConsommationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ConsommationRepository::class)]
#[Assert\Callback('validatePeriode')]
class Consommation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'type')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Logement $logement = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $valeur = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;


    #[ORM\Column(length: 20)]
    private ?string $unite = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $periodeDebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $periodeFin = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateCreation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogement(): ?Logement
    {
        return $this->logement;
    }

    public function setLogement(?Logement $logement): static
    {
        $this->logement = $logement;

        return $this;
    }

    public function getValeur(): ?string
    {
        return $this->valeur;
    }

    public function setValeur(string $valeur): static
    {
        $this->valeur = $valeur;

        return $this;
    }

    public function getUnite(): ?string
    {
        return $this->unite;
    }

    public function setUnite(string $unite): static
    {
        $this->unite = $unite;

        return $this;
    }

    public function getPeriodeDebut(): ?\DateTime
    {
        return $this->periodeDebut;
    }

public function setPeriodeDebut(?\DateTimeInterface $periodeDebut): self
{
    $this->periodeDebut = $periodeDebut;
    return $this;
}


            public function getPeriodeFin(): ?\DateTime
            {
                return $this->periodeFin;
            }

  public function setPeriodeFin(?\DateTimeInterface $periodeFin): self
{
    $this->periodeFin = $periodeFin;
    return $this;
}


    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function getType(): ?string
{
    return $this->type;
}

public function setType(string $type): self
{
    $this->type = $type;
    return $this;
}


    public function setDateCreation(?\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Valide que la date de début est antérieure à la date de fin.
     * Cette méthode est appelée automatiquement par Symfony Validator
     * via la contrainte Assert\Callback déclarée au niveau de la classe.
     */
    public function validatePeriode(ExecutionContextInterface $context): void
    {
        // Vérifier que les deux dates sont présentes
        if ($this->periodeDebut === null || $this->periodeFin === null) {
            return; // Les validations NotBlank gèrent les champs vides
        }

        // Vérifier que periodeDebut < periodeFin
        if ($this->periodeDebut >= $this->periodeFin) {
            $context->buildViolation('La date de début doit être strictement antérieure à la date de fin.')
                ->atPath('periodeFin') // L'erreur s'affiche sous le champ periodeFin
                ->addViolation();
        }
    }
}
