<?php

namespace App\Command;

use App\Entity\Etat;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use App\Service\SortieService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:controle-etat',
    description: 'Mise à jour des états des sorties',
    aliases: ['app:maj-etat'],
    hidden: false
)]
class EtatCommand extends Command {

    protected static $defaultName = 'app:controle-etat';

    private SortieService $service;
    private SortieRepository $sortieRepository;
    private EtatRepository $etatRepository;
    private array $etats;

    public function __construct(SortieService $service, SortieRepository $sortieRepository,
                                EtatRepository $etatRepository)
    {
        $this->service = $service;
        $this->sortieRepository = $sortieRepository;
        $this->etatRepository = $etatRepository;
        $this->etats = $this->etatRepository->findEtatsAControles();
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $output->writeln([
            'Début programme'
        ]);
        foreach ($this->sortieRepository->findAllOrder() as $sortie) {
            if ($this->service->estCloturable($sortie)) {
                $sortie->setEtat($this->obtenirNouvelEtat(1));
                var_dump($sortie->getEtat());
            } elseif ($this->service->estEnCours($sortie)) {
                $sortie->setEtat($this->obtenirNouvelEtat(2));
                var_dump($sortie->getEtat());
            } elseif ($this->service->estTerminable($sortie)) {
                $sortie->setEtat($this->obtenirNouvelEtat(3));
                var_dump($sortie->getEtat());
            } elseif ($this->service->estHistorisable($sortie)) {
                $sortie->setEtat($this->obtenirNouvelEtat(4));
                var_dump($sortie->getEtat());
            }
            $this->sortieRepository->save($sortie, true);
        }
        return Command::SUCCESS;
    }

    private function obtenirNouvelEtat(int $key): Etat {
        var_dump($this->etats[$key]);
        return $this->etats[$key];
    }

}