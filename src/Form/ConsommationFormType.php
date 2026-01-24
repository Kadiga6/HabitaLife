<?php
// ConsommationFormType.php
namespace App\Form;

use App\Entity\Consommation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ConsommationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Type de consommation
            ->add('type', ChoiceType::class, [
                'label' => 'Type de consommation',
                'placeholder' => '-- Sélectionnez un type --',
                'choices' => [
                    'Eau' => 'eau',
                    'Électricité' => 'electricite',
                    'Gaz' => 'gaz',
                ],
                'required' => true,
                'attr' => [
                    'class' => 'form-select',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez sélectionner un type de consommation.',
                    ]),
                ],
            ])

            // Valeur de consommation
            ->add('valeur', NumberType::class, [
                'label' => 'Valeur consommée',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 245',
                    'step' => '0.01',
                    'min' => '0',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez entrer une valeur de consommation.',
                    ]),
                    new Assert\GreaterThan([
                        'value' => 0,
                        'message' => 'La valeur doit être supérieure à 0.',
                    ]),
                ],
            ])

            // Date de début
            ->add('periodeDebut', DateType::class, [
                'label' => 'Date de début de la période',
                'widget' => 'single_text',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez sélectionner une date de début.',
                    ]),
                ],
            ])

            // Date de fin
            ->add('periodeFin', DateType::class, [
                'label' => 'Date de fin de la période',
                'widget' => 'single_text',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez sélectionner une date de fin.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Consommation::class,
        ]);
    }
}
