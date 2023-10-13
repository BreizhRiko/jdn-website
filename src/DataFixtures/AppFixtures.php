<?php

namespace App\DataFixtures;

use App\Entity\Menu;
use App\Entity\MenuReservation;
use App\Entity\Reservation;
use App\Entity\Table;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Faker;

class AppFixtures extends Fixture
{
    private $encoder;

    private $companyTables = [
        "Bruno Masure",
    ];
    private $teacherTables = [
        "Jean-Pierre Pernaut",
        "Léon Zitrone",
    ];
    private $jdnTables = [
        "Laurent Delahousse",
        "Elise Lucet",
        "Julien Arnaud",
    ];

    private $tableNames = [
        "Julian Bugier",
        "Anne-Sophie Lapix",
        "David Pujadas",
        "Karine Baste-Régis",
        "Claire Chazal",
        "Anne-Claire Coudray",
        "Audrey Crespo Mara",
        "Jacques Legros",
        "Yann Barthes",
        "Michel Denisot",
        "Marie Sophie Lacarrau",
        "Gilles Bouleau",
        "Nathanaël de Rincquesen",
        "Marie Drucker",
        "Samuel Etienne",
        "Laurence Ferrari",
        "Harry Roselmack",
        "Jean-Pierre Elkabbach",
        "Leïla Kaddour",
        "Thomas Sotto",
        "Hervé Mathoux",
        "William Leymergie",
        "Jean-Claude Bourret",
        "Christophe Hondelatte",
        "Audrey Pulvar",
        "Évelyne Dhéliat",
        "Gérard Holtz",
        "Ruth Elkrief",
        "Pascal Praud",
        "Thierry Gilardi",
        "Ophelie Meunier",
        "Nelson Monfort",
        "Bernard De La Villardière ",
        "Estelle Denis ",
        "Thierry Roland",
        "Mathieu Lartot",
        "Nikos Aliagas",
    ];

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail('jdn@ig2i.fr')
            ->setPassword($this->encoder->encodePassword($user, 'LztTtwrO96O89KB'))
            ->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);
        $faker = Faker\Factory::create();
        $menu = new Menu();
        $menu->setName("GALA")->setPrice(35);
        $menu->setStarter("À venir.");
        $menu->setDish("À venir.");
        $menu->setDessert("À venir.");
        $manager->persist($menu);

        for ($i = 0; $i < count($this->tableNames); $i++) {
            $table = new Table();
            $table
                ->setName($this->tableNames[$i])
                ->setCapacity(10)
                ->setAvailable(true);
            $manager->persist($table);
        }

        for ($i = 0; $i < count($this->jdnTables); $i++) {
            $table = new Table();
            $table
                ->setName($this->jdnTables[$i])
                ->setCapacity(10)
                ->setAvailable(true);
            $manager->persist($table);

            if (!$table->isAvailable()) {
                $reservation = new Reservation();
                $reservation
                    ->setEmail("")
                    ->setFirstName("Association")
                    ->setLastName("RI&JDN")
                    ->setPhoneNumber("")
                    ->setPaymentMethod("CASH")
                    ->setHostTable($table);

                $menuReservation = new MenuReservation();
                $menuReservation
                    ->setMenu($menu)
                    ->setReservation($reservation)
                    ->setQuantity(10);
                $manager->persist($menuReservation);
                $reservation->addMenuReservation($menuReservation);

                $manager->persist($reservation);
            }

        }

        for ($i = 0; $i < count($this->teacherTables); $i++) {
            $table = new Table();
            $table
                ->setName($this->teacherTables[$i])
                ->setCapacity(10)
                ->setAvailable(true);
            $manager->persist($table);

            if (!$table->isAvailable()) {
                $reservation = new Reservation();
                $reservation
                    ->setEmail("")
                    ->setFirstName("ENSEIGNANT")
                    ->setLastName("")
                    ->setPhoneNumber("")
                    ->setPaymentMethod("CASH")
                    ->setHostTable($table);

                $menuReservation = new MenuReservation();
                $menuReservation
                    ->setMenu($menu)
                    ->setReservation($reservation)
                    ->setQuantity(10);
                $manager->persist($menuReservation);
                $reservation->addMenuReservation($menuReservation);

                $manager->persist($reservation);
            }

        }

        for ($i = 0; $i < count($this->companyTables); $i++) {
            $table = new Table();
            $table
                ->setName($this->companyTables[$i])
                ->setCapacity(10)
                ->setAvailable(true);
            $manager->persist($table);

            if (!$table->isAvailable()) {
                $reservation = new Reservation();
                $reservation
                    ->setEmail("")
                    ->setFirstName("ENTREPRISE")
                    ->setLastName("")
                    ->setPhoneNumber("")
                    ->setPaymentMethod("CASH")
                    ->setHostTable($table);

                $menuReservation = new MenuReservation();
                $menuReservation
                    ->setMenu($menu)
                    ->setReservation($reservation)
                    ->setQuantity(10);
                $manager->persist($menuReservation);
                $reservation->addMenuReservation($menuReservation);

                $manager->persist($reservation);
            }

        }
        $manager->flush();
    }
}
