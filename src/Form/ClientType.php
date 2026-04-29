<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du client / Entreprise',
                'constraints' => [
                    new NotBlank(
                        message: 'Le nom du Client / Entreprise est obligatoire.'
                    ),
                    new Length(
                        max: 255,
                        maxMessage: 'Le nom du Client / Entreprise ne peut pas dépasser {{ limit }} caractères.'
                    )
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints'=> [
                    new NotBlank(
                        message: "L'E-Mail du Client / Entreprise est obligatoire."
                    ),
                    new Length(
                        max: 255,
                        maxMessage: 'L\'email ne peut pas dépasser {{ limit }} caractères.'
                    )
                ]
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'constraints' => [
                    new Length(
                        max: 10,
                        min: 10,
                        minMessage: 'Le numéro de téléphone doit comporter exactement {{ limit }} caractères.',
                        maxMessage: 'Le numéro de téléphone ne peut pas dépasser {{ limit }} caractères.'
                    )
                ]
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Adresse',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                ],
            ])
            ->add('siret', NumberType::class, [
                'label' => 'SIRET (Optionnel)',
                'required'=> false,
            ])
            ->add('rib', NumberType::class, [
                'label' => 'RIB (Optionnel)',
                'required'=> false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
