<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use App\Repository\TableRepository;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('admin/reservation')]
class ReservationController extends AbstractController
{
    #[Route('/', name: 'reservation_index', methods: ['GET'], schemes: 'https')]
    public function index(ReservationRepository $reservationRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $errorMessage = $request->query->get('errorMessage');
        $delimiter = 10;
        $reservations = $reservationRepository->findAll();
        $pagination = $paginator->paginate(
            $reservations, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            $delimiter // Nombre de résultats par page
        );
        return $this->render('reservation/index.html.twig', [
            'pagination' => $pagination,
            'errorMessage' => $errorMessage != null ? $errorMessage : null
        ]);
    }

    #[Route('/validate/{token}', name: 'validate_reservation', schemes: 'https')]
    public function validateReservation(string $token, ReservationRepository $repository, Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $reservation = $repository->findOneBy(['validationToken' => $token]);
        $eventDate = new DateTime('2022-11-19');
        $nowDate = new DateTime('now');
        $diff = $nowDate->diff($eventDate)->days;
        if ($reservation !== null) {
            if ($diff > 0) {
                $this->addFlash(
                    'danger',
                    'Impossible de valider une réservation avant la date de l\'évènement ! ( ' . $diff . ' ) jours restants.'
                );
            } else {
                $this->addFlash(
                    'success',
                    'La réservation de ' . $reservation->getFirstName() . ' a bien été validée.'
                );
                $reservation->setPaid(true);
                $em->persist($reservation);
                $em->flush();
            }
        }
        return $this->redirectToRoute('reservation_index');
    }

    #[Route('/token/{id}', name: 'token_reservation', schemes: 'https')]
    public function tokenReservation(Reservation $reservation): JsonResponse
    {
        return new JsonResponse($reservation->getValidationToken());

    }

    #[Route('/{id}', name: 'reservation_show', methods: ['GET'], schemes: 'https')]
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'reservation_edit', methods: ['GET', 'POST'], schemes: 'https')]
    public function edit(Request $request, Reservation $reservation, TableRepository $tableRepository): Response
    {
        $tables = $tableRepository->findAll();
        $availableTables = [];
        $isPaid = $reservation->getPaid();
        foreach ($tables as $table) {
            if ($table->isAvailable() && $table->isAddable($reservation->getMenuReservationsNumber()))
                $availableTables[] = $table;
        }
        $form = $this->createForm(ReservationType::class, $reservation);
        if ($isPaid) {
            $form->remove('menuReservations');
            $form->remove('paymentMethod');
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash(
                'notice',
                'La réservation au nom de '
                . $reservation->getFirstName() . ' '
                . $reservation->getLastName() . ' a bien été modifiée.'
            );
            return $this->redirectToRoute('reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
            'isPaid' => $isPaid,
        ]);
    }

    #[Route('/{id}', name: 'reservation_delete', methods: ['POST'], schemes: 'https')]
    public function delete(Request $request, Reservation $reservation): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($reservation);
            $entityManager->flush();
            $this->addFlash(
                'notice',
                'La réservation au nom de '
                . $reservation->getFirstName() . ' '
                . $reservation->getLastName() . ' a bien été supprimée.'
            );
        }

        return $this->redirectToRoute('reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}
