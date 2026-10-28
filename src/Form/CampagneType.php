<?php

namespace App\Form;

use App\Entity\Campagne;
use App\Entity\Exercice;
use App\Entity\Groupement;
use App\Entity\Type;
use App\Enum\StatutExercice;
use App\Repository\ExerciceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class CampagneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la campagne',
                'constraints' => [new NotBlank(message: 'Le nom est obligatoire.'), new Length(max: 100)],
            ])
            ->add('type', EntityType::class, [
                'class' => Type::class,
                'choice_label' => 'nomType',
                'label' => 'Type',
                'placeholder' => 'Choisir un type',
            ])
            ->add('groupement', EntityType::class, [
                'class' => Groupement::class,
                'choice_label' => 'nom',
                'label' => 'Groupement',
                'placeholder' => 'Choisir un groupement',
            ])
            ->add('exercice', EntityType::class, [
                'class' => Exercice::class,
                'label' => 'Exercice (en cours)',
                'placeholder' => 'Choisir un exercice',
                'query_builder' => fn (ExerciceRepository $r) => $r->createQueryBuilder('e')
                    ->andWhere('e.statut = :statut')
                    ->setParameter('statut', StatutExercice::EN_COURS)
                    ->orderBy('e.dateDebut', 'DESC'),
                'choice_label' => fn (Exercice $e) => sprintf(
                    '%s : %s → %s',
                    $e->getGroupement()->getNom(),
                    $e->getDateDebut()->format('d/m/Y'),
                    $e->getDateFin()->format('d/m/Y'),
                ),
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début',
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin',
            ])
            ->add('montant', IntegerType::class, [
                'label' => 'Montant (FCFA)',
                'constraints' => [new NotBlank(message: 'Le montant est obligatoire.'), new Positive(message: 'Le montant doit être supérieur à 0.')],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Campagne::class]);
    }
}