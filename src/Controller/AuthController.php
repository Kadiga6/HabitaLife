<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{
    #[Route('/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupère la dernière erreur de connexion s'il y en a
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Dernier email entré par l'utilisateur
        $lastEmail = $authenticationUtils->getLastUsername();

        return $this->render('connexion/index.html.twig', [
            'last_email' => $lastEmail,
            'error' => $error,
        ]);
    }

    #[Route('/inscription', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UtilisateurRepository $utilisateurRepository
    ): Response
    {
        // 1️⃣ INITIALISER TOUTES LES CLÉS AVEC NULL (IMPORTANT !)
        // Cette structure est TOUJOURS présente, peu importe GET ou POST
        $errors = [
            'email' => null,
            'prenom' => null,
            'nom' => null,
            'motDePasse' => null,
            'confirmPassword' => null,
        ];

        // Variables du formulaire
        $email = '';
        $prenom = '';
        $nom = '';

        // 2️⃣ TRAITEMENT POST
        if ($request->isMethod('POST')) {
            // Récupérer les données du formulaire
            $email = $request->request->get('email', '');
            $prenom = $request->request->get('prenom', '');
            $nom = $request->request->get('nom', '');
            $motDePasse = $request->request->get('motDePasse', '');
            $confirmPassword = $request->request->get('confirmPassword', '');

            // 3️⃣ VALIDATION - ajouter les erreurs uniquement si nécessaire
            // Email
            if (empty($email)) {
                $errors['email'] = 'L\'email est requis.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Veuillez entrer une adresse email valide.';
            } elseif ($utilisateurRepository->findOneBy(['email' => $email])) {
                $errors['email'] = 'Cet email est déjà utilisé.';
            }

            // Prénom
            if (empty($prenom)) {
                $errors['prenom'] = 'Le prénom est requis.';
            }

            // Nom
            if (empty($nom)) {
                $errors['nom'] = 'Le nom est requis.';
            }

            // Mot de passe
            if (empty($motDePasse)) {
                $errors['motDePasse'] = 'Le mot de passe est requis.';
            } elseif (strlen($motDePasse) < 6) {
                $errors['motDePasse'] = 'Le mot de passe doit contenir au moins 6 caractères.';
            }

            // Confirmation mot de passe
            if (empty($confirmPassword)) {
                $errors['confirmPassword'] = 'Veuillez confirmer votre mot de passe.';
            } elseif ($motDePasse !== $confirmPassword) {
                $errors['confirmPassword'] = 'Les mots de passe ne correspondent pas.';
            }

            // 4️⃣ SI PAS D'ERREURS, CRÉER L'UTILISATEUR
            if (!array_filter($errors)) { // Vérifier que TOUTES les erreurs sont null
                $utilisateur = new Utilisateur();
                $utilisateur->setEmail($email);
                $utilisateur->setPrenom($prenom);
                $utilisateur->setNom($nom);
                $utilisateur->setRole('ROLE_USER');
                $utilisateur->setDateCreation(new \DateTime());

                // Hasher le mot de passe
                $hashedPassword = $passwordHasher->hashPassword($utilisateur, $motDePasse);
                $utilisateur->setMotDePasse($hashedPassword);

                // Persister et flush
                $entityManager->persist($utilisateur);
                $entityManager->flush();

                // Succès - rediriger
                $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
                return $this->redirectToRoute('app_login');
            }

            // POST avec erreurs : afficher le formulaire avec les erreurs
        }

        // 5️⃣ RENDU - Toujours avec la structure complète des erreurs
        return $this->render('inscription/index.html.twig', [
            'errors' => $errors,
            'email' => $email,
            'prenom' => $prenom,
            'nom' => $nom,
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        // La logique de déconnexion est gérée par Symfony
    }
}
