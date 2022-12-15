<?php

namespace App\Command;

use App\Entity\Etat;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use App\Service\EtatService;
use App\Service\SortieService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:controle-etat',
    description: 'Mise à jour des états des sorties',
    aliases: ['a:c:e'],
    hidden: false
)]
class EtatCommand extends Command {

    protected static $defaultName = 'app:controle-etat';

    private SortieService $sortieService;
    private EtatService $etatService;
    private SortieRepository $sortieRepository;
    private EtatRepository $etatRepository;

    private Etat $cloture;
    private Etat $encours;
    private Etat $passe;
    private Etat $hist;

    public function __construct(SortieService $sortieService, EtatService $etatService,
                                SortieRepository $sortieRepository, EtatRepository $etatRepository)
    {
        $this->sortieService = $sortieService;
        $this->etatService = $etatService;
        $this->sortieRepository = $sortieRepository;
        $this->etatRepository = $etatRepository;
        $this->obtenirEtats();
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        foreach ($this->sortieRepository->findAllControleEtat() as $sortie) {
            if ($this->sortieService->estCloturable($sortie)) {
                $sortie->setEtat($this->cloture);
            } elseif ($this->sortieService->estEnCours($sortie)) {
                $sortie->setEtat($this->encours);
            } elseif ($this->sortieService->estTerminable($sortie)) {
                $sortie->setEtat($this->passe);
            } elseif ($this->sortieService->estHistorisable($sortie)) {
                $sortie->setEtat($this->hist);
            }
            $this->sortieRepository->save($sortie);
        }
        $this->sortieRepository->flush();
        return Command::SUCCESS;
    }

    private function obtenirEtats(): void {
        $this->cloture = $this->obtenirEtat($this->etatService::ETAT_CLOTURE);
        $this->encours = $this->obtenirEtat($this->etatService::ETAT_EN_COURS);
        $this->passe = $this->obtenirEtat($this->etatService::ETAT_PASSE);
        $this->hist = $this->obtenirEtat($this->etatService::ETAT_HISTORISE);
    }

    private function obtenirEtat(string $libelle): Etat {
        return $this->etatRepository->findSelonLibelle($libelle);
    }

}