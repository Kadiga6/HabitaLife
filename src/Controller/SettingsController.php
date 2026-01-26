<?php
// filepath: c:\wamp64\www\IRIS\Bachelor\HabitaLife\src\Controller\SettingsController.php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/settings')]
class SettingsController extends AbstractController
{
    #[Route('', name: 'settings')]
    public function index(): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        return $this->render('settings/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/update-profile', name: 'settings_update_profile', methods: ['POST'])]
    public function updateProfile(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Récupérer les données du formulaire
        $prenom = $request->request->get('prenom');
        $nom = $request->request->get('nom');
        $email = $request->request->get('email');

        // Validation basique
        if (!$prenom || !$nom || !$email) {
            $this->addFlash('error', 'Tous les champs sont obligatoires.');
            return $this->redirectToRoute('settings');
        }

        // Vérifier si l'email est unique (en excluant l'utilisateur actuel)
        $existingUser = $entityManager->getRepository($user::class)
            ->findOneBy(['email' => $email]);
        
        if ($existingUser && $existingUser->getId() !== $user->getId()) {
            $this->addFlash('error', 'Cet email est déjà utilisé par un autre compte.');
            return $this->redirectToRoute('settings');
        }

        // Mettre à jour les données
        $user->setPrenom($prenom);
        $user->setNom($nom);
        $user->setEmail($email);
        $user->setDateModification(new \DateTime());

        // Enregistrer
        $entityManager->flush();

        $this->addFlash('success', 'Vos informations personnelles ont été mises à jour avec succès !');
        return $this->redirectToRoute('settings');
    }

    #[Route('/delete-account', name: 'settings_delete_account', methods: ['POST'])]
    public function deleteAccount(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Vérifier le token CSRF pour la sécurité
      if (!$this->isCsrfTokenValid('delete_account', $request->request->get('_token'))) {
    $this->addFlash('danger', 'Token de sécurité invalide.');
    return $this->redirectToRoute('settings');
}

        // Supprimer l'utilisateur
        $entityManager->remove($user);
        $entityManager->flush();

        // Déconnecter l'utilisateur
        return $this->redirectToRoute('app_logout');
    }
}