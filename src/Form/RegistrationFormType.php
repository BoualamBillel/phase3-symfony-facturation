<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'attr' => ['placeholder' => '...'],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez renseigner votre prénom.',
                    ),
                    new Length(
                        max: 255,
                        maxMessage: 'Le prénom saisi est trop long.',
                    ),
                ],
            ])
            ->add('lastName', TextType::class, [
                'attr' => ['placeholder' => '...'],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez renseigner votre nom.',
                    ),
                    new Length(
                        max: 255,
                        maxMessage: 'Le nom saisi est trop long.',
                    ),
                ],
            ])
            ->add('companyName', TextType::class, [
                'attr' => ['placeholder' => 'Tech Solutions'],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez renseigner votre raison sociale.',
                    ),
                    new Length(
                        max: 255,
                        maxMessage: 'La raison sociale saisie est trop longue.',
                    ),
                ],
            ])
            ->add('iban', TextType::class, [
                'attr' => ['placeholder' => 'FR76 1234 5678 9012 3456 7890 123'],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez renseigner l\'IBAN de votre entreprise.',
                    ),
                    new Iban(
                        message: 'Le format de cet IBAN est invalide. Veuillez vérifier votre saisie.',
                    ),
                ],
            ])
            ->add('email', EmailType::class, [
                'attr' => ['placeholder' => 'contact@entreprise.fr'],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez renseigner votre adresse email.',
                    ),
                    new Email(
                        message: 'L\'adresse email saisie n\'est pas valide.',
                    ),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // mapped => false signifie que ce champ n'est pas lié directement 
                // à une propriété de l'entité User en base de données.
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => '••••••••'
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez choisir un mot de passe.',
                    ),
                    new Length(
                        min: 6,
                        minMessage: 'Votre mot de passe doit faire au moins {{ limit }} caractères.',
                        // max length allowed by Symfony for security reasons
                        max: 4096,
                    ),
                ],
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