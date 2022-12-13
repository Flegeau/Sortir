<?php

namespace App\Service;

class EtatService {

    public const ETAT_CREE = 'Créée';
    public const ETAT_OUVERT = 'Ouverte';
    public const ETAT_CLOTURE = 'Clôturée';
    public const ETAT_EN_COURS = 'Activité en cours';
    public const ETAT_PASSE = 'Passée';
    public const ETAT_ANNULE = 'Annulée';
    public const ETAT_HISTORISE = 'Historisée';

    public const ETATS_AFFICHABLES = array(
        self::ETAT_OUVERT,
        self::ETAT_CLOTURE,
        self::ETAT_EN_COURS,
        self::ETAT_PASSE
    );
    public const ETATS_ANNULABLES = array(
        self::ETAT_OUVERT,
        self::ETAT_CLOTURE
    );
    public const ETATS_HISTORISABLES = array(
        self::ETAT_PASSE,
        self::ETAT_ANNULE
    );
}
