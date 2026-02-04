<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Controller\Elearn_modelController;

class ElearnController extends AbstractController
{
    
	private $session;
	private $elearn_model;
	private $validator;
	
	public function __construct(SessionInterface $session, Elearn_modelController $elearn_model, ValidatorInterface $validator)
    {
		$this->session = $session;
		$this->elearn_model = $elearn_model;
        $this->validator = $validator;
    }
	
		
	/**
     * @Route("/elearn", name="elearn")
     */
    public function index(): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED'))
			return $this->redirectToRoute('menu');
		else
			return $this->render('elearn/home.html.twig', [
				'controller_name' => 'ElearnController',
			]);
    }
	
	/**
     * @Route("/menu", name="menu")
     */
    public function menu(): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED'))
			return $this->redirectToRoute('elearn');
				
		if ($this->isGranted('ROLE_TEACHER'))
			$data['curricularunits'] = $this->elearn_model->get_curricularunits();
		else{
			$data['users_id'] = $this->getUser()->getId();
			$data['curricularunits'] = $this->elearn_model->get_curricularunits_enrolled($this->getUser()->getId());
		}
			

		
		return $this->render('elearn/menu.html.twig', $data);
    }
	
	
	
}
