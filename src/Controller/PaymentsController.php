<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PaymentsController extends AbstractController
{
    #[Route('/payments', name: 'payments')]
    public function index(): Response
    {
        return $this->render('payments/index.html.twig');
    }
}
