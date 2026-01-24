<?php

namespace App\Controller;

use App\Entity\Paiement;
use App\Form\PaiementType;
use App\Repository\ContratRepository;
use App\Repository\PaiementRepository;
use App\Service\PaiementMetierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/payments', name: 'payments_')]
class PaymentsController extends AbstractController
{
    private PaiementMetierService $paiementMetier;

    public function __construct(PaiementMetierService $paiementMetier)
    {
        $this->paiementMetier = $paiementMetier;
    }

    #[Route('', name: 'index')]
    public function index(
        PaiementRepository $paiementRepository,
        ContratRepository $contratRepository
    ): Response
    {
        // Récupérer l'utilisateur connecté
        $utilisateur = $this->getUser();

        // Récupérer les contrats de l'utilisateur
        $contrats = $contratRepository->findBy(['utilisateur' => $utilisateur]);
        
        // Récupérer tous les paiements des contrats de cet utilisateur
        $contratIds = array_map(fn($c) => $c->getId(), $contrats);
        $paiements = [];
        if (!empty($contratIds)) {
            $paiements = $paiementRepository->findByContratIds($contratIds);
        }

        // Mettre à jour les statuts selon la logique métier
        foreach ($paiements as $paiement) {
            $this->paiementMetier->determinerStatut($paiement);
        }

        // Calculer les statistiques
        $stats = [
            'payes' => count(array_filter($paiements, fn($p) => $p->getStatut() === 'paye')),
            'en_attente' => count(array_filter($paiements, fn($p) => $p->getStatut() === 'en_attente')),
            'en_retard' => count(array_filter($paiements, fn($p) => $p->getStatut() === 'en_retard')),
        ];

        // Déterminer le paiement à régler (en attente ou en retard)
        $paiementAPayer = null;
        foreach ($paiements as $paiement) {
            if (in_array($paiement->getStatut(), ['en_attente', 'en_retard'], true)) {
                $paiementAPayer = $paiement;
                break;
            }
        }

        // Déterminer le contrat actif (ou le premier contrat)
$contrat = null;

foreach ($contrats as $c) {
    if ($c->getStatut() === 'actif') {
        $contrat = $c;
        break;
    }
}

// fallback si aucun statut "actif"
if (!$contrat && !empty($contrats)) {
    $contrat = $contrats[0];
}


        return $this->render('payments/index.html.twig', [
            'paiements' => $paiements,
            'stats' => $stats,
            'contrats' => $contrats,
            'contrat' => $contrat,
            'paiementAPayer' => $paiementAPayer,
        ]);
    }

    #[Route('/{id}/pay', name: 'pay')]
    public function pay(
        Paiement $paiement,
        Request $request,
        EntityManagerInterface $em
    ): Response
    {
        // Vérifier que l'utilisateur a accès à ce paiement
        if ($paiement->getContrat()->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Valider le paiement selon la logique métier
        $erreurs = $this->paiementMetier->validerPaiement($paiement);
        if (!empty($erreurs)) {
            foreach ($erreurs as $erreur) {
                $this->addFlash('error', $erreur);
            }
            return $this->redirectToRoute('payments_index');
        }

        $form = $this->createForm(PaiementType::class, $paiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer la date et le statut du paiement
            $paiement->setDatePaiement(new \DateTime());
            $paiement->setStatut('paye');

            $em->flush();

            $this->addFlash('success', sprintf(
                'Paiement pour %s effectué avec succès !',
                $paiement->getPeriode()
            ));

            return $this->redirectToRoute('payments_index');
        }

        return $this->render('payments/pay.html.twig', [
            'paiement' => $paiement,
            
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(
        Request $request,
        ContratRepository $contratRepository,
        EntityManagerInterface $em
    ): Response
    {
        // Récupérer l'utilisateur connecté
        $utilisateur = $this->getUser();

        // Récupérer le contrat actif de l'utilisateur
        $contrat = $contratRepository->findActiveContractForUser($utilisateur);

        if (!$contrat) {
            $this->addFlash('warning', 'Vous n\'avez pas de contrat actif. Veuillez en configurer un d\'abord.');
            return $this->redirectToRoute('payments_index');
        }

        // Générer automatiquement les paiements attendus
        $this->paiementMetier->genererPaiementsAttendus($contrat);

        // Créer un nouveau paiement
        $paiement = new Paiement();
        $paiement->setContrat($contrat);
        $paiement->setStatut('en_attente');

        $form = $this->createForm(PaiementType::class, $paiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Valider le paiement selon la logique métier
            $erreurs = $this->paiementMetier->validerPaiement($paiement);
            
            if (!empty($erreurs)) {
                foreach ($erreurs as $erreur) {
                    $this->addFlash('error', $erreur);
                }
                return $this->redirectToRoute('payments_new');
            }

            // Enregistrer le paiement
            $paiement->setDatePaiement(new \DateTime());
            $paiement->setStatut('paye');

            $em->persist($paiement);
            $em->flush();

            $this->addFlash('success', sprintf(
                'Paiement pour %s effectué avec succès !',
                $paiement->getPeriode()
            ));

            return $this->redirectToRoute('payments_index');
        }


        return $this->render('payments/pay.html.twig', [
            'paiement' => $paiement,
            'form' => $form->createView(),
        ]);
    }
}






