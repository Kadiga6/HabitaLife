# 💻 Exemples de code - Intégration Paiements

## Exemples pratiques pour utiliser la fonctionnalité paiements

---

## 1. Récupérer les paiements d'un utilisateur (Service)

```php
<?php
// src/Service/PaiementService.php

namespace App\Service;

use App\Entity\Utilisateur;
use App\Repository\PaiementRepository;
use App\Repository\ContratRepository;

class PaiementService
{
    public function __construct(
        private PaiementRepository $paiementRepository,
        private ContratRepository $contratRepository,
    ) {}

    /**
     * Récupère tous les paiements d'un utilisateur avec statistiques
     */
    public function getPaiementsWithStats(Utilisateur $utilisateur): array
    {
        // Récupérer les contrats
        $contrats = $this->contratRepository->findBy(['utilisateur' => $utilisateur]);
        $contratIds = array_map(fn($c) => $c->getId(), $contrats);
        
        if (empty($contratIds)) {
            return [
                'paiements' => [],
                'stats' => ['payes' => 0, 'en_attente' => 0, 'en_retard' => 0],
            ];
        }

        // Récupérer les paiements
        $paiements = $this->paiementRepository->findByContratIds($contratIds);

        // Calculer les statistiques
        $stats = [
            'payes' => count(array_filter($paiements, fn($p) => $p->getStatut() === 'paye')),
            'en_attente' => count(array_filter($paiements, fn($p) => $p->getStatut() === 'en_attente')),
            'en_retard' => count(array_filter($paiements, fn($p) => $p->getStatut() === 'en_retard')),
        ];

        return [
            'paiements' => $paiements,
            'stats' => $stats,
        ];
    }

    /**
     * Récupère le montant total à payer (en attente + en retard)
     */
    public function getMontantTotalAPayer(Utilisateur $utilisateur): float
    {
        $contrats = $this->contratRepository->findBy(['utilisateur' => $utilisateur]);
        $contratIds = array_map(fn($c) => $c->getId(), $contrats);
        
        if (empty($contratIds)) {
            return 0;
        }

        $paiements = $this->paiementRepository->findPendingPayments($contratIds);
        
        $total = 0;
        foreach ($paiements as $paiement) {
            $total += (float) $paiement->getMontant();
        }

        return $total;
    }

    /**
     * Vérifie si l'utilisateur a des paiements en retard
     */
    public function hasLatePayments(Utilisateur $utilisateur): bool
    {
        $contrats = $this->contratRepository->findBy(['utilisateur' => $utilisateur]);
        $contratIds = array_map(fn($c) => $c->getId(), $contrats);
        
        if (empty($contratIds)) {
            return false;
        }

        $paiements = $this->paiementRepository->findByContratIds($contratIds);
        
        return count(array_filter($paiements, fn($p) => $p->getStatut() === 'en_retard')) > 0;
    }
}
```

**Utilisation :**
```php
public function dashboard(PaiementService $paiementService): Response
{
    $utilisateur = $this->getUser();
    $data = $paiementService->getPaiementsWithStats($utilisateur);
    $montantAPayer = $paiementService->getMontantTotalAPayer($utilisateur);
    
    return $this->render('dashboard/index.html.twig', [
        'paiements_stats' => $data['stats'],
        'montant_a_payer' => $montantAPayer,
        'has_late_payments' => $paiementService->hasLatePayments($utilisateur),
    ]);
}
```

---

## 2. Créer un paiement (Migration de données)

```php
<?php
// Script à exécuter une fois pour créer les paiements initiaux

namespace App\Command;

use App\Entity\Paiement;
use App\Repository\ContratRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:create-paiements',
    description: 'Crée les paiements mensuels pour les contrats actifs',
)]
class CreatePaiementsCommand extends Command
{
    public function __construct(
        private ContratRepository $contratRepository,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Récupérer tous les contrats actifs
        $contrats = $this->contratRepository->findActive();
        
        $monthNames = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];
        
        foreach ($contrats as $contrat) {
            // Créer les paiements pour les 12 prochains mois
            for ($month = 1; $month <= 12; $month++) {
                $year = date('Y');
                $monthNum = (int)date('m') + $month;
                
                if ($monthNum > 12) {
                    $monthNum -= 12;
                    $year++;
                }

                $paiement = new Paiement();
                $paiement->setContrat($contrat);
                $paiement->setPeriode($monthNames[$monthNum] . ' ' . $year);
                $paiement->setMontant($contrat->getMontantLoyer());
                $paiement->setStatut('en_attente');
                $paiement->setDateCreation(new \DateTime());

                $this->entityManager->persist($paiement);
                $output->writeln("Paiement créé : {$paiement->getPeriode()}");
            }
        }

        $this->entityManager->flush();
        $output->writeln('<info>Paiements créés avec succès !</info>');

        return Command::SUCCESS;
    }
}
```

**Utilisation :**
```bash
php bin/console app:create-paiements
```

---

## 3. Afficher un widget dans le dashboard

```twig
{# templates/dashboard/partials/payment_widget.html.twig #}

<div class="card border-primary">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="bi bi-receipt"></i> Paiements
        </h5>
    </div>
    <div class="card-body">
        {% if has_late_payments %}
            <div class="alert alert-danger mb-3">
                <i class="bi bi-exclamation-triangle"></i>
                Vous avez des paiements en retard !
            </div>
        {% endif %}

        <p class="mb-2">
            <strong>À payer :</strong>
            <span class="badge bg-warning">{{ montant_a_payer|number_format(2, ',', ' ') }} €</span>
        </p>

        <div class="row text-center">
            <div class="col-4">
                <small class="text-muted">Payés</small>
                <div class="h5 text-success">{{ paiements_stats.payes }}</div>
            </div>
            <div class="col-4">
                <small class="text-muted">En attente</small>
                <div class="h5 text-warning">{{ paiements_stats.en_attente }}</div>
            </div>
            <div class="col-4">
                <small class="text-muted">En retard</small>
                <div class="h5 text-danger">{{ paiements_stats.en_retard }}</div>
            </div>
        </div>

        <a href="{{ path('payments_index') }}" class="btn btn-primary btn-sm w-100 mt-3">
            <i class="bi bi-arrow-right"></i> Voir tous les paiements
        </a>
    </div>
</div>
```

**Utilisation dans le dashboard :**
```twig
{% extends 'base.html.twig' %}

{% block body %}
<div class="container">
    <div class="row">
        <div class="col-lg-3">
            {# Widget paiements #}
            {% include 'dashboard/partials/payment_widget.html.twig' %}
        </div>
        {# Autres widgets... #}
    </div>
</div>
{% endblock %}
```

---

## 4. Envoyer une notification email après paiement

```php
<?php
// src/Service/NotificationService.php

namespace App\Service;

use App\Entity\Paiement;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class NotificationService
{
    public function __construct(private MailerInterface $mailer) {}

    public function sendPaymentConfirmation(Paiement $paiement): void
    {
        $utilisateur = $paiement->getContrat()->getUtilisateur();
        
        $email = (new Email())
            ->from('noreply@habitago.fr')
            ->to($utilisateur->getEmail())
            ->subject('Confirmation de paiement')
            ->html($this->getPaymentConfirmationHtml($paiement));

        $this->mailer->send($email);
    }

    private function getPaymentConfirmationHtml(Paiement $paiement): string
    {
        return <<<HTML
            <h2>Paiement confirmé</h2>
            <p>Bonjour {$paiement->getContrat()->getUtilisateur()->getPrenom()},</p>
            
            <p>Votre paiement a été enregistré avec succès.</p>
            
            <table style="width: 100%; max-width: 400px; border: 1px solid #ddd;">
                <tr style="background: #f5f5f5;">
                    <td style="padding: 10px;"><strong>Période</strong></td>
                    <td style="padding: 10px;">{$paiement->getPeriode()}</td>
                </tr>
                <tr>
                    <td style="padding: 10px;"><strong>Montant</strong></td>
                    <td style="padding: 10px;">{$paiement->getMontant()}€</td>
                </tr>
                <tr style="background: #f5f5f5;">
                    <td style="padding: 10px;"><strong>Mode</strong></td>
                    <td style="padding: 10px;">{$paiement->getMoyenPaiement()}</td>
                </tr>
                <tr>
                    <td style="padding: 10px;"><strong>Date</strong></td>
                    <td style="padding: 10px;">{$paiement->getDatePaiement()->format('d/m/Y')}</td>
                </tr>
            </table>
            
            <p>Merci d'avoir payé à temps.</p>
        HTML;
    }
}
```

**Utilisation dans le contrôleur :**
```php
if ($form->isSubmitted() && $form->isValid()) {
    $paiement->setStatut('paye');
    $paiement->setDatePaiement(new \DateTime());

    $entityManager->persist($paiement);
    $entityManager->flush();

    // Envoyer un email
    $notificationService->sendPaymentConfirmation($paiement);

    $this->addFlash('success', 'Paiement effectué !');
    return $this->redirectToRoute('payments_index');
}
```

---

## 5. Requête personnalisée : Paiements du mois en cours

```php
<?php
// Dans PaiementRepository

public function findCurrentMonthPayments(Utilisateur $utilisateur): array
{
    $currentMonth = (int)date('m');
    $currentYear = (int)date('Y');
    
    $monthNames = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
    ];
    
    $currentPeriode = $monthNames[$currentMonth] . ' ' . $currentYear;
    
    return $this->createQueryBuilder('p')
        ->join('p.contrat', 'c')
        ->andWhere('c.utilisateur = :utilisateur')
        ->andWhere('p.periode = :periode')
        ->setParameter('utilisateur', $utilisateur)
        ->setParameter('periode', $currentPeriode)
        ->getQuery()
        ->getResult()
    ;
}
```

---

## 6. Filtre Twig personnalisé pour afficher les montants

```php
<?php
// src/Twig/Extension/FormatMontantExtension.php

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FormatMontantExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('montant', [$this, 'formatMontant']),
        ];
    }

    public function formatMontant(float $amount, string $devise = '€'): string
    {
        return number_format($amount, 2, ',', ' ') . ' ' . $devise;
    }
}
```

**Utilisation :**
```twig
{{ paiement.montant|montant }}
{# Affiche : 850,00 € #}
```

---

## 7. Test unitaire du service

```php
<?php
// tests/Service/PaiementServiceTest.php

namespace App\Tests\Service;

use App\Entity\Paiement;
use App\Entity\Utilisateur;
use App\Service\PaiementService;
use PHPUnit\Framework\TestCase;

class PaiementServiceTest extends TestCase
{
    public function testGetMontantTotalAPayer(): void
    {
        // Setup
        $utilisateur = new Utilisateur();
        $service = new PaiementService($paiementRepo, $contratRepo);

        // Execute
        $total = $service->getMontantTotalAPayer($utilisateur);

        // Assert
        $this->assertGreaterThanOrEqual(0, $total);
    }

    public function testHasLatePayments(): void
    {
        $utilisateur = new Utilisateur();
        $service = new PaiementService($paiementRepo, $contratRepo);

        $hasLate = $service->hasLatePayments($utilisateur);

        $this->assertIsBool($hasLate);
    }
}
```

---

Ces exemples montrent comment étendre la fonctionnalité paiements avec des services, des tâches cron, des notifications, etc.
