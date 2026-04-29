<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new NotBlank(message: 'Le nom du produit est obligatoire.')
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix (€)',
                'scale' => 2,
                'html5' => true,
                'attr' => [
                    'step' => "0.01",
                    "min" => '0',
                ]
            ])
            ->add('unit', ChoiceType::class, [
                'label' => 'Unité',
                'choices' => [
                    'Pièce' => 'piece',
                    'Heure' => 'hour',
                    'Jour' => 'day',
                    'Mois' => 'month',
                    'Année' => 'year',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
