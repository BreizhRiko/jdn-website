<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use App\Repository\ReservationRepository;
use App\Repository\TableRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    private $delimiter = 10;

    #[Route('/search', name: 'search')]
    public function index(MenuRepository $menuRepository, ReservationRepository $reservationRepository, PaginatorInterface $paginator, TableRepository $tableRepository, Request $request): Response
    {
        $slug = $request->get('slug');
        $reservations = $reservationRepository->findBySlug($slug);
        for ($i = 0; $i < count($reservations); $i++) {
            $reservations[$i] = $reservationRepository->findOneBy(['id' => $reservations[$i]['id']]);
        }
        $menus = $menuRepository->findBySlug($slug);
        $tables = $tableRepository->findBySlug($slug);
        $reservations = $paginator->paginate(
            $reservations,
            $request->query->getInt('page_reservations', 1),
            $this->delimiter,
            ['pageParameterName' => 'page_reservations']
        );
        $menus = $paginator->paginate(
            $menus,
            $request->query->getInt('page_menus', 1),
            $this->delimiter,
            ['pageParameterName' => 'page_menus']
        );
        $tables = $paginator->paginate(
            $tables,
            $request->query->getInt('page_tables', 1),
            $this->delimiter,
            ['pageParameterName' => 'page_tables']
        );
        return $this->render('search/index.html.twig', [
            'slug' => $slug,
            'reservation_pagination' => $reservations,
            'menu_pagination' => $menus,
            'table_pagination' => $tables,
        ]);
    }
}
