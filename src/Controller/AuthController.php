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
        ValidatorInterface $validator,
        UtilisateurRepository $utilisateurRepository
    ): Response
    {
        if ($request->isMethod('POST')) {
            $errors = [];
            $email = $request->request->get('email');
            $prenom = $request->request->get('prenom');
            $nom = $request->request->get('nom');
            $motDePasse = $request->request->get('motDePasse');
            $confirmPassword = $request->request->get('confirmPassword');

            // Initialiser le tableau d'erreurs
            $errors = [];

            // Validation basique
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Veuillez entrer une adresse email valide.';
            }

            if (empty($prenom)) {
                $errors['prenom'] = 'Le prénom est requis.';
            }

            if (empty($nom)) {
                $errors['nom'] = 'Le nom est requis.';
            }

            if (empty($motDePasse) || strlen($motDePasse) < 6) {
                $errors['motDePasse'] = 'Le mot de passe doit contenir au moins 6 caractères.';
            }

            if ($motDePasse !== $confirmPassword) {
                $errors['confirmPassword'] = 'Les mots de passe ne correspondent pas.';
            }

            // Vérifier si l'email existe déjà
            if ($utilisateurRepository->findOneBy(['email' => $email])) {
                $errors['email'] = 'Cet email est déjà utilisé.';
            }

            // Si pas d'erreurs, créer l'utilisateur
            if (empty($errors)) {
                $utilisateur = new Utilisateur();
                $utilisateur->setEmail($email);
                $utilisateur->setPrenom($prenom);
                $utilisateur->setNom($nom);
                $utilisateur->setRole('ROLE_USER');
                $utilisateur->setDateCreation(new \DateTime());

                // Hasher le mot de passe
                $hashedPassword = $passwordHasher->hashPassword(
                    $utilisateur,
                    $motDePasse
                );
                $utilisateur->setMotDePasse($hashedPassword);

                // Persister et flush
                $entityManager->persist($utilisateur);
                $entityManager->flush();

                // Rediriger vers la page de connexion
                $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
                return $this->redirectToRoute('app_login');
            }

            return $this->render('inscription/index.html.twig', [
                'errors' => $errors,
                'email' => $email,
                'prenom' => $prenom,
                'nom' => $nom,
            ]);
        }

return $this->render('inscription/index.html.twig', [
    'errors' => [
        'email' => null,
        'prenom' => null,
        'nom' => null,
        'motDePasse' => null,
        'confirmPassword' => null,
    ],
    'email' => '',
    'prenom' => '',
    'nom' => '',
]);

    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        // La logique de déconnexion est gérée par Symfony
    }
}
