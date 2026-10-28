<?php

namespace App\Controller\Admin;

use App\Entity\Groupement;
use App\Form\GroupementType;
use App\Repository\GroupementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/groupements')]
#[IsGranted('ROLE_ADMIN')]
class GroupementController extends AbstractController
{
    #[Route('', name: 'admin_groupement_index', methods: ['GET'])]
    public function index(GroupementRepository $repository): Response
    {
        return $this->render('admin/groupement/index.html.twig', [
            'groupements' => $repository->findBy([], ['nom' => 'ASC']),
        ]);
    }

    #[Route('/nouveau', name: 'admin_groupement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $groupement = new Groupement();
        $form = $this->createForm(GroupementType::class, $groupement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($groupement);
            $em->flush();
            $this->addFlash('success', 'Groupement créé.');

            return $this->redirectToRoute('admin_groupement_index');
        }

        return $this->render('admin/groupement/form.html.twig', [
            'form' => $form,
            'titre' => 'Nouveau groupement',
        ]);
    }

    #[Route('/{id}/modifier', name: 'admin_groupement_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Groupement $groupement, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(GroupementType::class, $groupement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Groupement modifié.');

            return $this->redirectToRoute('admin_groupement_index');
        }

        return $this->render('admin/groupement/form.html.twig', [
            'form' => $form,
            'titre' => 'Modifier le groupement',
        ]);
    }

    #[Route('/{id}/supprimer', name: 'admin_groupement_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Groupement $groupement, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $groupement->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton de sécurité invalide.');

            return $this->redirectToRoute('admin_groupement_index');
        }

        if (!$groupement->getMembres()->isEmpty()
            || !$groupement->getCampagnes()->isEmpty()
            || !$groupement->getExercices()->isEmpty()) {
            $this->addFlash('danger', 'Suppression impossible : ce groupement a des membres, campagnes ou exercices.');

            return $this->redirectToRoute('admin_groupement_index');
        }

        $em->remove($groupement);
        $em->flush();
        $this->addFlash('success', 'Groupement supprimé.');

        return $this->redirectToRoute('admin_groupement_index');
    }
}