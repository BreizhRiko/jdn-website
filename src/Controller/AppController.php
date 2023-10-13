<?php

namespace App\Controller;

use App\Entity\MenuReservation;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\MenuRepository;
use App\Repository\ReservationRepository;
use App\Repository\TableRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AppController extends AbstractController
{

    private string $LYDIA_PUBLIC_TOKEN = '615d5cb1e280f778385311';
    private bool $reservation_enabled = false;

    private function getLydiaData($baseUrl, $recipient, $amount, $validation_token): array
    {
        return [
            'vendor_token' => $this->LYDIA_PUBLIC_TOKEN,
            'amount' => $amount,
            'recipient' => $recipient,
            'payment_mail_description' => 'Merci de nous rejoindre pour cette soirée d\'exception ! Récapitulatif de la commande disponible à cette adresse : ' . $baseUrl . '/success/' . $validation_token,
            'browser_success_url' => $baseUrl . '/success/' . $validation_token,
            'end_mobile_url' => $baseUrl . '/success/' . $validation_token,
            'browser_fail_url' => $baseUrl . '/cancel/' . $validation_token,
            'payment_method' => 'auto',
            'currency' => 'EUR',
            'type' => 'phone',
            'display_confirmation' => 'no'
        ];
    }

    #[Route('/', name: 'home', schemes: 'https')]
    public function index(TableRepository $tableRepository, MenuRepository $menuRepository, Request $request): Response
    {
        // creates a task object and initializes some data for this example
        $reservation = new Reservation();
        $entityManager = $this->getDoctrine()->getManager();
        $menus = $menuRepository->findAll();
        $tables = $tableRepository->findBy([], ['name' => 'ASC']);
        $availableTables = [];
        foreach ($tables as $table) {
            if ($table->isAvailable())
                $availableTables[] = $table;
        }

        foreach ($menus as $menu) {
            $mr = new MenuReservation();
            $mr->setReservation($reservation)->setMenu($menu)->setQuantity(0);
            $reservation->addMenuReservation($mr);
        }

        $form = $this->createForm(ReservationType::class, $reservation,['attr' => ['id' => 'inscription_form']])
            ->add('hostTable', ChoiceType::class, [
                'attr' => [
                    'class' => 'form-select-lg'
                ],
                'placeholder' => 'Choisissez votre table',
                'choices' => $availableTables,
                'choice_label' => 'name',
                'label' => false
            ]);
        $submittedToken = $request->request->get('token');
        $form->handleRequest($request);
        if (
            $form->isSubmitted()
            && $form->isValid()
            && $this->reservation_enabled
            && $this->isCsrfTokenValid('add-item', $submittedToken)
        ) {
            if (!$reservation->getHostTable()->isAddable($reservation->getMenuReservationsNumber())) {
                $form->addError(new FormError('IMPOSSIBLE DE VALIDER LA RÉSERVATION, LA TABLE NE PEUT PAS ACCUEILLIR LE NOMBRE DE MENUS CHOISIS'));
            } else {
                $reservation = $form->getData();
                $amount = 0;
                foreach ($reservation->getMenuReservations() as $mr) {
                    if ($mr->getQuantity() > 0) {
                        $entityManager->persist($mr);
                        $amount += $mr->getQuantity() * $mr->getMenu()->getPrice();
                    } else {
                        $reservation->removeMenuReservation($mr);
                    }
                }

                $entityManager->persist($reservation);
                $entityManager->flush();
                if ($reservation->getPaymentMethod() === "LYDIA") {
                    $baseUrl = $request->getSchemeAndHttpHost();
                    $client = HttpClient::create();

                    try {
                        $response = $client->request('POST', 'https://lydia-app.com/api/request/do.json', [
                            'body' => $this->getLydiaData(
                                $baseUrl,
                                $reservation->getPhoneNumber(),
                                $amount,
                                $reservation->getValidationToken()
                            )]);
                        $content = $response->toArray();
                    } catch (TransportExceptionInterface $e) {
                        $form->addError(new FormError('UNE ERREUR INCONNUE S\'EST PRODUITE'));
                    }
                    return $this->redirect($content['mobile_url']);
                } else {
                    return $this->redirectToRoute('success_reservation', ['id' => $reservation->getId()]);
                }
            }
        }
        return $this->render('app/index.html.twig', [
            'tables' => $tables,
            'menus' => $menus,
            'reservation_enabled' => $this->reservation_enabled,
            'form' => $form->createView()
        ]);
    }

    /**
     * @throws \Symfony\Component\Notifier\Exception\TransportExceptionInterface
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    #[Route('/success/{id}', name: 'success_reservation', schemes: 'https')]
    public function success(string $id, ReservationRepository $repository, MailerInterface $mailer, Request $request): Response
    {
        $baseUrl = $request->getSchemeAndHttpHost();
        $entityManager = $this->getDoctrine()->getManager();
        $reservation = $repository->findOneBy(['validationToken' => $id]);
        if ($reservation) {
            $reservation->setPaid(true);
        } else {
            $reservation = $repository->findOneBy(['id' => $id]);
        }

        if (!$reservation->getMailSent()) {
            $email = (new TemplatedEmail())
                ->from('noreply@just-diplomed-night.fr')
                ->to(new Address($reservation->getEmail()))
                ->subject('Reçu réservation JDN')
                ->priority(Email::PRIORITY_HIGH)
                ->htmlTemplate('mails/receipt.html.twig')
                ->context([
                    'reservation' => $reservation,
                    'successURL' => $baseUrl . '/success/' . $reservation->getId()
                ]);
            $mailer->send($email);
            $reservation->setMailSent(true);
        }
        $entityManager->persist($reservation);
        $entityManager->flush();
        return $this->render('app/success.html.twig', [
            'reservation' => $reservation
        ]);
    }

    #[Route('/cancel/{id}', name: 'cancel_reservation', schemes: 'https')]
    public function cancel(string $id, ReservationRepository $repository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $reservation = $repository->findOneBy(['validationToken' => $id]);
        $deleted = false;
        if ($reservation) {
            $entityManager->remove($reservation);
            $entityManager->flush();
            $deleted = true;
        } else {
            $reservation = $repository->findOneBy(['id' => $id]);
            if ($reservation) {
                $entityManager->remove($reservation);
                $entityManager->flush();
                $deleted = true;
            }
        }
        if ($deleted) {
            $this->addFlash(
                'danger',
                'Un problème est survenu lors de la réservation, veuillez réessayer.'
            );
        }
        return $this->redirect('/#reservations');
    }
}
