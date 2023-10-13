<?php

namespace App\Controller;

use App\Entity\Table;
use App\Form\TableType;
use App\Repository\TableRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('admin/table')]
class TableController extends AbstractController
{
    #[Route('/', name: 'table_index', methods: ['GET'])]
    public function index(TableRepository $tableRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $tableRepository->findAll(), // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            10 // Nombre de résultats par page
        );
        return $this->render('table/index.html.twig', [
            'paginator' => $pagination,
        ]);
    }

    #[Route('/new', name: 'table_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $table = new Table();
        $form = $this->createForm(TableType::class, $table);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($table);
            $entityManager->flush();

            $this->addFlash(
                'notice',
                'La table \'' . $table->getName() . ' a bien été crée.'
            );
            return $this->redirectToRoute('table_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('table/new.html.twig', [
            'table' => $table,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'table_show', methods: ['GET'])]
    public function show(Table $table): Response
    {
        return $this->render('table/show.html.twig', [
            'table' => $table,
        ]);
    }

    #[Route('/{id}/lock', name: 'table_toggle_lock', methods: ['GET'])]
    public function toggleLock(Table $table): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $table->toggleAvailable();
        $entityManager->persist($table);
        $entityManager->flush();
        return $this->redirectToRoute('table_edit', ['id' => $table->getId()]);
    }

    #[Route('/{id}/edit', name: 'table_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Table $table): Response
    {
        $form = $this->createForm(TableType::class, $table);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash(
                'notice',
                'La table \'' . $table->getName() . ' a bien été modifiée.'
            );
            return $this->redirectToRoute('table_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('table/edit.html.twig', [
            'table' => $table,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'table_delete', methods: ['POST'])]
    public function delete(Request $request, Table $table): Response
    {
        if ($this->isCsrfTokenValid('delete' . $table->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($table);
            $entityManager->flush();
            $this->addFlash(
                'notice',
                'La table \'' . $table->getName() . ' a bien été supprimée.'
            );
        }

        return $this->redirectToRoute('table_index', [], Response::HTTP_SEE_OTHER);
    }
}
