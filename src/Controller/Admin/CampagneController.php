<?php

namespace App\Controller\Admin;

use App\Entity\Campagne;
use App\Enum\StatutExercice;
use App\Form\CampagneType;
use App\Repository\CampagneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/campagnes')]
#[IsGranted('ROLE_ADMIN')]
class CampagneController extends AbstractController
{
    #[Route('', name: 'admin_campagne_index', methods: ['GET'])]
    public function index(CampagneRepository $repository): Response
    {
        return $this->render('admin/campagne/index.html.twig', [
            'campagnes' => $repository->findBy([], ['dateDebut' => 'DESC']),
        ]);
    }

    #[Route('/nouvelle', name: 'admin_campagne_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $campagne = new Campagne();
        $form = $this->createForm(CampagneType::class, $campagne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($erreur = $this->verifier($campagne)) {
                $form->addError(new FormError($erreur));
            } else {
                $em->persist($campagne);
                $em->flush();
                $this->addFlash('success', 'Campagne créée.');

                return $this->redirectToRoute('admin_campagne_index');
            }
        }

        return $this->render('admin/campagne/form.html.twig', ['form' => $form, 'titre' => 'Nouvelle campagne']);
    }

    #[Route('/{id}/modifier', name: 'admin_campagne_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Campagne $campagne, Request $request, EntityManagerInterface $em): Response
    {
        if ($campagne->getExercice()?->getStatut() === StatutExercice::TERMINE) {
            $this->addFlash('danger', "Modification impossible : l'exercice de cette campagne est terminé.");

            return $this->redirectToRoute('admin_campagne_index');
        }

        $form = $this->createForm(CampagneType::class, $campagne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($erreur = $this->verifier($campagne)) {
                $form->addError(new FormError($erreur));
            } else {
                $em->flush();
                $this->addFlash('success', 'Campagne modifiée.');

                return $this->redirectToRoute('admin_campagne_index');
            }
        }

        return $this->render('admin/campagne/form.html.twig', ['form' => $form, 'titre' => 'Modifier la campagne']);
    }

    #[Route('/{id}/supprimer', name: 'admin_campagne_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Campagne $campagne, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $campagne->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton de sécurité invalide.');
        } elseif (!$campagne->getModeCotisations()->isEmpty()) {
            $this->addFlash('danger', 'Suppression impossible : des membres sont affectés à cette campagne.');
        } else {
            $em->remove($campagne);
            $em->flush();
            $this->addFlash('success', 'Campagne supprimée.');
        }

        return $this->redirectToRoute('admin_campagne_index');
    }

    private function verifier(Campagne $campagne): ?string
    {
        $exercice = $campagne->getExercice();

        if ($exercice->getGroupement() !== $campagne->getGroupement()) {
            return "L'exercice choisi n'appartient pas à ce groupement.";
        }
        if ($campagne->getDateDebut() >= $campagne->getDateFin()) {
            return 'La date de début doit précéder la date de fin.';
        }
        if ($campagne->getDateDebut() < $exercice->getDateDebut() || $campagne->getDateFin() > $exercice->getDateFin()) {
            return sprintf(
                "Les dates doivent rester dans l'exercice (du %s au %s).",
                $exercice->getDateDebut()->format('d/m/Y'),
                $exercice->getDateFin()->format('d/m/Y'),
            );
        }

        return null;
    }
}