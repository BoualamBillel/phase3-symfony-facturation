<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyName', TextType::class, [
                'label' => 'Raison Sociale',
                'required' => true,
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(
                        message: 'La raison sociale est obligatoire.'
                    ),
                    new Length(
                        max : 255,
                        maxMessage : 'La raison sociale ne peut pas dépasser {{ limit }} caractères.'
                    )
                ]
            ])
            ->add('siret', TextType::class, [
                'label' => 'Numéro SIRET (Optionnel)',
                'required' => false,
                'constraints' => [
                    new Regex(
                        pattern : '/^[0-9]{14}$/',
                        message : 'Le SIRET doit contenir exactement 14 chiffres (sans espaces).'
                    )
                ]
            ])
            ->add('iban', TextType::class, [
                'label' => 'IBAN',
                'required' => false,
                'constraints' => [
                    new Iban(
                        message: 'Le format de cet IBAN est invalide. Veuillez vérifier votre saisie.',
                    ),
                ]
            ])
            ->add('cgv', TextareaType::class, [
                'label' => 'Conditions Générales de Vente (CGV)',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Ces conditions apparaîtront en bas de vos factures PDF...'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
