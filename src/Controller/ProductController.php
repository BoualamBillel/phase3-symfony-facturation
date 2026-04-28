<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/product', name: 'app_product_')]
final class ProductController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setOwner($this->getUser());

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Le produit a bien été ajouté.');
            return $this->redirectToRoute('app_product_index');
        }

        $products = $em->getRepository(Product::class)->findBy([
            'owner' => $this->getUser(),
        ]);

        return $this->render('product/index.html.twig', [
            'form' => $form->createView(),
            'products' => $products,
        ]);
    }
}
