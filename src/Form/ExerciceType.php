<?php

namespace App\Form;

use App\Entity\Groupement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ExerciceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dates = [
            'dateDebut' => 'Date de début',
            'dateFin' => 'Date de fin',
            'dateLimitePrets' => 'Date limite des prêts',
            'dateRetribution' => 'Date de rétribution',
        ];

        $builder->add('groupement', EntityType::class, [
            'class' => Groupement::class,
            'choice_label' => 'nom',
            'label' => 'Groupement',
            'placeholder' => 'Choisir un groupement',
            'constraints' => [new NotBlank(message: 'Choisissez un groupement.')],
        ]);

        foreach ($dates as $champ => $label) {
            $builder->add($champ, DateType::class, [
                'widget' => 'single_text',
                'label' => $label,
                'constraints' => [new NotBlank(message: 'Cette date est obligatoire.')],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => null]);
    }
}
