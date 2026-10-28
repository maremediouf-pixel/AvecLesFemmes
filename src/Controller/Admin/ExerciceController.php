<?php

namespace App\Controller\Admin;

use App\Entity\Exercice;
use App\Enum\StatutExercice;
use App\Form\ExerciceType;
use App\Repository\ExerciceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/exercices')]
#[IsGranted('ROLE_ADMIN')]
class ExerciceController extends AbstractController
{
    #[Route('', name: 'admin_exercice_index', methods: ['GET'])]
    public function index(ExerciceRepository $repository): Response
    {
        return $this->render('admin/exercice/index.html.twig', [
            'exercices' => $repository->findBy([], ['dateDebut' => 'DESC']),
        ]);
    }

    #[Route('/nouveau', name: 'admin_exercice_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, ExerciceRepository $repository): Response
    {
        $form = $this->createForm(ExerciceType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $d = $form->getData();

            if ($erreur = $this->verifierDates($d)) {
                $form->addError(new FormError($erreur));
            } elseif ($repository->findOneBy(['groupement' => $d['groupement'], 'statut' => StatutExercice::EN_COURS])) {
                $form->addError(new FormError('Ce groupement a déjà un exercice en cours. Terminez-le avant d\'en ouvrir un autre.'));
            } else {
                $exercice = new Exercice(
                    \DateTime::createFromInterface($d['dateDebut']),
                    \DateTime::createFromInterface($d['dateFin']),
                    \DateTime::createFromInterface($d['dateLimitePrets']),
                    \DateTime::createFromInterface($d['dateRetribution']),
                );
                $exercice->setGroupement($d['groupement']);
                $em->persist($exercice);
                $em->flush();
                $this->addFlash('success', 'Exercice ouvert.');

                return $this->redirectToRoute('admin_exercice_index');
            }
        }

        return $this->render('admin/exercice/form.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/terminer', name: 'admin_exercice_close', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function close(Exercice $exercice, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('close' . $exercice->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton de sécurité invalide.');
        } elseif ($exercice->getStatut() === StatutExercice::TERMINE) {
            $this->addFlash('danger', 'Cet exercice est déjà terminé.');
        } else {
            $exercice->setStatut(StatutExercice::TERMINE);
            $em->flush();
            $this->addFlash('success', 'Exercice terminé.');
        }

        return $this->redirectToRoute('admin_exercice_index');
    }

    #[Route('/{id}/supprimer', name: 'admin_exercice_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Exercice $exercice, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $exercice->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton de sécurité invalide.');
        } elseif (!$exercice->getCampagnes()->isEmpty()) {
            $this->addFlash('danger', 'Suppression impossible : cet exercice contient des campagnes.');
        } else {
            $em->remove($exercice);
            $em->flush();
            $this->addFlash('success', 'Exercice supprimé.');
        }

        return $this->redirectToRoute('admin_exercice_index');
    }

    private function verifierDates(array $d): ?string
    {
        if ($d['dateDebut'] >= $d['dateFin']) {
            return 'La date de début doit précéder la date de fin.';
        }
        if ($d['dateLimitePrets'] < $d['dateDebut'] || $d['dateLimitePrets'] > $d['dateFin']) {
            return 'La date limite des prêts doit être comprise entre le début et la fin de l\'exercice.';
        }
        if ($d['dateRetribution'] < $d['dateLimitePrets']) {
            return 'La date de rétribution ne peut pas précéder la date limite des prêts.';
        }

        return null;
    }
}