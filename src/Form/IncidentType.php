<?php

namespace App\Form;

use App\Entity\Incident;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class IncidentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Titre
            ->add('titre', TextType::class, [
                'label' => 'Titre de l\'incident',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Fuite d\'eau sous l\'évier',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez entrer un titre pour l\'incident.',
                    ]),
                    new Assert\Length([
                        'min' => 3,
                        'minMessage' => 'Le titre doit contenir au moins 3 caractères.',
                        'max' => 255,
                        'maxMessage' => 'Le titre ne peut pas dépasser 255 caractères.',
                    ]),
                ],
            ])

            // Catégorie
            ->add('categorie', ChoiceType::class, [
                'label' => 'Catégorie',
                'placeholder' => '-- Sélectionnez une catégorie --',
                'choices' => [
                    'Plomberie' => 'plomberie',
                    'Électricité' => 'electricite',
                    'Chauffage' => 'chauffage',
                    'Serrurerie' => 'serrurerie',
                    'Autre' => 'autre',
                ],
                'required' => true,
                'attr' => [
                    'class' => 'form-select',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez sélectionner une catégorie.',
                    ]),
                ],
            ])

            // Description
            ->add('description', TextareaType::class, [
                'label' => 'Description détaillée',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Décrivez l\'incident en détail (localisation, nature du problème, etc.)',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez entrer une description de l\'incident.',
                    ]),
                    new Assert\Length([
                        'min' => 10,
                        'minMessage' => 'La description doit contenir au moins 10 caractères.',
                        'max' => 2000,
                        'maxMessage' => 'La description ne peut pas dépasser 2000 caractères.',
                    ]),
                ],
            ])

            // Priorité
            ->add('priorite', ChoiceType::class, [
                'label' => 'Priorité',
                'placeholder' => '-- Sélectionnez une priorité --',
                'choices' => [
                    'Basse' => 'basse',
                    'Normale' => 'normale',
                    'Urgente' => 'urgente',
                ],
                'required' => true,
                'attr' => [
                    'class' => 'form-select',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez sélectionner une priorité.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Incident::class,
        ]);
    }
}
