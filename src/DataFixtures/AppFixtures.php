<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private ObjectManager $manager;
    private UserPasswordHasherInterface $hasher;
   // private Generatorrator $generator;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
        //$this->generator = Factory::create('fr_FR');
    }


    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        // $product = new Product();
        // $manager->persist($product);

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
}
