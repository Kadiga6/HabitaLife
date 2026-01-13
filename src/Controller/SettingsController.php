<?php
// filepath: c:\wamp64\www\IRIS\Bachelor\HabitaLife\src\Controller\SettingsController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SettingsController extends AbstractController
{
    #[Route('/settings', name: 'settings')]
    public function index(): Response
    {
        // Informations personnelles de l'utilisateur
        $personalInfo = [
            'card' => [
                'title' => 'Informations personnelles',
                'icon' => 'person',
                'shadow' => true,
            ],
            'fields' => [
                'firstName' => [
                    'label' => 'Prénom',
                    'value' => 'Jean',
                    'type' => 'text',
                ],
                'lastName' => [
                    'label' => 'Nom',
                    'value' => 'Dupont',
                    'type' => 'text',
                ],
                'email' => [
                    'label' => 'Adresse e-mail',
                    'value' => 'jean.dupont@email.com',
                    'type' => 'email',
                ],
                'phone' => [
                    'label' => 'Numéro de téléphone',
                    'value' => '+33 6 12 34 56 78',
                    'type' => 'tel',
                ],
                'address' => [
                    'label' => 'Adresse',
                    'value' => '123 Rue de la Paix, 75000 Paris',
                    'type' => 'text',
                ],
            ],
            'button' => [
                'label' => 'Modifier mes informations',
                'icon' => 'pencil-square',
                'variant' => 'primary',
                'disabled' => true,
            ],
        ];

        // Paramètres de sécurité
        $securitySettings = [
            'card' => [
                'title' => 'Sécurité',
                'icon' => 'shield-lock',
                'shadow' => true,
            ],
            'sections' => [
                'password' => [
                    'title' => 'Mot de passe',
                    'items' => [
                        [
                            'name' => 'Modifier votre mot de passe',
                            'description' => 'Dernière modification : 15 Novembre 2025',
                            'button' => [
                                'label' => 'Changer le mot de passe',
                                'icon' => 'lock',
                                'disabled' => true,
                            ],
                        ],
                    ],
                ],
                'twoFactor' => [
                    'title' => 'Authentification à deux facteurs (2FA)',
                    'items' => [
                        [
                            'name' => 'Authentification par SMS',
                            'description' => 'Sécurité additionnelle pour votre compte',
                            'type' => 'toggle',
                            'enabled' => false,
                            'disabled' => true,
                        ],
                    ],
                ],
            ],
            'alert' => [
                'type' => 'warning',
                'icon' => 'exclamation-triangle',
                'title' => 'Conseil de sécurité :',
                'message' => 'Utilisez un mot de passe fort et unique pour protéger votre compte.',
            ],
        ];

        // Paramètres de suppression du compte
        $accountDeletion = [
            'card' => [
                'title' => 'Suppression du compte',
                'icon' => 'trash',
                'shadow' => true,
            ],
            'section' => [
                'title' => 'Zone de danger',
            ],
            'alert' => [
                'type' => 'danger',
                'icon' => 'exclamation-triangle-fill',
                'title' => 'Attention :',
                'message' => 'Cette action est irréversible. Toutes vos données seront supprimées.',
            ],
            'description' => 'En supprimant votre compte, vous perdrez l\'accès à tous vos services et données.',
            'button' => [
                'label' => 'Supprimer définitivement mon compte',
                'icon' => 'trash',
                'variant' => 'outline-danger',
                'disabled' => true,
            ],
        ];

        return $this->render('settings/index.html.twig', [
            'personalInfo' => $personalInfo,
            'securitySettings' => $securitySettings,
            'accountDeletion' => $accountDeletion,
        ]);
    }
}
?>