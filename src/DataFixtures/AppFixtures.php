<?php

namespace App\DataFixtures;

use App\Entity\Groupement;
use App\Entity\Membre;
use App\Entity\Role;
use App\Entity\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Rôles
        $roleAdmin = (new Role())->setNomRole('Admin');
        $roleMembre = (new Role())->setNomRole('Membre simple');
        $manager->persist($roleAdmin);
        $manager->persist($roleMembre);

        // Types de campagne
        $manager->persist((new Type())->setNomType('Tontine'));
        $manager->persist((new Type())->setNomType('Épargne'));

        // Groupement (obligatoire : la relation de Membre n'est pas nullable)
        $groupement = (new Groupement())
            ->setNom('Groupement principal')
            ->setAdresse('Dakar')
            ->setTelephone('770000000');
        $manager->persist($groupement);

        // Administrateur
        $admin = new Membre();
        $admin->setNomComplet('Administrateur')
            ->setTelephone('770000001')
            ->setAdresse('Dakar')
            ->setLogin('admin')
            ->setFonction('Présidente')
            ->setRole($roleAdmin)
            ->setGroupement($groupement);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // Membre simple (pour tester les accès à l'étape sécurité)
        $membre = new Membre();
        $membre->setNomComplet('Membre Test')
            ->setTelephone('770000002')
            ->setAdresse('Dakar')
            ->setLogin('membre')
            ->setFonction('Adjointe')
            ->setRole($roleMembre)
            ->setGroupement($groupement);
        $membre->setPassword($this->hasher->hashPassword($membre, 'membre123'));
        $manager->persist($membre);

        $manager->flush();
    }
}