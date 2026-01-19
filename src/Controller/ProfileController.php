<?php
// filepath: c:\wamp64\www\IRIS\Bachelor\HabitaLife\src\Controller\ProfileController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'profile')]
    public function index(): Response
    {
        // Données fictives de l'utilisateur
        $user = [
            'firstName' => 'Marie',
            'lastName' => 'Joubert',
            'email' => 'marie.joubert@email.com',
            'phone' => '+33 6 12 34 56 78',
            'birthDate' => '15 Janvier 1995',
            'nationality' => 'Française',
            'avatar' => 'MJ',
            'joinedDate' => '01/08/2024',
            'status' => 'active',
        ];

        // Données fictives du logement
        $housing = [
            'address' => '15 Rue de la Paix, 75002 Paris',
            'type' => 'Appartement T3',
            'surface' => '65 m²',
            'rent' => '850.00',
            'currency' => '€',
            'startDate' => '01 Août 2024',
            'leaseDuration' => '3 ans',
            'status' => 'active',
        ];

        // Données fictives du bailleur
        $landlord = [
            'name' => 'SCI Les Jardins',
            'email' => 'contact@jardins.fr',
            'phone' => '+33 1 23 45 67 89',
            'address' => '25 Avenue des Champs, 75008 Paris',
        ];

        // Statistiques utilisateur
        $stats = [
            'paymentRate' => 100,
            'totalIssues' => 11,
            'resolvedIssues' => 8,
            'paidPayments' => 5,
            'pendingPayments' => 1,
        ];

        // Activité récente
        $recentActivity = [
            [
                'type' => 'payment',
                'title' => 'Paiement effectué',
                'date' => '05 Janvier 2026',
                'icon' => 'credit-card',
            ],
            [
                'type' => 'issue',
                'title' => 'Incident déclaré',
                'date' => '10 Janvier 2026',
                'icon' => 'exclamation-circle',
            ],
            [
                'type' => 'profile',
                'title' => 'Profil mis à jour',
                'date' => '08 Janvier 2026',
                'icon' => 'person-check',
            ],
            [
                'type' => 'login',
                'title' => 'Connexion',
                'date' => 'Aujourd\'hui 14:32',
                'icon' => 'box-arrow-in-right',
            ],
        ];

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'housing' => $housing,
            'landlord' => $landlord,
            'stats' => $stats,
            'recentActivity' => $recentActivity,
        ]);
    }
}
?>