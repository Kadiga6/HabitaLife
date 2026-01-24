<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class LogementController extends AbstractController
{
    #[Route('/logement', name: 'app_logement')]
    public function index(): Response
    {
        // Récupérer l'utilisateur connecté
        $utilisateur = $this->getUser();

        // Récupérer le premier contrat actif de l'utilisateur
        // (en supposant qu'un utilisateur n'a qu'un logement actif)
        $contrat = null;
        $logement = null;
         $consommations = [];

        // Parcourir les contrats de l'utilisateur pour en trouver un actif
        foreach ($utilisateur->getContrats() as $c) {
            if ($c->getStatut() === 'actif' || is_null($c->getDateFin()) || $c->getDateFin() > new \DateTime()) {
                $contrat = $c;
                $logement = $c->getLogement();
                break;
            }
        }

       // On AFFICHERA la page dans tous les cas
        return $this->render('logement/index.html.twig', [
            'logement' => $logement,
            'contrat' => $contrat,
            'consommations' => $consommations,
        ]);

        // Récupérer les consommations du logement (dernières du mois ou dernières valeurs)
        $consommations = $logement->getType(); // Relation OneToMany vers Consommation

        // Récupérer les incidents du logement
        $incidents = $logement->getTitre(); // Relation OneToMany vers Incident

        // Compter les incidents ouverts
        $incidentsOuverts = 0;
        $dernierIncident = null;

        foreach ($incidents as $incident) {
            if ($incident->getStatut() === 'en cours' || $incident->getStatut() === 'ouvert') {
                $incidentsOuverts++;
            }
            // Récupérer le dernier incident
            if ($dernierIncident === null || $incident->getDateCreation() > $dernierIncident->getDateCreation()) {
                $dernierIncident = $incident;
            }
        }

        return $this->render('logement/index.html.twig', [
            'logement' => $logement,
            'contrat' => $contrat,
            'consommations' => $consommations,
            'incidents_ouverts' => $incidentsOuverts,
            'dernier_incident' => $dernierIncident,
        ]);
    }
}
