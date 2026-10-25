<?php

namespace App\DataFixtures;

use App\Entity\Campagne;
use App\Entity\Groupement;
use App\Entity\Membre;
use App\Entity\ModeCotisation;
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
        $types = [
            'Tontine' => (new Type())->setNomType('Tontine'),
            'Épargne' => (new Type())->setNomType('Épargne'),
        ];
        foreach ($types as $type) {
            $manager->persist($type);
        }

        // Mots de passe hashés une seule fois (plus rapide pour 24 membres)
        $hashAdmin = $this->hasher->hashPassword(new Membre(), 'admin123');
        $hashMembre = $this->hasher->hashPassword(new Membre(), 'membre123');

        // Groupements et leurs membres : [nomComplet, login, fonction]
        $groupementsData = [
            [
                'nom' => 'Groupement Les Étoiles',
                'adresse' => 'Médina, Dakar',
                'tel' => '338210001',
                'membres' => [
                    ['Awa Diop', 'admin', 'Présidente'],
                    ['Fatou Ndiaye', 'fatou', 'Adjointe'],
                    ['Khady Sow', 'khady', 'Trésorière'],
                    ['Mariama Faye', 'mariama', 'Membre'],
                    ['Rokhaya Gueye', 'rokhaya', 'Membre'],
                    ['Sokhna Thiam', 'sokhna', 'Membre'],
                ],
            ],
            [
                'nom' => 'Groupement Teranga',
                'adresse' => 'Pikine, Dakar',
                'tel' => '338210002',
                'membres' => [
                    ['Mame Fall', 'mame', 'Présidente'],
                    ['Aminata Ba', 'aminata', 'Adjointe'],
                    ['Ndeye Sarr', 'ndeye', 'Trésorière'],
                    ['Coumba Diallo', 'coumba', 'Membre'],
                    ['Binta Cissé', 'binta', 'Membre'],
                    ['Astou Mbaye', 'astou', 'Membre'],
                ],
            ],
            [
                'nom' => 'Groupement Yaakaar',
                'adresse' => 'Thiès Nord, Thiès',
                'tel' => '338210003',
                'membres' => [
                    ['Seynabou Niang', 'seynabou', 'Présidente'],
                    ['Adja Seck', 'adja', 'Adjointe'],
                    ['Penda Lô', 'penda', 'Trésorière'],
                    ['Yacine Sy', 'yacine', 'Membre'],
                    ['Maty Dione', 'maty', 'Membre'],
                    ['Ramatoulaye Badji', 'ramatoulaye', 'Membre'],
                ],
            ],
            [
                'nom' => 'Groupement Jigeen Ñi',
                'adresse' => 'Rufisque, Dakar',
                'tel' => '338210004',
                'membres' => [
                    ['Oumou Sané', 'oumou', 'Présidente'],
                    ['Ndella Thiaw', 'ndella', 'Adjointe'],
                    ['Fama Camara', 'fama', 'Trésorière'],
                    ['Salimata Sonko', 'salimata', 'Membre'],
                    ['Bineta Mendy', 'bineta', 'Membre'],
                    ['Dior Kane', 'dior', 'Membre'],
                ],
            ],
        ];

        // Modèles de campagne : [nom, type, début, fin, montant, libre, indices des membres affectés]
        $modeles = [
            ['Tontine mensuelle', 'Tontine', '2026-09-01', '2027-02-28', 10000, false, [0, 1, 2, 3, 4, 5]],
            ['Tontine de Tabaski', 'Tontine', '2026-10-15', '2027-05-15', 25000, false, [0, 1, 2, 3]],
            ['Épargne solidaire', 'Épargne', '2026-09-01', '2027-08-31', 5000, true, [0, 1, 2, 3, 4, 5]],
            ['Épargne scolaire', 'Épargne', '2026-10-01', '2027-06-30', 15000, true, [0, 2, 4, 5]],
        ];

        $compteurTel = 1000001;

        foreach ($groupementsData as $g => $data) {
            $groupement = (new Groupement())
                ->setNom($data['nom'])
                ->setAdresse($data['adresse'])
                ->setTelephone($data['tel']);
            $manager->persist($groupement);

            // Membres (la présidente est Admin, les autres sont membres simples)
            $membres = [];
            foreach ($data['membres'] as [$nomComplet, $login, $fonction]) {
                $estAdmin = $fonction === 'Présidente';

                $membre = new Membre();
                $membre->setNomComplet($nomComplet)
                    ->setTelephone('77' . $compteurTel++)
                    ->setAdresse($data['adresse'])
                    ->setLogin($login)
                    ->setFonction($fonction)
                    ->setRole($estAdmin ? $roleAdmin : $roleMembre)
                    ->setGroupement($groupement)
                    ->setPassword($estAdmin ? $hashAdmin : $hashMembre);
                $manager->persist($membre);
                $membres[] = $membre;
            }

            // Campagnes du groupement et affectation des membres
            foreach ($modeles as [$nom, $typeNom, $debut, $fin, $montant, $libre, $indices]) {
                $campagne = (new Campagne())
                    ->setNom($nom . ' - ' . $data['nom'])
                    ->setDateDebut(new \DateTimeImmutable($debut))
                    ->setDateFin(new \DateTimeImmutable($fin))
                    ->setMontant($montant * ($g % 2 === 0 ? 1 : 2))
                    ->setType($types[$typeNom])
                    ->setGroupement($groupement);
                $manager->persist($campagne);

                foreach ($indices as $i) {
                    $mode = (new ModeCotisation())
                        ->setLibre($libre)
                        ->setCampagne($campagne)
                        ->setMembre($membres[$i]);
                    $manager->persist($mode);
                }
            }
        }

        $manager->flush();
    }
}