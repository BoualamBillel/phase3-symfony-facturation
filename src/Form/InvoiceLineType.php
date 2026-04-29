<?php

namespace App\Form;

use App\Entity\InvoiceLine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class InvoiceLineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('productName', TextType::class, [
                'label' => 'Prestation',
                'constraints' => [new NotBlank(['message' => 'Veuillez saisir un nom.'])]
            ])
            ->add('unitPrice', NumberType::class, [
                'label' => 'Prix unitaire (HT)',
                'scale' => 2,
                'html5' => true,
                'attr' => ['step' => '0.01', 'min' => '0'],
                'constraints' => [
                    new NotBlank(), 
                    new PositiveOrZero(['message' => 'Le prix ne peut pas être négatif.'])
                ]
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Quantité',
                'data' => 1, 
                'attr' => ['min' => '1'],
                'constraints' => [
                    new NotBlank(), 
                    new Positive(['message' => 'La quantité doit être supérieure à 0.'])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InvoiceLine::class,
        ]);
    }
}