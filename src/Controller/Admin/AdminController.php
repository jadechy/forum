<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use App\Repository\CategoryRepository;
use App\Repository\LanguageRepository;
use App\Repository\UserRepository;
use App\Repository\ResponseRepository;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route(path: '/', name: 'app_admin_homepage')]
    public function admin(): Response
    {
        return $this->render('admin/admin.html.twig');
    }

    #[Route('/category', name: 'admin_category')]
    public function adminCatgories(CategoryRepository $categoryRepository): Response
    {
        return $this->render('admin/category.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/language', name: 'admin_language')]
    public function adminLanguages(LanguageRepository $languageRepository): Response
    {
        return $this->render('admin/language.html.twig', [
            'languages' => $languageRepository->findAll(),
        ]);
    }

    #[Route('/user', name: 'admin_user')]
    public function adminUsers(UserRepository $userRepository): Response
    {
        return $this->render('admin/user.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/response', name: 'admin_response')]
    public function adminResponse(ResponseRepository $responseRepository): Response
    {
        return $this->render('admin/response.html.twig', [
            'responses' => $responseRepository->findAll(),
        ]);
    }
}
