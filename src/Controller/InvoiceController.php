<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Invoice;
use App\Entity\Product;
use App\Form\InvoiceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/invoice', name: 'app_invoice_')]

final class InvoiceController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $status = $request->query->get('status');

        $criteria = ['owner' => $this->getUser()];

        if ($status && in_array($status, ['draft', 'validated', 'paid'])) {
            $criteria['status'] = $status;
        } else {
            $status = 'all';
        }

        $invoices = $em->getRepository(Invoice::class)->findBy(
            $criteria,
            ['createdAt' => 'DESC']
        );

        return $this->render('invoice/index.html.twig', [
            'invoices' => $invoices,
            'currentStatus' => $status,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Invoice $invoice): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($invoice->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit d'accéder à cette facture.");
        }

        return $this->render('/invoice/show.html.twig', [
            'invoice' => $invoice,
        ]);
    }

    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $invoice = new Invoice();
        $clients = $em->getRepository(Client::class)->findBy([
            'owner' => $this->getUser(),
        ]);
        $products = $em->getRepository(Product::class)->findBy([
            'owner' => $this->getUser(),
        ]);

        $form = $this->createForm(InvoiceType::class, $invoice, [
            'clients_choices' => $clients,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invoice->setOwner($this->getUser());

            $clickedStatus = $request->request->get('status');

            if ($clickedStatus === 'validated') {
                $invoice->setStatus('validated');
                $invoice->setNumber('FAC-' . date('Ymd') . '-' . rand(100, 999));
            } else {
                $invoice->setStatus('draft');
                $invoice->setNumber(null);
            }

            $totalFacture = 0;

            foreach ($invoice->getInvoiceLines() as $line) {
                $line->setInvoice($invoice);

                $lineTotal = $line->getUnitPrice() * $line->getQuantity();
                $line->setTotal($lineTotal);

                $totalFacture += $lineTotal;
            }

            $invoice->setTotalAmount($totalFacture);

            $em->persist($invoice);
            $em->flush();

            $message = ($invoice->getStatus() === 'validated')
                ? 'Facture validée et enregistrée.'
                : 'Brouillon enregistré avec succès.';

            $this->addFlash('success', $message);
            return $this->redirectToRoute('app_invoice_index');
        }

        return $this->render('invoice/add.html.twig', [
            'invoice' => $invoice,
            'products' => $products,
            'form' => $form->createView(),
        ]);

    }

    #[Route(path: '/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Invoice $invoice, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($invoice->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $clients = $em->getRepository(Client::class)->findBy([
            'owner' => $invoice->getOwner(),
        ]);
        $products = $em->getRepository(Product::class)->findBy([
            'owner' => $invoice->getOwner(),
        ]);

        $form = $this->createForm(InvoiceType::class, $invoice, [
            'clients_choices' => $clients,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $clickedStatus = $request->request->get('status');
            if ($clickedStatus === 'validated') {
                $invoice->setStatus('validated');
                if (!$invoice->getNumber()) {
                    $invoice->setNumber('FAC-' . date('Ymd') . '-' . rand(100, 999));
                }
            } else {
                $invoice->setStatus('draft');
            }

            $totalFacture = 0;
            foreach ($invoice->getInvoiceLines() as $line) {
                $line->setInvoice($invoice);
                $lineTotal = $line->getUnitPrice() * $line->getQuantity();
                $line->setTotal($lineTotal);
                $totalFacture += $lineTotal;
            }
            $invoice->setTotalAmount($totalFacture);

            $em->flush();

            $this->addFlash('success', 'Facture mise à jour avec succès.');
            return $this->redirectToRoute('app_invoice_index');
        }

        return $this->render('invoice/edit.html.twig', [
            'invoice' => $invoice,
            'form' => $form->createView(),
            'products' => $products,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Invoice $invoice, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($invoice->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $invoice->getId(), $request->request->get('_token'))) {
            if ($invoice->getStatus() !== 'draft') {
                $this->addFlash('error', 'Une facture validée ne peut pas être supprimée.');
                return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
            }

            $em->remove($invoice);
            $em->flush();
            $this->addFlash('success', 'Le brouillon a été supprimé.');
        } else {
            $this->addFlash('error', 'Token de sécurité invalide.');
        }

        return $this->redirectToRoute('app_invoice_index');
    }

    #[Route(path: '/{id}/validate', name: 'validate', methods: ['POST'])]
    public function validate(Request $request, Invoice $invoice, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($invoice->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($invoice->getStatus() !== 'draft') {
            $this->addFlash('error', 'Seul un brouillon peut être validé.');
            return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
        }

        $invoice->setStatus('validated');

        if (!$invoice->getNumber()) {
            $invoice->setNumber('FACT-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT));
        }

        $em->flush();

        $this->addFlash('success', 'La facture a été validée avec succès. Elle ne peut plus être modifiée.');
        return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
    }

    #[Route('/{id}/pay', name: 'pay', methods: ['POST'])]
    public function pay(Invoice $invoice, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($invoice->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($invoice->getStatus() !== 'validated') {
            $this->addFlash('error', 'Seule une facture en attente peut être marquée comme payée.');
            return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
        }

        $invoice->setStatus('paid');
        $em->flush();

        $this->addFlash('success', 'Félicitations, le paiement a été enregistré avec succès !');
        return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
    }

    #[Route('/{id}/send', name: 'send', methods: ['POST'])]
    public function send(Invoice $invoice): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($invoice->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $this->addFlash('info', 'Fonctionnalité d\'envoi par email en cours de développement.');

        return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
    }
}
