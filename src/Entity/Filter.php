<?php

namespace App\Entity;


use phpDocumentor\Reflection\Types\Boolean;

class Filter
{
    private Campus|null $campus = null;

    private string|null $nom = null;

    private ?\DateTime $dateStart = null;

    private ?\DateTime $dateEnd = null;

    private ?bool $organisateur = false;

    private ?bool $inscrit = false;

    private ?bool $nonInscrit = false;

    private ?bool $passees = false;

    public function getCampus(): ?Campus {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self {
        $this->campus = $campus;

        return $this;
    }

    public function getNom(): ?string {
        return $this->nom;
    }

    public function setNom(?string $nom): self {
        $this->nom = $nom;

        return $this;
    }

    public function getDateStart(): ?\DateTime {
        return $this->dateStart;
    }

    public function setDateStart(?\DateTime $dateStart): self {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?\DateTime {
        return $this->dateEnd;
    }

    public function setDateEnd(?\DateTime $dateEnd): self {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getOrganisateur(): ?bool {
        return $this->organisateur;
    }

    public function setOrganisateur(?bool $organisateur): self {
        $this->organisateur = $organisateur;

        return $this;
    }

    public function getInscrit(): ?bool {
        return $this->inscrit;
    }

    public function setInscrit(?bool $inscrit): self {
        $this->inscrit = $inscrit;

        return $this;
    }

    public function getNonInscrit(): ?bool {
        return $this->nonInscrit;
    }

    public function setNonInscrit(?bool $nonInscrit): self {
        $this->nonInscrit = $nonInscrit;

        return $this;
    }

    public function getPassees(): ?bool {
        return $this->passees;
    }

    public function setPassees(?bool $passees): self {
        $this->passees = $passees;

        return $this;
    }

}