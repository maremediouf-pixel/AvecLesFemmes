<?php

namespace App\Controller\Admin;

use App\Entity\Campagne;
use App\Entity\Membre;
use App\Entity\ModeCotisation;
use App\Enum\StatutExercice;
use App\Repository\MembreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\Count;

#[Route('/admin/campagnes')]
#[IsGranted('ROLE_ADMIN')]
class AffectationController extends AbstractController
{
    #[Route('/{id}/membres', name: 'admin_affectation_index', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function index(Campagne $campagne, Request $request, EntityManagerInterface $em): Response
    {
        $modifiable = $campagne->getExercice()->getStatut() === StatutExercice::EN_COURS;

        $dejaAffectes = [];
        foreach ($campagne->getModeCotisations() as $mode) {
            $dejaAffectes[] = $mode->getMembre()->getId();
        }

        $form = null;
        if ($modifiable) {
            $form = $this->createFormBuilder()
                ->add('membres', EntityType::class, [
                    'class' => Membre::class,
                    'multiple' => true,
                    'expanded' => true,
                    'label' => 'Membres à affecter',
                    'choice_label' => fn (Membre $m) => $m->getNomComplet() . ' (' . $m->getFonction() . ')',
                    'query_builder' => function (MembreRepository $r) use ($campagne, $dejaAffectes) {
                        $qb = $r->createQueryBuilder('m')
                            ->andWhere('m.groupement = :groupement')
                            ->setParameter('groupement', $campagne->getGroupement())
                            ->orderBy('m.nomComplet', 'ASC');
                        if ($dejaAffectes) {
                            $qb->andWhere('m.id NOT IN (:deja)')->setParameter('deja', $dejaAffectes);
                        }

                        return $qb;
                    },
                    'constraints' => [new Count(min: 1, minMessage: 'Choisissez au moins un membre.')],
                ])
                ->add('libre', CheckboxType::class, [
                    'required' => false,
                    'label' => 'Cotisation libre (le membre choisit son montant)',
                ])
                ->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $libre = (bool) $form->get('libre')->getData();
                foreach ($form->get('membres')->getData() as $membre) {
                    $mode = (new ModeCotisation())
                        ->setLibre($libre)
                        ->setCampagne($campagne)
                        ->setMembre($membre);
                    $em->persist($mode);
                }
                $em->flush();
                $this->addFlash('success', 'Membres affectés à la campagne.');

                return $this->redirectToRoute('admin_affectation_index', ['id' => $campagne->getId()]);
            }
        }

        return $this->render('admin/affectation/index.html.twig', [
            'campagne' => $campagne,
            'modifiable' => $modifiable,
            'form' => $form,
        ]);
    }

    #[Route('/affectations/{id}/retirer', name: 'admin_affectation_remove', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function remove(ModeCotisation $mode, Request $request, EntityManagerInterface $em): Response
    {
        $campagne = $mode->getCampagne();

        if (!$this->isCsrfTokenValid('retirer' . $mode->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton de sécurité invalide.');
        } elseif ($campagne->getExercice()->getStatut() !== StatutExercice::EN_COURS) {
            $this->addFlash('danger', "Modification impossible : l'exercice est terminé.");
        } else {
            $em->remove($mode);
            $em->flush();
            $this->addFlash('success', 'Membre retiré de la campagne.');
        }

        return $this->redirectToRoute('admin_affectation_index', ['id' => $campagne->getId()]);
    }
}