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
}
