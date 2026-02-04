<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\UserAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email; 
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use App\Repository\UserRepository;

class RegistrationController extends AbstractController
{
    
    private $mailer;
    private $userRepository;
  
    public function __construct(MailerInterface $mailer, UserRepository $userRepository)
    {
        $this->mailer = $mailer;
        $this->userRepository = $userRepository;

    }    
    
    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, UserAuthenticator $authenticator): Response
    {
		
		$user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
			$user->setRoles( array( $form->get('role')->getData() ) );
			$user->setCreatedAt(new \DateTime());
			$user->setUpdatedAt(new \DateTime());
            // activation digest
            $activation_digest = substr(md5(time()),0,32);
            $user->setActivationDigest($activation_digest);
            // user registered by 
            if ($this->isGranted('ROLE_TEACHER')){
                $user->setCreatedBy($this->getUser()->getId());
            }
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
	

/*
			return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
*/	

            if ($this->isGranted('ROLE_TEACHER')){
                $this->addFlash('success', "User Created, Enroll User now in a Curricular Unit");				
			    return $this->redirectToRoute('users');
            }
            else{
                //send activation email
                $this->sendEmail($user->getEmail(), $user->getName(), $activation_digest);
                $data['message'] = "User Created, Please check your email to activate your account.";               
                return $this->render('registration/message.html.twig', $data);
            }



        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/account/activate/{id?}", name="account/activate")
     */
    public function acountActivate($id = FALSE): Response
    {
		if ($id)	
            if ($this->userRepository->findUserByActivationDigest($id)) {
                $this->userRepository->activateAccount($id);
                $data['message'] = "Your email address has been successfully validated. You can now login to your account.";	
            }
            else
                $data['message'] = "ERRO: Token não é valido ou expirou.";

            return $this->render('registration/activate.html.twig', $data);

	}

    
    
    private function sendEmail($user_email, $user_name, $reset_digest)
    {
        $email = (new TemplatedEmail())
        ->from('jbastos@ualg.pt')
        ->to(new Address($user_email))
        ->subject('Registo na aplicação EAT')
        // path of the Twig template to render
        ->htmlTemplate('registration/email.html.twig')
        // pass variables (name => value) to the template
        ->context([
        'expiration_date' => new \DateTime('+1 days'),
        'username' => $user_name,
        'reset_digest' => $reset_digest,
        ]);
        $this->mailer->send($email);
        return true;
 
    }

}

