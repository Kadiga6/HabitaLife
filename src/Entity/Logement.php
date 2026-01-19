<?php

namespace App\Entity;

use App\Repository\LogementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LogementRepository::class)]
class Logement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    /**
     * @var Collection<int, Contrat>
     */
    #[ORM\OneToMany(targetEntity: Contrat::class, mappedBy: 'logement')]
    private Collection $contrats;

    #[ORM\Column(length: 100)]
    private ?string $ville = null;

    #[ORM\Column(length: 10)]
    private ?string $codePostal = null;

    #[ORM\Column(length: 50)]
    private ?string $typeLogement = null;

    #[ORM\Column]
    private ?\DateTime $dateCreation = null;

    /**
     * @var Collection<int, Consommation>
     */
    #[ORM\OneToMany(targetEntity: Consommation::class, mappedBy: 'logement')]
    private Collection $type;

    /**
     * @var Collection<int, Incident>
     */
    #[ORM\OneToMany(targetEntity: Incident::class, mappedBy: 'logement')]
    private Collection $titre;

    public function __construct()
    {
        $this->contrats = new ArrayCollection();
        $this->type = new ArrayCollection();
        $this->titre = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * @return Collection<int, Contrat>
     */
    public function getContrats(): Collection
    {
        return $this->contrats;
    }

    public function addContrat(Contrat $contrat): static
    {
        if (!$this->contrats->contains($contrat)) {
            $this->contrats->add($contrat);
            $contrat->setLogement($this);
        }

        return $this;
    }

    public function removeContrat(Contrat $contrat): static
    {
        if ($this->contrats->removeElement($contrat)) {
            // set the owning side to null (unless already changed)
            if ($contrat->getLogement() === $this) {
                $contrat->setLogement(null);
            }
        }

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

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

    public function getTypeLogement(): ?string
    {
        return $this->typeLogement;
    }

    public function setTypeLogement(string $typeLogement): static
    {
        $this->typeLogement = $typeLogement;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * @return Collection<int, Consommation>
     */
    public function getType(): Collection
    {
        return $this->type;
    }

    public function addType(Consommation $type): static
    {
        if (!$this->type->contains($type)) {
            $this->type->add($type);
            $type->setLogement($this);
        }

        return $this;
    }

    public function removeType(Consommation $type): static
    {
        if ($this->type->removeElement($type)) {
            // set the owning side to null (unless already changed)
            if ($type->getLogement() === $this) {
                $type->setLogement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Incident>
     */
    public function getTitre(): Collection
    {
        return $this->titre;
    }

    public function addTitre(Incident $titre): static
    {
        if (!$this->titre->contains($titre)) {
            $this->titre->add($titre);
            $titre->setLogement($this);
        }

        return $this;
    }

    public function removeTitre(Incident $titre): static
    {
        if ($this->titre->removeElement($titre)) {
            // set the owning side to null (unless already changed)
            if ($titre->getLogement() === $this) {
                $titre->setLogement(null);
            }
        }

        return $this;
    }
}
