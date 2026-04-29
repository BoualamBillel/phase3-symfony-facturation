<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/client', name: 'app_client_')]
final class ClientController extends AbstractController
{
    #[Route(path: '/', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client->setOwner($this->getUser());

            $em->persist($client);
            $em->flush();

            $this->addFlash('success', 'Le client a bien été ajouté.');
            return $this->redirectToRoute('app_client_index');
        }

        $clients = $em->getRepository(Client::class)->findBy([
            'owner' => $this->getUser()
        ]);



        return $this->render('client/index.html.twig', [
            'form' => $form->createView(),
            'clients' => $clients
        ]);
    }
}
