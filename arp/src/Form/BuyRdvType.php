<?php

namespace App\Form;

use App\Entity\BookedRdv;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;

class BuyRdvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('clientSurname', TextType::class, [
                'label' => 'Nom ou pseudonyme',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez indiquer un nom ou pseudonyme']),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Votre nom ou un pseudonyme'
                ]
            ])
            ->add('beginAt', DateTimeType::class, [
                'label' => 'Date et heure du rendez-vous',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner une date et heure']),
                    new GreaterThan([
                        'value' => 'now',
                        'message' => 'La date doit être dans le futur'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control flatpickr-datetime',
                    'placeholder' => 'Sélectionnez une date et heure'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BookedRdv::class,
        ]);
    }
}