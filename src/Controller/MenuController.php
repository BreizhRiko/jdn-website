<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Form\MenuType;
use App\Repository\MenuRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('admin/menu')]
class MenuController extends AbstractController
{
    #[Route('/', name: 'menu_index', methods: ['GET'])]
    public function index(MenuRepository $menuRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $menuRepository->findAll(), // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            10 // Nombre de résultats par page
        );
        return $this->render('menu/index.html.twig', [
            'paginator' => $pagination,
        ]);
    }

    #[Route('/new', name: 'menu_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $menu = new Menu();
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            /** @var UploadedFile $image */
            $image = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $image->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );

                } catch (FileException $e) {
                    $this->addFlash(
                        'notice',
                        'Le menu \'' . $menu->getName() . '\' n\'a pas pu être ajouté suite à une erreur liée au fichier uploadé'
                    );
                    return $this->redirectToRoute('menu_index', [], Response::HTTP_SEE_OTHER);
                }
                $menu->setImage($newFilename);
            }
            $entityManager->persist($menu);
            $entityManager->flush();
            $this->addFlash(
                'notice',
                'Votre menu a bien été créé !'
            );
            return $this->redirectToRoute('menu_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('menu/new.html.twig', [
            'menu' => $menu,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'menu_show', methods: ['GET'])]
    public function show(Menu $menu): Response
    {
        return $this->render('menu/show.html.twig', [
            'menu' => $menu,
        ]);
    }

    #[Route('/{id}/edit', name: 'menu_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Menu $menu, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $image */
            $image = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $image->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash(
                        'notice',
                        'Le menu \'' . $menu->getName() . '\' n\'a pas pu être modifié suite à une erreur liée au fichier uploadé'
                    );
                    return $this->redirectToRoute('menu_index', [], Response::HTTP_SEE_OTHER);
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                if ($menu->getImage() != null) {
                    $filesystem = new Filesystem();
                    $filesystem->remove($this->getParameter('uploads_directory') . '/' . $menu->getImage());
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $menu->setImage($newFilename);
            }
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                'notice',
                'Le menu \'' . $menu->getName() . '\' a bien été modifié'
            );
            return $this->redirectToRoute('menu_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('menu/edit.html.twig', [
            'menu' => $menu,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'menu_delete', methods: ['POST'])]
    public function delete(Request $request, Menu $menu): Response
    {
        if ($this->isCsrfTokenValid('delete' . $menu->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($menu);
            $entityManager->flush();
            $this->addFlash(
                'notice',
                'Le menu \'' . $menu->getName() . '\' a bien été supprimé'
            );
        }

        return $this->redirectToRoute('menu_index', [], Response::HTTP_SEE_OTHER);
    }
}
