<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\VilleRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class AppFixturesQuentin extends Fixture
{
    private ObjectManager $manager;
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->ajouterCampus(5);
        $this->ajouterVille(20);
        $this->ajouterLieu(50);
        $this->ajouterEtat();
        //$this->ajouterParticipant(60);
        //$this->ajouterSortie(50);
    }

    public function ajouterCampus(int $nb): void
    {
        for ($i = 0; $i < $nb; $i++)
        {
            $campus = new Campus();
            $campus->setNom($this->faker->company);
            $this->manager->persist($campus);
        }
        $this->manager->flush();
    }

    public function ajouterVille(int $nb): void
    {
        for ($i = 0; $i < $nb; $i++)
        {
            $ville = new Ville();
            $ville->setNom($this->faker->city);
            $ville->setCodePostal($this->faker->postcode);
            $this->manager->persist($ville);
        }
        $this->manager->flush();
    }

    public function ajouterLieu(int $nb): void
    {
        $villes = $this->manager->getRepository(VilleRepository::class)->findAll();
        for ($i = 0; $i < $nb; $i++)
        {
            $lieu = new Lieu();
            $lieu->setNom($this->faker->text(120));
            $lieu->setRue($this->faker->streetAddress);
            $lieu->setLatitude($this->faker->latitude);
            $lieu->setLongitude($this->faker->longitude);
            $lieu->setVille($this->faker->randomElement($villes));
            $this->manager->persist($lieu);
        }
        $this->manager->flush();
    }

    public function ajouterEtat(): void
    {
        $libelles = ['Créée', 'Ouverte', 'Clôturée', 'Activité en cours', 'Passée', 'Annulée', 'Historisée'];
        for ($i = 0; $i < count($libelles); $i++)
        {
            $etat = new Etat();
            $etat->setLibelle($libelles[$i]);
            $this->manager->persist($etat);
        }
        $this->manager->flush();
    }

    public function ajouterParticipant(int $nb): void
    {
        for ($i = 0; $i < $nb; $i++)
        {
            $participant = new Participant();
        }
        $this->manager->flush();
    }

    public function ajouterSortie(int $nb): void
    {
        for ($i = 0; $i < $nb; $i++)
        {
            $sortie = new Sortie();
        }
        $this->manager->flush();
    }

}
