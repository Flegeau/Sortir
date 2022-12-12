<?php

namespace App\Service;

use App\Entity\Sortie;

class ControleSortie {

    private const ETATS_NON_AFFICHABLES = array('Créée', 'Annulée', 'Historisée');
    private const ETAT_MODIFIABLE = 'Créée';
    private \DateTime $date;

    public function __construct()
    {
        $this->date = new \DateTime();
    }

    public function estAffichable(Sortie $sortie): bool
    {
        if (in_array($sortie->getEtat()->getLibelle(), self::ETATS_NON_AFFICHABLES))
        {
            return false;
        }
        return true;
    }

    public function estModifiable(Sortie $sortie): bool
    {
        if ($sortie->getEtat()->getLibelle() !== self::ETAT_MODIFIABLE ||
            $this->date > $sortie->getDateLimiteInscription() ||
            $this->date > $sortie->getDateHeureDebut())
        {
            return false;
        }
        return true;
    }

}
