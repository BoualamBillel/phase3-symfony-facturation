<?php

namespace App\Controller;

use App\Entity\Invoice;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(Request $request, EntityManagerInterface $em, ChartBuilderInterface $chartBuilder): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $selectedYear = $request->query->get('year', date('Y'));

        $paidInvoices = $em->getRepository(Invoice::class)->findBy([
            'owner' => $user,
            'status' => 'paid'
        ]);

        $monthlyRevenue = array_fill(1, 12, 0);
        $annualRevenue = 0;
        $availableYears = [$selectedYear];

        foreach ($paidInvoices as $invoice) {
            $date = $invoice->getInvoiceDate() ?: $invoice->getCreatedAt();
            $year = $date->format('Y');

            if (!in_array($year, $availableYears)) {
                $availableYears[] = $year;
            }

            if ($year == $selectedYear) {
                $month = (int) $date->format('m');
                $monthlyRevenue[$month] += $invoice->getTotalAmount();
                $annualRevenue += $invoice->getTotalAmount();
            }
        }

        rsort($availableYears);

        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        
        $chart->setData([
            'labels' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
            'datasets' => [
                [
                    'label' => 'Chiffre d\'Affaires',
                    'backgroundColor' => '#3B82F6', 
                    'hoverBackgroundColor' => '#2563EB', 
                    'barPercentage' => 0.4, 
                    'borderRadius' => 2, 
                    'data' => array_values($monthlyRevenue),
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => ['display' => false], 
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'suggestedMax' => 2400, 
                    'grid' => [
                        'color' => '#E5E7EB', 
                        'borderDash' => [5, 5], 
                    ],
                    'border' => [
                        'display' => false, 
                    ],
                    'ticks' => [
                        'padding' => 10, 
                    ]
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'border' => [
                        'display' => false,
                    ]
                ]
            ],
        ]);

        return $this->render('dashboard/index.html.twig', [
            'selectedYear' => $selectedYear,
            'availableYears' => $availableYears,
            'annualRevenue' => $annualRevenue,
            'chart' => $chart,
        ]);
    }
}
