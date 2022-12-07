<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private ObjectManager $manager;
    private UserPasswordHasherInterface $hasher;
    private Generator $generator;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
        $this->generator = Factory::create('fr_FR');
    }


    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        // $product = new Product();
        // $manager->persist($product);

        $this->addUser();
        $this->addUser();
    }

    public function addUser(){

        $campus = new Campus();
        $campus->setNom("Quimper");

        $this->manager->persist($campus);

        $user = new Participant();
        $user ->setCampus($campus)
                ->setNom("toto")
                ->setPrenom("tata")
                ->setEmail("toto@tata.fr")
                ->setPseudo("tutu29")
                ->setActif(true)
                ->setRoles(["ROLE_USER"])
                ->setTelephone("0238647892");


        $user->setPassword($this->hasher->hashPassword($user, "123456"));

        $this->manager->persist($user);
        $this->manager->flush($user);
    }

    public function addSortie(){
        for ($i = 0; $i < 10; $i++){
            $sortie = new Sortie();
            $sortie->setNom($this->generator->name)
                ->setDateHeureDebut($this->generator->dateTimeBetween("+2 days", "+2 months"))
                ->setDuree($this->generator->numberBetween(0, 1440))
                ->setDateLimiteInscription($this->generator->dateTimeBetween( "-2 months","-2 days"))
                ->setNbInscriptionsMax($this->generator->numberBetween(0, 10))
                ->setInfoSortie($this->generator->sentence)
                ->setMotif($this->generator->slug)
                ->setEtat($this->generator->)
                ->setLieu($this->generator->)
                ->setCampus($this->generator->)
                ->addParticipant($this->generator->);

            $this->manager->persist($sortie);
        }

        $this->manager->flush();
    }
}
