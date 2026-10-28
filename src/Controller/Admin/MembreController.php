<?php

namespace App\Controller\Admin;

use App\Entity\Membre;
use App\Form\MembreType;
use App\Repository\MembreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/membres')]
#[IsGranted('ROLE_ADMIN')]
class MembreController extends AbstractController
{
    #[Route('', name: 'admin_membre_index', methods: ['GET'])]
    public function index(MembreRepository $repository): Response
    {
        return $this->render('admin/membre/index.html.twig', [
            'membres' => $repository->findBy([], ['nomComplet' => 'ASC']),
        ]);
    }

    #[Route('/nouveau', name: 'admin_membre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $membre = new Membre();
        $form = $this->createForm(MembreType::class, $membre, ['is_new' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $membre->setPassword($hasher->hashPassword($membre, $form->get('plainPassword')->getData()));
            $em->persist($membre);
            $em->flush();
            $this->addFlash('success', 'Membre créé.');

            return $this->redirectToRoute('admin_membre_index');
        }

        return $this->render('admin/membre/form.html.twig', ['form' => $form, 'titre' => 'Nouveau membre']);
    }

    #[Route('/{id}/modifier', name: 'admin_membre_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Membre $membre, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $form = $this->createForm(MembreType::class, $membre, ['is_new' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = $form->get('plainPassword')->getData();
            if ($plain) {
                $membre->setPassword($hasher->hashPassword($membre, $plain));
            }
            $em->flush();
            $this->addFlash('success', 'Membre modifié.');

            return $this->redirectToRoute('admin_membre_index');
        }

        return $this->render('admin/membre/form.html.twig', ['form' => $form, 'titre' => 'Modifier le membre']);
    }

    #[Route('/{id}/supprimer', name: 'admin_membre_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Membre $membre, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $membre->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton de sécurité invalide.');

            return $this->redirectToRoute('admin_membre_index');
        }

        if ($membre === $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer votre propre compte.');

            return $this->redirectToRoute('admin_membre_index');
        }

        if (!$membre->getModeCotisations()->isEmpty()) {
            $this->addFlash('danger', 'Suppression impossible : ce membre participe à des campagnes.');

            return $this->redirectToRoute('admin_membre_index');
        }

        $em->remove($membre);
        $em->flush();
        $this->addFlash('success', 'Membre supprimé.');

        return $this->redirectToRoute('admin_membre_index');
    }
}