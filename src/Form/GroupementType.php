<?php

namespace App\Form;

use App\Entity\Groupement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class GroupementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du groupement',
                'constraints' => [new NotBlank(message: 'Le nom est obligatoire.'), new Length(max: 100)],
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'constraints' => [new NotBlank(message: "L'adresse est obligatoire."), new Length(max: 100)],
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'constraints' => [new NotBlank(message: 'Le téléphone est obligatoire.'), new Length(max: 100)],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Groupement::class]);
    }
}