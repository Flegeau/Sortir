<?php

namespace App\Service;

use App\Entity\Sortie;

class ControleSortie {

    public const MESSAGE_LOGIN = 'Vous devez d\'abord vous connecter';
    public const MESSAGE_NON_AFFICHABLE = 'La sortie ne peut être affichée';
    public const MESSAGE_NON_MODIFIABLE = 'La sortie ne peut être modifiée';
    public const MESSAGE_CREATION = 'La sortie a été créée';
    public const MESSAGE_PUBLICATION = 'La sortie a été publiée';
    public const MESSAGE_MODIFICATION = 'La sortie a été modifiée';
    public const MESSAGE_SUPPRESSION = 'La sortie a été supprimée';

    private const ETAT_CREE = 'Créée';
    private const ETAT_OUVERT = 'Ouverte';
    private const ETAT_CLOTURE = 'Clôturée';
    private const ETAT_EN_COURS = 'Activité en cours';
    private const ETAT_PASSE = 'Passée';
    private const ETAT_ANNULE = 'Annulée';
    private const ETAT_HISTORISE = 'Historisée';

    private const ETATS_AFFICHABLES = array(
        self::ETAT_OUVERT,
        self::ETAT_CLOTURE,
        self::ETAT_EN_COURS,
        self::ETAT_PASSE
    );

    private const ETATS_ANNULABLES = array(
        self::ETAT_OUVERT,
        self::ETAT_CLOTURE
    );
    private const ETATS_HISTORISABLES = array(
        self::ETAT_PASSE,
        self::ETAT_ANNULE
    );

    private \DateTime $date;

    public function __construct()
    {
        $this->date = new \DateTime();
    }

    public function estAffichable(Sortie $sortie): bool
    {
        if (in_array($sortie->getEtat()->getLibelle(), self::ETATS_AFFICHABLES))
        {
            return true;
        }
        return false;
    }

    public function estModifiable(Sortie $sortie): bool
    {
        if ($sortie->getEtat()->getLibelle() === self::ETAT_CREE &&
            $sortie->getDateLimiteInscription() > $this->date &&
            $sortie->getDateHeureDebut() > $this->date)
        {
            return true;
        }
        return false;
    }

    public function estInscrivable(Sortie $sortie): bool
    {
        if ($sortie->getEtat()->getLibelle() === self::ETAT_OUVERT &&
            count($sortie->getParticipants()) < $sortie->getNbInscriptionsMax() &&
            $sortie->getDateLimiteInscription() > $this->date)
        {
            return true;
        }
        return false;
    }

    public function estDesistable(Sortie $sortie): bool
    {
        if ($this->estInscrivable($sortie) ||
            ($sortie->getEtat()->getLibelle() === self::ETAT_CLOTURE &&
            $sortie->getDateLimiteInscription() > $this->date))
        {
            return true;
        }
        return false;
    }

    public function estEnCours(Sortie $sortie): bool
    {
        if ($sortie->getEtat()->getLibelle() === self::ETAT_CLOTURE &&
            $this->date > $sortie->getDateHeureDebut())
        {
            return true;
        }
        return false;
    }

    public function estAnnulable(Sortie $sortie): bool
    {
        if (in_array($sortie->getEtat()->getLibelle(), self::ETATS_ANNULABLES))
        {
            return true;
        }
        return false;
    }

    public function estTerminable(Sortie $sortie): bool
    {
        if ($sortie->getEtat()->getLibelle() === self::ETAT_EN_COURS &&
            $this->date > $this->obtenirDateFinSortie($sortie))
        {
            return true;
        }
        return false;
    }

    public function estHistorisable(Sortie $sortie): bool
    {
        if (in_array($sortie->getEtat()->getLibelle(), self::ETATS_HISTORISABLES) &&
            $this->obtenirDateLimiteHistorisation() > $this->obtenirDateFinSortie($sortie))
        {
            return true;
        }
        return false;
    }

    private function obtenirDateFinSortie(Sortie $sortie): \DateTime
    {
        return $sortie->getDateHeureDebut()->modify('+'.$sortie->getDuree().' minutes');
    }

    private function obtenirDateLimiteHistorisation(): \DateTime
    {
        return $this->date->modify('-1 month');
    }

}
