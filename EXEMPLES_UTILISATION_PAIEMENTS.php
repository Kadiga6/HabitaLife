<?php

/**
 * EXEMPLES D'UTILISATION - Service Métier des Paiements
 * 
 * Ce fichier montre comment utiliser PaiementMetierService
 * dans différents contextes de l'application.
 */

// ============================================
// EXEMPLE 1️⃣ : UTILISATION DANS UN CONTRÔLEUR
// ============================================

namespace App\Controller;

use App\Entity\Contrat;
use App\Entity\Paiement;
use App\Service\PaiementMetierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MonControleur extends AbstractController
{
    // Le service est injecté dans le constructeur
    private PaiementMetierService $paiementMetier;

    public function __construct(PaiementMetierService $paiementMetier)
    {
        $this->paiementMetier = $paiementMetier;
    }

    /**
     * EXEMPLE 1A : Générer les paiements attendus
     */
    public function genererPaiementsAction(Contrat $contrat): void
    {
        // Génère automatiquement les paiements mensuels
        // jusqu'à la date actuelle
        $this->paiementMetier->genererPaiementsAttendus($contrat);
    }

    /**
     * EXEMPLE 1B : Vérifier si un paiement est autorisé
     */
    public function verifierPaiementAction(Contrat $contrat): void
    {
        $periode = "février"; // Ou autre mois

        if ($this->paiementMetier->estPaiementAutorise($contrat, $periode)) {
            echo "✅ Paiement autorisé pour {$periode}";
        } else {
            echo "❌ Paiement refusé pour {$periode}";
        }
    }

    /**
     * EXEMPLE 1C : Déterminer le statut automatiquement
     */
    public function determinerStatutAction(Paiement $paiement): void
    {
        // Met à jour automatiquement le statut
        // en fonction de la date d'échéance et du paiement
        $this->paiementMetier->determinerStatut($paiement);

        echo "Statut déterminé : {$paiement->getStatut()}";
    }

    /**
     * EXEMPLE 1D : Valider avant sauvegarde
     */
    public function sauvegarderPaiementAction(
        Paiement $paiement,
        EntityManagerInterface $em
    ): void {
        // Valider selon la logique métier
        $erreurs = $this->paiementMetier->validerPaiement($paiement);

        if (!empty($erreurs)) {
            // Il y a des erreurs
            foreach ($erreurs as $erreur) {
                echo "❌ {$erreur}\n";
            }
            return; // Ne pas sauvegarder
        }

        // Validation OK : sauvegarder
        $em->persist($paiement);
        $em->flush();
        echo "✅ Paiement sauvegardé";
    }

    /**
     * EXEMPLE 1E : Vérifier si un paiement est en retard
     */
    public function afficherStatutAction(Paiement $paiement): void
    {
        if ($this->paiementMetier->estEnRetard($paiement)) {
            echo "⚠️ Ce paiement est EN RETARD !";
        } else {
            echo "✅ Ce paiement est à jour";
        }
    }
}

// ============================================
// EXEMPLE 2️⃣ : DANS UN EVENT LISTENER
// ============================================

namespace App\EventListener;

use App\Entity\Paiement;
use App\Service\PaiementMetierService;
use Doctrine\ORM\Events;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;

#[AsDoctrineListener(event: Events::prePersist, priority: 500)]
class PaiementPrePersistListener
{
    private PaiementMetierService $paiementMetier;

    public function __construct(PaiementMetierService $paiementMetier)
    {
        $this->paiementMetier = $paiementMetier;
    }

    /**
     * Avant de sauvegarder un paiement,
     * mettre à jour automatiquement son statut
     */
    public function prePersist($event): void
    {
        $entity = $event->getEntity();

        if (!$entity instanceof Paiement) {
            return;
        }

        // Déterminer automatiquement le statut
        $this->paiementMetier->determinerStatut($entity);
    }
}

// ============================================
// EXEMPLE 3️⃣ : DANS UNE COMMANDE CONSOLE
// ============================================

namespace App\Command;

use App\Repository\ContratRepository;
use App\Service\PaiementMetierService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenererPaiementsCommand extends Command
{
    protected static $defaultName = 'app:paiements:generer';

    private PaiementMetierService $paiementMetier;
    private ContratRepository $contratRepository;

    public function __construct(
        PaiementMetierService $paiementMetier,
        ContratRepository $contratRepository
    ) {
        parent::__construct();
        $this->paiementMetier = $paiementMetier;
        $this->contratRepository = $contratRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Générer les paiements pour tous les contrats actifs
        $contrats = $this->contratRepository->findBy(['statut' => 'actif']);

        $count = 0;
        foreach ($contrats as $contrat) {
            $this->paiementMetier->genererPaiementsAttendus($contrat);
            $count++;
        }

        $output->writeln("✅ {$count} contrats traités");
        return Command::SUCCESS;
    }
}

// ============================================
// EXEMPLE 4️⃣ : CAS COMPLETS DE TRAITEMENT
// ============================================

/**
 * Cas 1 : Afficher le statut des paiements d'un utilisateur
 */
function afficherTableauPaiementsUtilisateur(
    ContratRepository $contratRepository,
    PaiementMetierService $paiementMetier,
    $utilisateur
): void {
    // Récupérer les contrats de l'utilisateur
    $contrats = $contratRepository->findBy(['utilisateur' => $utilisateur]);

    foreach ($contrats as $contrat) {
        echo "Contrat : {$contrat->getId()}\n";
        echo "Entrée : {$contrat->getDateDebut()->format('d/m/Y')}\n";
        echo "Loyer : {$contrat->getMontantLoyer()}€\n";
        echo "\nPaiements :\n";

        foreach ($contrat->getMontant() as $paiement) {
            // Mettre à jour le statut
            $paiementMetier->determinerStatut($paiement);

            $emoji = match ($paiement->getStatut()) {
                'paye' => '✅',
                'en_retard' => '⚠️',
                default => '⏳',
            };

            printf(
                "  %s %s : %s (%.2f€)\n",
                $emoji,
                $paiement->getPeriode(),
                $paiement->getStatut(),
                $paiement->getMontant()
            );
        }
        echo "\n";
    }
}

/**
 * Cas 2 : Traiter un paiement avec validation complète
 */
function traiterPaiement(
    Paiement $paiement,
    PaiementMetierService $paiementMetier,
    EntityManagerInterface $em
): array {
    $resultat = ['success' => false, 'messages' => []];

    // 1. Valider
    $erreurs = $paiementMetier->validerPaiement($paiement);
    if (!empty($erreurs)) {
        $resultat['messages'] = $erreurs;
        return $resultat;
    }

    // 2. Enregistrer la date et le statut
    $paiement->setDatePaiement(new \DateTime());
    $paiementMetier->determinerStatut($paiement);

    // 3. Sauvegarder
    try {
        $em->flush();
        $resultat['success'] = true;
        $resultat['messages'][] = "Paiement traité avec succès";
    } catch (\Exception $e) {
        $resultat['messages'][] = "Erreur lors de la sauvegarde : " . $e->getMessage();
    }

    return $resultat;
}

/**
 * Cas 3 : Vérifier les retards et envoyer des notifications
 */
function verifierRetardsEtNotifier(
    PaiementRepository $paiementRepository,
    PaiementMetierService $paiementMetier,
    $notificationService
): int {
    $paiementsEnRetard = 0;

    $paiements = $paiementRepository->findBy(['statut' => 'en_attente']);

    foreach ($paiements as $paiement) {
        // Mettre à jour le statut
        $paiementMetier->determinerStatut($paiement);

        if ($paiement->getStatut() === 'en_retard') {
            $paiementsEnRetard++;

            // Envoyer une notification
            $notificationService->notifierRetardPaiement(
                $paiement->getContrat()->getUtilisateur(),
                $paiement
            );
        }
    }

    return $paiementsEnRetard;
}

// ============================================
// EXEMPLE 5️⃣ : DANS UN FORMULAIRE
// ============================================

namespace App\Form;

use App\Entity\Paiement;
use App\Service\PaiementMetierService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PaiementType extends AbstractType
{
    private PaiementMetierService $paiementMetier;

    public function __construct(PaiementMetierService $paiementMetier)
    {
        $this->paiementMetier = $paiementMetier;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // ... champs du formulaire ...

        // Validations métier au moment de la soumission
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $paiement = $event->getData();

            // Valider selon la logique métier
            $erreurs = $this->paiementMetier->validerPaiement($paiement);

            foreach ($erreurs as $erreur) {
                $event->getForm()->addError(
                    new FormError($erreur)
                );
            }
        });
    }
}

// ============================================
// RÉSUMÉ : FLUX TYPIQUE
// ============================================

/*
1️⃣ CRÉATION D'UN PAIEMENT
   ├─ Récupérer le contrat actif
   ├─ genererPaiementsAttendus()
   └─ Créer une nouvelle entité Paiement

2️⃣ VALIDATION
   └─ validerPaiement($paiement)
      ├─ Vérifier le contrat
      ├─ estPaiementAutorise()
      └─ Vérifier les doublons

3️⃣ DÉTERMINATION DU STATUT
   └─ determinerStatut($paiement)
      ├─ Si payé → "paye"
      └─ Si pas payé → estEnRetard() → "en_retard" ou "en_attente"

4️⃣ AFFICHAGE
   └─ Montrer les paiements avec statut à jour
*/
