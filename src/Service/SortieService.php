<?php

namespace App\Service;

use App\Entity\Sortie;

class SortieService {

    public const MESSAGE_LOGIN = 'Vous devez d\'abord vous connecter';
    public const MESSAGE_CREATION = 'La sortie a été créée';
    public const MESSAGE_PUBLICATION = 'La sortie a été publiée';
    public const MESSAGE_MODIFICATION = 'La sortie a été modifiée';
    public const MESSAGE_SUPPRESSION = 'La sortie a été supprimée';
    public const MESSAGE_ANNULATION = 'La sortie a été annulée';

    public const MESSAGE_NON_AFFICHABLE = 'La sortie ne peut être affichée';
    public const MESSAGE_NON_MODIFIABLE = 'La sortie ne peut être modifiée';
    public const MESSAGE_NON_ANNULABLE = 'La sortie ne peut être annulée';
    public const MESSAGE_NON_PUBLIABLE = 'La sortie ne peut être publiée';
    public const MESSAGE_NON_DESISTABLE = 'Vous ne pouvez plus vous désister de la sortie';
    public const MESSAGE_NON_INSCRIVABLE = 'Vous ne pouvez plus vous inscrire à la sortie';

    private EtatService $service;
    private \DateTime $date;

    public function __construct(EtatService $service) {
        $this->service = $service;
        $this->date = new \DateTime();
    }

    public function estAffichable(Sortie $sortie): bool {
        return in_array($sortie->getEtat()->getLibelle(), $this->service::ETATS_AFFICHABLES);
    }

    public function estModifiable(Sortie $sortie): bool {
        if ($sortie->getEtat()->getLibelle() === $this->service::ETAT_CREE &&
            $sortie->getDateLimiteInscription() > $this->date &&
            $sortie->getDateHeureDebut() > $this->date)
        {
            return true;
        }
        return false;
    }

    public function estCloturable(Sortie $sortie): bool {
        if ($sortie->getEtat()->getLibelle() === $this->service::ETAT_OUVERT &&
            $this->date > $sortie->getDateLimiteInscription())
        {
            return true;
        }
        return false;
    }

    public function estInscrivable(Sortie $sortie): bool {
        if ($sortie->getEtat()->getLibelle() === $this->service::ETAT_OUVERT &&
            $sortie->getNbInscriptionsMax() > $sortie->getParticipants()->count() &&
            $sortie->getDateLimiteInscription() > $this->date)
        {
            return true;
        }
        return false;
    }

    public function estDesistable(Sortie $sortie): bool {
        if ($this->estInscrivable($sortie) ||
            ($sortie->getEtat()->getLibelle() === $this->service::ETAT_CLOTURE &&
            $sortie->getDateLimiteInscription() > $this->date))
        {
            return true;
        }
        return false;
    }

    public function estEnCours(Sortie $sortie): bool {
        if ($sortie->getEtat()->getLibelle() === $this->service::ETAT_CLOTURE &&
            $this->date > $sortie->getDateHeureDebut())
        {
            return true;
        }
        return false;
    }

    public function estAnnulable(Sortie $sortie): bool {
        return in_array($sortie->getEtat()->getLibelle(), $this->service::ETATS_ANNULABLES);
    }

    public function estTerminable(Sortie $sortie): bool {
        if ($sortie->getEtat()->getLibelle() === $this->service::ETAT_EN_COURS &&
            $this->date > $this->obtenirDateFinSortie($sortie))
        {
            return true;
        }
        return false;
    }

    public function estHistorisable(Sortie $sortie): bool {
        if (in_array($sortie->getEtat()->getLibelle(), $this->service::ETATS_HISTORISABLES) &&
            $this->obtenirDateLimiteHistorisation() > $this->obtenirDateFinSortie($sortie))
        {
            return true;
        }
        return false;
    }

    private function obtenirDateFinSortie(Sortie $sortie): \DateTime {
        return $sortie->getDateHeureDebut()->modify('+'.$sortie->getDuree().' minutes');
    }

    private function obtenirDateLimiteHistorisation(): \DateTime {
        $dateJour = new \DateTime();
        return $dateJour->modify('-1 month');
    }

}
