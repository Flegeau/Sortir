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
