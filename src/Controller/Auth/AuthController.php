<?php

namespace App\Controller\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use App\Entity\User;

class AuthController extends AbstractController
{
    #[Route('/register', name: 'register')]
    public function register(): Response
    {
        return $this->render('auth/register.html.twig');
    }

    #[Route('/forgot', name: 'app_forgot')]
    public function forgot(Request $request, ManagerRegistry $doctrine, MailerInterface $mailer): Response
    {
        $email = $request->request->get('_email');

        if ($email) {
            $user = $doctrine->getRepository(User::class)->findOneBy(['email' => $email]);
            if (!$user) {
                $this->addFlash('error', 'Aucun utilisateur trouvé avec cette adresse email.');
                return $this->redirectToRoute('forgot');
            }

            $resetToken = Uuid::v4();
            $user->setResetToken($resetToken);

            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $resetLink = $this->generateUrl(
                'reset', 
                ['token' => $resetToken],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $email = (new Email())
                ->from('noreply@example.com')
                ->to($user->getEmail())
                ->subject('Réinitialisation de votre mot de passe')
                ->text('Voici le lien pour réinitialiser votre mot de passe : ' . $resetLink);
  
            $mailer->send($email);

            $this->addFlash('success', 'Un email de réinitialisation de mot de passe a été envoyé.');

            return $this->redirectToRoute('app_login');
        }
        
        return $this->render('auth/forgot.html.twig');
    }

    #[Route('/reset/{token}', name: 'app_reset')]
    public function reset(Request $request, string $token, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $doctrine->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if (!$user) {
            throw $this->createNotFoundException('Jeton de réinitialisation invalide.');
        }
        if ($request->isMethod('POST')) {
            /** @var string $password */
            $password = $request->get('password');
            $repeatPassword = $request->get('repeat_password');
    
            if ($password !== $repeatPassword) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->redirectToRoute('reset', ['token' => $token]);
            }
    
            // Hacher et enregistrer le nouveau mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
            $user->setResetToken(null);
    
            $entityManager = $doctrine->getManager();
            $entityManager->flush();
    
            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');
            return $this->redirectToRoute('app_login');
        }
    
        return $this->render('auth/reset.html.twig', [
            'token' => $token,
            'email' => $user->getEmail(),
        ]);
    }
}
