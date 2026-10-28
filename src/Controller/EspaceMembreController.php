<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Enum\StatutExercice;
use App\Repository\ExerciceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class EspaceMembreController extends AbstractController
{
    #[Route('/mon-espace', name: 'app_espace', methods: ['GET'])]
    public function index(ExerciceRepository $exercices): Response
    {
        $membre = $this->getUser();
        if (!$membre instanceof Membre) {
            throw $this->createAccessDeniedException();
        }

        $affectations = $membre->getModeCotisations();

        $totalFixe = 0;
        foreach ($affectations as $mode) {
            if (!$mode->isLibre()) {
                $totalFixe += $mode->getCampagne()->getMontant();
            }
        }

        return $this->render('espace/index.html.twig', [
            'membre' => $membre,
            'exercice' => $exercices->findOneBy([
                'groupement' => $membre->getGroupement(),
                'statut' => StatutExercice::EN_COURS,
            ]),
            'affectations' => $affectations,
            'totalFixe' => $totalFixe,
        ]);
    }
}