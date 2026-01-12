<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IssuesController extends AbstractController
{
    #[Route('/issues', name: 'issues')]
    public function index(): Response
    {
        return $this->render('issues/index.html.twig');
    }
}
