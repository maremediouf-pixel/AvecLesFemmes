<?php

namespace App\Form;

use App\Entity\Groupement;
use App\Entity\Membre;
use App\Entity\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class MembreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomComplet', TextType::class, [
                'label' => 'Nom complet',
                'constraints' => [new NotBlank(message: 'Le nom est obligatoire.'), new Length(max: 100)],
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'constraints' => [new NotBlank(message: 'Le téléphone est obligatoire.'), new Length(max: 100)],
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'constraints' => [new NotBlank(message: "L'adresse est obligatoire."), new Length(max: 100)],
            ])
            ->add('login', TextType::class, [
                'label' => 'Login',
                'constraints' => [new NotBlank(message: 'Le login est obligatoire.'), new Length(max: 100)],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => $options['is_new'] ? 'Mot de passe' : 'Nouveau mot de passe (laisser vide pour ne pas changer)',
                'mapped' => false,
                'required' => $options['is_new'],
                'constraints' => $options['is_new']
                    ? [new NotBlank(message: 'Le mot de passe est obligatoire.'), new Length(min: 6, minMessage: '6 caractères minimum.')]
                    : [new Length(min: 6, minMessage: '6 caractères minimum.')],
            ])
            ->add('fonction', TextType::class, [
                'label' => 'Fonction',
                'constraints' => [new NotBlank(message: 'La fonction est obligatoire.'), new Length(max: 50)],
            ])
            ->add('role', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'nomRole',
                'label' => 'Rôle',
                'placeholder' => 'Choisir un rôle',
            ])
            ->add('groupement', EntityType::class, [
                'class' => Groupement::class,
                'choice_label' => 'nom',
                'label' => 'Groupement',
                'placeholder' => 'Choisir un groupement',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Membre::class,
            'is_new' => true,
        ]);
    }
}