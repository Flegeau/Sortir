<?php

namespace App\Security\Voter;

use App\Entity\Sortie;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class SortieVoter extends Voter
{
    public const SHOW_SORTIE = 'SHOW_SORTIE';
    public const EDITABLE_SORTIE = 'EDITABLE_SORTIE';
    public const CANCEL_SORTIE = 'CANCEL_SORTIE';
    public const IN_SORTIE = 'IN_SORTIE';
    public const OUT_SORTIE = 'OUT_SORTIE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::SHOW_SORTIE, self::EDITABLE_SORTIE, self::CANCEL_SORTIE, self::IN_SORTIE, self::OUT_SORTIE])
            && $subject instanceof \App\Entity\Sortie;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        /**
         * @var Sortie $subject
         */
        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::SHOW_SORTIE:
                $libellesShow = ['Ouverte', 'Clôturée', 'Activité en cours', 'Passée'];
                if (in_array($subject->getEtat()->getLibelle(), $libellesShow)){
                    return true;
                } else {
                    return false;
                }
            case self::EDITABLE_SORTIE:
                if ($user === $subject->getOrganisateur() && $subject->getEtat()->getLibelle() == "Créée"){
                    return true;
                } else {
                    return false;
                }
            case self::CANCEL_SORTIE:
                $libelles = ['Ouverte', 'Clôturée'];
                if ($user === $subject->getOrganisateur() && in_array($subject->getEtat()->getLibelle(), $libelles)){
                    return true;
                } else {
                    return false;
                }
            case self::IN_SORTIE:
                if ($user !== $subject->getOrganisateur() && !$subject->getParticipants()->contains($user) && $subject->getEtat()->getLibelle() == "Ouverte" && $subject->getDateLimiteInscription() >= new \DateTime()){
                    return true;
                } else {
                    return false;
                }
            case self::OUT_SORTIE:
                $libelles = ['Ouverte', 'Clôturée'];
                if ($user !== $subject->getOrganisateur() && $subject->getParticipants()->contains($user) && in_array($subject->getEtat()->getLibelle(), $libelles) && $subject->getDateLimiteInscription() >= new \DateTime()){
                    return true;
                } else {
                    return false;
                }
        }

        return false;
    }
}
