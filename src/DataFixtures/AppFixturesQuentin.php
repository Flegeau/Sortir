<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
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
        $this->ajouterEtats();
        $this->ajouterCampus(10);
        $this->ajouterVilles(30);
        $this->ajouterLieus(50);
        $this->ajouterParticipants(120);
        $this->ajouterSorties(20);
    }

    public function ajouterEtats(): void
    {
        $libelles = array('Créée', 'Ouverte', 'Clôturée', 'Activité en cours', 'Passée', 'Annulée', 'Historisée');

        for ($i = 0; $i < count($libelles); $i++)
        {
            $etat = new Etat();
            $etat->setLibelle($libelles[$i]);
            $this->manager->persist($etat);
        }
        $this->manager->flush();
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

    public function ajouterVilles(int $nb): void
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

    public function ajouterLieus(int $nb): void
    {
        $villes = $this->manager->getRepository(Ville::class)->findAll();

        for ($i = 0; $i < $nb; $i++)
        {
            $lieu = new Lieu();
            $lieu->setNom($this->faker->company);
            $lieu->setRue($this->faker->streetAddress);
            $lieu->setLatitude($this->faker->latitude);
            $lieu->setLongitude($this->faker->longitude);
            $lieu->setVille($this->faker->randomElement($villes));
            $this->manager->persist($lieu);
        }
        $this->manager->flush();
    }

    public function ajouterParticipants(int $nb): void
    {
        $campus = $this->manager->getRepository(Campus::class)->findAll();

        for ($i = 0; $i < $nb; $i++)
        {
            $participant = new Participant();
            $participant->setNom($this->faker->lastName);
            $participant->setPrenom($this->faker->firstName);
            $participant->setTelephone($this->faker->phoneNumber);
            $participant->setEmail($this->faker->email);
            //var_dump($participant->getEmail());
            $participant->setPseudo($this->faker->userName);
            //var_dump($participant->getPseudo());
            $goodPassword = $this->faker->password(8, 20);
            //var_dump($goodPassword);
            //$participant->setPassword($this->hasher->hashPassword($participant, $goodPassword));
            $participant->setPassword($goodPassword);
            $participant->setActif($this->faker->boolean(99));
            $participant->setCampus($this->faker->randomElement($campus));
            $this->manager->persist($participant);
        }
        $this->manager->flush();
    }

    public function ajouterSorties(int $nb): void
    {
        $etats = $this->manager->getRepository(Etat::class)->findAll();
        $lieus = $this->manager->getRepository(Lieu::class)->findAll();
        $campus = $this->manager->getRepository(Campus::class)->findAll();
        $durees = array(60, 120, 180, 240, 300, 360);
        $lorem = "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression. 
        Le Lorem Ipsum est le faux texte standard de l'imprimerie depuis les années 1500, quand un imprimeur anonyme assembla ensemble des morceaux de texte pour réaliser un livre spécimen de polices de texte. 
        Il n'a pas fait que survivre cinq siècles, mais s'est aussi adapté à la bureautique informatique, sans que son contenu n'en soit modifié. 
        Il a été popularisé dans les années 1960 grâce à la vente de feuilles Letraset contenant des passages du Lorem Ipsum, et, plus récemment, par son inclusion dans des applications de mise en page de texte, comme Aldus PageMaker.";

        for ($i = 0; $i < $nb; $i++)
        {
            $participants = $this->manager->getRepository(Participant::class)->findAll();
            $indexOrganisateur = (int)$this->faker->randomKey($participants);
            $organisateur = $participants[$indexOrganisateur];
            unset($participants[$indexOrganisateur]);

            $sortie = new Sortie();
            $sortie->setNom($this->faker->name);
            $sortie->setDateHeureDebut($this->faker->dateTimeBetween("+3 months", "+8 months"));
            $sortie->setDuree($this->faker->randomElement($durees));
            $sortie->setDateLimiteInscription($this->faker->dateTimeBetween("now", "+2 months"));
            $nbP = $this->faker->numberBetween(2, 20);
            $sortie->setNbInscriptionsMax($nbP);
            $sortie->setInfoSortie($lorem);
            $sortie->setEtat($this->faker->randomElement($etats));
            $sortie->setLieu($this->faker->randomElement($lieus));
            $sortie->setCampus($this->faker->randomElement($campus));
            $sortie->setOrganisateur($organisateur);
            $sortie->addParticipant($organisateur);
            for ($ii = 1; $ii < $this->faker->numberBetween(1, $nbP); $ii++)
            {
                $sortie->addParticipant($this->faker->randomElement($participants));
            }
            $this->manager->persist($sortie);
        }
        $this->manager->flush();
    }

}
