<?php

namespace App\Controller;

use App\Entity\Incident;
use App\Form\IncidentType;
use App\Repository\ContratRepository;
use App\Repository\IncidentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class IssuesController extends AbstractController
{
    /**
     * Affiche la liste des incidents du logement du locataire connecté
     */
    #[Route('/issues', name: 'issues', methods: ['GET'])]
    public function index(
        ContratRepository $contratRepository,
        IncidentRepository $incidentRepository
    ): Response
    {
        $user = $this->getUser();

        // Récupérer le logement actif de l'utilisateur (via un contrat actif)
        $contrat = $contratRepository->findOneBy([
            'utilisateur' => $user,
            'statut' => 'actif' // Supposant qu'il y a un statut "actif" pour les contrats courants
        ]);

        // S'il n'a pas de contrat actif, on peut en prendre le plus récent
        if (!$contrat) {
            $contrats = $contratRepository->findBy(['utilisateur' => $user]);
            $contrat = !empty($contrats) ? reset($contrats) : null;
        }

        $incidents = [];
        if ($contrat) {
            // Récupérer les incidents du logement, triés par date de création (plus récents d'abord)
            $incidents = $incidentRepository->findBy(
                ['logement' => $contrat->getLogement()],
                ['dateCreation' => 'DESC']
            );
        }

        return $this->render('issues/index.html.twig', [
            'incidents' => $incidents,
            'contrat' => $contrat,
        ]);
    }

    /**
     * Affiche et traite le formulaire de déclaration d'un nouvel incident
     */
    #[Route('/issues/new', name: 'issues_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        ContratRepository $contratRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = $this->getUser();

        // Récupérer le logement actif de l'utilisateur
        $contrat = $contratRepository->findOneBy([
            'utilisateur' => $user,
            'statut' => 'actif'
        ]);

        if (!$contrat) {
            $contrats = $contratRepository->findBy(['utilisateur' => $user]);
            $contrat = !empty($contrats) ? reset($contrats) : null;
        }

        // Si pas de contrat/logement, on ne peut pas créer d'incident
        if (!$contrat) {
            $this->addFlash('error', 'Vous n\'avez pas de logement assigné. Impossible de déclarer un incident.');
            return $this->redirectToRoute('issues');
        }

        $incident = new Incident();
        $incident->setLogement($contrat->getLogement());

        $form = $this->createForm(IncidentType::class, $incident);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Définir la date de création à maintenant
            $incident->setDateCreation(new \DateTime());
            
            // dateResolution reste null (incident en cours)
            // logement est déjà défini ci-dessus

            $entityManager->persist($incident);
            $entityManager->flush();

            $this->addFlash('success', 'Incident déclaré avec succès. Nous traitons votre demande.');
            return $this->redirectToRoute('issues');
        }

        return $this->render('issues/new.html.twig', [
            'form' => $form,
            'contrat' => $contrat,
        ]);
    }

    /**
     * Affiche les détails d'un incident spécifique (optionnel, pour le futur)
     */
    #[Route('/issues/{id}', name: 'issues_show', methods: ['GET'])]
    public function show(
        Incident $incident,
        ContratRepository $contratRepository
    ): Response
    {
        $user = $this->getUser();

        // Vérifier que l'incident appartient au logement de l'utilisateur
        $contrat = $contratRepository->findOneBy([
            'utilisateur' => $user,
            'logement' => $incident->getLogement()
        ]);

        if (!$contrat) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cet incident.');
        }

        return $this->render('issues/show.html.twig', [
            'incident' => $incident,
        ]);
    }
}
