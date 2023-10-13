<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\MenuRepository;
use App\Repository\MenuReservationRepository;
use App\Repository\ReservationRepository;
use App\Repository\TableRepository;
use DateInterval;
use DatePeriod;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    private string $beginEvent = '2022-11-19';
    private string $beginMeasures = '2022-10-19';

    #[Route('/admin', name: 'admin')]
    public function index(ReservationRepository $reservationRepository, MenuRepository $menuRepository, MenuReservationRepository $menuReservationRepository, TableRepository $tableRepository): Response
    {
        $reservations = $reservationRepository->findAll();
        $tables = $tableRepository->findAll();
        $toPayReservations = $reservationRepository->findBy(['paid' => false]);
        $menusReservationsNb = $menuReservationRepository->countMenuReservations();
        $menus = $menuRepository->findAll();
        $totalPaidMoney = 0;
        $totalUnpaidMoney = 0;
        $totalMoney = 0;
        $ratioPaid = 0;
        $available_seats = 0;
        $numberReservationsToPay = 0;
        foreach ($tables as $table){
           $available_seats += ($table->getCapacity() - $table->getCurrentCapacity());
        }
        foreach ($reservations as $r) {
            foreach ($r->getMenuReservations() as $mr) {
                if ($r->getPaid())
                    $totalPaidMoney += ($mr->getQuantity() * $mr->getMenu()->getPrice());
                else{
                    $totalUnpaidMoney += ($mr->getQuantity() * $mr->getMenu()->getPrice());
                }
                $totalMoney += ($mr->getQuantity() * $mr->getMenu()->getPrice());
            }
            if(!$r->getPaid())
                $numberReservationsToPay++;
        }
        if ($totalPaidMoney > 0 && $totalUnpaidMoney > 0)
            $ratioPaid = $totalPaidMoney * 100 / $totalMoney;

        $dataDoughnut = $this->buildDoughnutChart($menus, $menusReservationsNb);
        return $this->render('admin/index.html.twig', [
            'totalReservations' => $reservations,
            'toPayReservations' => $toPayReservations,
            'totalMoney' => $totalMoney,
            'totalPaidMoney' => $totalPaidMoney,
            'available_seats' => $available_seats,
            'ratioPaid' => $ratioPaid,
            'menus' => $menus,
            'numberReservationsToPay' => $numberReservationsToPay,
            'dataDoughnutChart' => json_encode($dataDoughnut),
            'doughnutChartLabels' => $dataDoughnut['data']['labels'],
            'dataLineChart' => $this->buildLineChart($reservations),
        ]);
    }

    /**
     * @throws \Exception
     */
    private function buildLineChart($reservations): string
    {

        $beginEvent = new DateTime($this->beginEvent);
        $beginMeasures = new DateTime($this->beginMeasures);

        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($beginMeasures, $interval, $beginEvent->add($interval));
        $range = [];
        foreach ($period as $dt) {
            $range[] = $dt->format("d/m");
        }

        $options = [
            'type' => 'line',
            'data' =>
                [
                    'labels' => $range,
                    'datasets' => [
                        [
                            'label' => 'Earnings',
                            'fill' => true,
                            'data' => $this->getRangedMoney($reservations, $range),
                            'backgroundColor' => 'rgba(78, 115, 223, 0.05)',
                            'borderColor' => 'rgba(78, 115, 223, 1)']]],
            'options' => ['maintainAspectRatio' => false,
                'legend' => ['display' => false, 'labels' => ['fontStyle' => 'normal'],
                    'title' => ['fontStyle' => 'normal'
                    ],
                    'scales' =>
                        [
                            'xAxes' => [
                                [
                                    'gridLines' => ['color' => 'rgb(234, 236, 244)',
                                        'zeroLineColor' => 'rgb(234, 236, 244)',
                                        'drawBorder' => false,
                                        'drawTicks' => false,
                                        'borderDash' => ['2'],
                                        'zeroLineBorderDash' => ['2'],
                                        'drawOnChartArea' => false
                                    ],
                                    'ticks' => ['fontColor' => '#858796', 'fontStyle' => 'normal', 'padding' => 20]],
                                'yAxes' => [
                                    [
                                        'gridLines' =>
                                            [
                                                'color' => 'rgb(234, 236, 244)',
                                                'zeroLineColor' => 'rgb(234, 236, 244)',
                                                'drawBorder' => false,
                                                'drawTicks' => false,
                                                'borderDash' => ['2'],
                                                'zeroLineBorderDash' => ['2']
                                            ],
                                        'ticks' => [
                                            'fontColor' => '#858796',
                                            'fontStyle' => 'normal',
                                            'padding' => 20
                                        ]
                                    ]]
                            ]
                        ]
                ]]];
        return json_encode($options);
    }

    private function buildDoughnutChart($menus, $menuReservationsNb): array
    {
        $options = [
            'type' => 'doughnut', 'data' => [
                'labels' =>
                    [],
                'datasets' => [
                    [
                        'label' => '',
                        'backgroundColor' => ['#4e73df', '#1cc88a', '#36b9cc'],
                        'borderColor' => ['#ffffff', '#ffffff', '#ffffff'],
                        'data' => []
                    ]
                ]
            ],
            'options' => ['maintainAspectRatio' => false,
                'legend' => ['display' => false, 'labels' => ['fontStyle' => 'normal']],
                'title' => ['fontStyle' => 'normal'
                ]
            ]
        ];
        $i = 0;
        foreach ($menus as $menu) {
            if ($i < count($menuReservationsNb)) {
                $options['data']['datasets'][0]['data'][] = $menuReservationsNb[$i][1];
            } else {
                $options['data']['datasets'][0]['data'][] = 0;
            }
            $options['data']['labels'][] = $menu->getName();
            $i++;
        }
        return $options;
    }

    /**
     * @param Reservation[] $reservations
     */
    private function getRangedMoney(array $reservations, $range): array
    {
        $data = [];
        $data = array_pad($data, count($range), 0);
        for ($i = 0; $i < count($range); $i++) {
            $value = 0;
            if ($i > 0)
                $value = $data[$i - 1];
            foreach ($reservations as $reservation) {
                if ($range[$i] === $reservation->getCreatedAt()->format('d/m') && $reservation->getPaid()) {
                    $value += $reservation->getPrice();
                }
            }
            $data[$i] = $value;
        }
        return $data;
    }
}
