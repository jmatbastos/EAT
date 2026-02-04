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

class CurricularunitController extends AbstractController
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
	 * @Route("/curricularunits", name="curricularunits")
	 */	
		
	public function curricularunits(): Response	
	{
		
		$data['curricularunits']=$this->elearn_model->get_curricularunits();
		$data['users_id']=$this->getUser()->getId();
		return $this->render('curricularunit/curricularunits_list.html.twig', $data);
	}
	

	
	
	/**
	 * @Route("/curricularunits/show/{id?}", name="show_curricularunit")
	 */	
		
	public function show_curricularunit($id): Response	
	{		
		$this->session->set('curricularunits_id', $id);
		$curricularunit=$this->elearn_model->get_curricularunitByID($id);
		$curricularchapters=$this->elearn_model->get_curricularchapters($id);

		for ($i = 0; $i < count($curricularchapters); $i++) { 
			$curricularchapters[$i]['curricularsections']= $this->elearn_model->get_curricularsections($curricularchapters[$i]['curricularchapters_id']);
			for ($j = 0; $j < count($curricularchapters[$i]['curricularsections']); $j++) 
				$curricularchapters[$i]['curricularsections'][$j]['curriculargoals']= $this->elearn_model->get_curriculargoalsByID($curricularchapters[$i]['curricularsections'][$j]['curricularsections_id']);
		}

		if ( $curricularunit['users_id'] == $this->getUser()->getId() || $this->isGranted('ROLE_ADMIN') )
			$data['owner']=true;
		else
			$data['owner']=false;
			
		$data['curricularunit']=$curricularunit;
		$data['curricularchapters']=$curricularchapters;

		return $this->render('curricularunit/curricularunit_show.html.twig', $data);
	}
	
	/**
	 * @Route("/curricularunits/edit/{id?}", name="edit_curricularunit")
	 */	
		
	public function edit_curricularunit($id,Request $request): Response	
	{
		if ($request->isMethod('POST') && $request->attributes->get('_route') === 'edit_curricularunit') {
          
		   $token = $request->request->get("csrf_token");

		   if (!$this->isCsrfTokenValid('edit_curricularunit', $token)) {
			   return new Response("Operation not allowed", Response::HTTP_OK,
				   ['content-type' => 'text/plain']);
		   }
		
			
			$name=$request->request->get('name');
			$year=$request->request->get('year');
			if ( $this->elearn_model->get_curricularunitByID($id)['users_id'] == $this->getUser()->getId() || $this->isGranted('ROLE_ADMIN') )
				$this->elearn_model->update_curricularunit($id, $name, $year);
			return $this->redirectToRoute('show_curricularunit', ['id' => $id]);
		
		
		}
		
		//method is GET, should not be here!				
		return $this->redirectToRoute('show_curricularunit', ['id' => $id]);
	}	
	
	
	
	
	/**
	 * @Route("/curricularunits/delete/{id?}", name="del_curricularunit")
	 */	
		
	public function del_curricularunit($id): Response	
	{
		if ( $this->elearn_model->get_curricularunitByID($id)['users_id'] != $this->getUser()->getId() && !$this->isGranted('ROLE_ADMIN') )
			$this->addFlash(
				'notice', 'Permission denied, Curricular Unit does not belong to you' 
			);			
		elseif ( !empty($this->elearn_model->get_examsByCurricularunitID($id))  )		
            $this->addFlash(
                'notice', 'Permission denied, Curricular Unit has exams'
			);
		elseif ( !empty($this->elearn_model->get_questionsByCurricularunitsID($id))  )	
            $this->addFlash(
                'notice', 'Permission denied, Curricular Unit has items'
			);
		elseif ( !empty($this->elearn_model->get_attemptsByCurricularunitID($id))  )	
            $this->addFlash(
                'notice', 'Permission denied, Curricular Unit has attempts'
			);			
		else
			$this->elearn_model->del_curricularunit($id);	
		
		return $this->redirectToRoute('curricularunits');	
	}
	
	/**
	 * @Route("/curricularunits/add", name="add_curricularunit")
	 */	
		
	public function add_curricularunit(Request $request): Response	
	{		
		if ($request->isMethod('POST') && $request->attributes->get('_route') === 'add_curricularunit') {
          
			   $token = $request->request->get("csrf_token");

			   if (!$this->isCsrfTokenValid('curricularunit', $token)) {
				   return new Response("Operation not allowed", Response::HTTP_OK,
					   ['content-type' => 'text/plain']);
			   }
						 
			   $name=$request->request->get('name');
			   $year=$request->request->get('year');			   
			   $language=$request->request->get('language');	
			   
				// data validation
				
				$input = ['name' => $name, 'year' => $year, 'language' => $language];				
				$constraints = new Assert\Collection([
				'name' => [new Assert\NotBlank(['message' => 'The name must not be blank']),
				           new Assert\Length(['max' => 60,
											  'maxMessage' => 'The name cannot be longer than {{ limit }} characters',
												])
						  ],
				'year' => [new Assert\NotBlank(['message' => 'The year is not chosen']),
				           new Assert\Range(['min' => 10,
											 'max' => 12,
											 'notInRangeMessage' => 'The year must be 10, 11 or 12',
											]),
						   new Assert\Type([
										'type' => 'numeric',
										'message' => 'The year must be an integer',
									])											
						  ],
				'language' => new Assert\NotBlank(['message' => 'The language is not chosen'])
						  ,						  
			   ]);

				$data = $this->requestValidation($input, $constraints);
				  
				if ( $data['errors'] > 0) {
					$data['languages'] = $this->elearn_model->get_languages();
					return $this->render('curricularunit/curricularunit.html.twig', $data);
				} 			   


				 // OK, generate new curricular unit
				 
				$curricularunits_id=$this->elearn_model->add_curricularunit($name, 
										 $year,
										 $language,
										 $this->getUser()->getId()
										 );	
				$this->session->set('curricularunits_id', $curricularunits_id);
				return $this->redirectToRoute('add_curricularchapters');							 
		
		}	

			// Method is GET
			$data['errors'] = 0;
			$data['languages'] = $this->elearn_model->get_languages();
			return $this->render('curricularunit/curricularunit.html.twig', $data);				
	
	}
	
	
	/**
	 * @Route("/curricularchapters/add", name="add_curricularchapters")
	 */	
		
	public function add_curricularchapters(Request $request): Response	
	{
		if ($request->isMethod('POST') && $request->attributes->get('_route') === 'add_curricularchapters') {
          
		   $token = $request->request->get("csrf_token");

		   if (!$this->isCsrfTokenValid('curricularchapter', $token)) {
			   return new Response("Operation not allowed", Response::HTTP_OK,
				   ['content-type' => 'text/plain']);
		   }
					 
		   if ( $this->elearn_model->get_curricularunitByID($this->session->get('curricularunits_id'))['users_id'] != $this->getUser()->getId() && !$this->isGranted('ROLE_ADMIN') )
				$this->addFlash(
					'notice', 'Permission denied: Curricular Unit does not belong to you' 
				);
		   else {
			$name=$request->request->get('name');
		   
			if ( array_search("", $name) === false ){
					// insert chapters in database
				 
				 for ($i = 0; $i < count($name); $i++) 
				 $this->elearn_model->add_curricularchapter($name[$i], 
									 $this->session->get('curricularunits_id'),
									 $this->getUser()->getId()
									 );	
							 
				 return $this->redirectToRoute('add_curricularsections'); 
			}
			else 
			 $this->addFlash(
				 'notice', 'Please enter a Curricular Chapter Name'
			 );
		   }
		 



		   
		   
			
		}	
		// Method is GET
		$data['curricularunits_id'] = $this->session->get('curricularunits_id');		
		return $this->render('curricularunit/curricularchapter.html.twig', $data);	
		
	}
	
	/**
	 * @Route("/curricularchapters/edit/{id?}", name="edit_curricularchapter")
	 */	
		
	public function edit_curricularchapter($id,Request $request): Response	
	{
		if ($request->isMethod('POST') && $request->attributes->get('_route') === 'edit_curricularchapter') {
          
		   $token = $request->request->get("csrf_token");

		   if (!$this->isCsrfTokenValid('edit_curricularchapter', $token)) {
			   return new Response("Operation not allowed", Response::HTTP_OK,
				   ['content-type' => 'text/plain']);
		   }
		   if ( $this->elearn_model->get_curricularunitByID($this->session->get('curricularunits_id'))['users_id'] != $this->getUser()->getId() && !$this->isGranted('ROLE_ADMIN') )
				$this->addFlash(
					'notice', 'Permission denied: Curricular Unit does not belong to you' 
				);
		   else {		
						$name=$request->request->get('name');
						if ( $this->elearn_model->get_curricularchapterById($id)['users_id'] == $this->getUser()->getId() || $this->isGranted('ROLE_ADMIN') )
							$this->elearn_model->update_curricularchapter($id, $name);
						// everything ok, close window
						return new Response('<html><body onload="window.close()"><script>
				window.onunload = refreshParent;
				function refreshParent() {
					window.opener.location.reload();
				}
			</script></body></html>', 
													Response::HTTP_OK,
												['content-type' => 'text/html']);
			}
		}
		
		//method is GET
				
		$data['curricularchapter']=$this->elearn_model->get_curricularchapterById($id);
		$data['errors'] = 0;
		return $this->render('curricularunit/edit_curricularchapter.html.twig', $data);
	}	
	
	
	/**
	 * @Route("/curricularsections/add", name="add_curricularsections")
	 */	
		
	public function add_curricularsections(Request $request): Response	
	{
		if ($request->isMethod('POST') && $request->attributes->get('_route') === 'add_curricularsections') {
          
		   $token = $request->request->get("csrf_token");

		   if (!$this->isCsrfTokenValid('curricularsection', $token)) {
			   return new Response("Operation not allowed", Response::HTTP_OK,
				   ['content-type' => 'text/plain']);
		   }

		   if ( $this->elearn_model->get_curricularunitByID($this->session->get('curricularunits_id'))['users_id'] != $this->getUser()->getId() && !$this->isGranted('ROLE_ADMIN') )
				$this->addFlash(
					'notice', 'Permission denied: Curricular Unit does not belong to you' 
				);
		   else {		   
				$name=$request->request->get('name');
				$curricularchapter=$request->request->get('curricularchapter');
				if ( array_search("", $name) === false  &&  $curricularchapter !== null && count($name)==count($curricularchapter)){
				
					// insert sections in database
				
					for ($i = 0; $i < count($name); $i++) 
					$this->elearn_model->add_curricularsection($name[$i], 
											$curricularchapter[$i],
											$this->getUser()->getId()
											);	
								
					return $this->redirectToRoute('add_curriculargoals');
				}
				elseif (array_search("", $name) !== false) 		
					$this->addFlash(
						'notice', 'Please enter a Curricular Section Name'
					);
				elseif ($curricularchapter === null || count($name)!=count($curricularchapter))
					$this->addFlash(
						'notice', 'Please choose a Curricular Chapter Name'
					);
			}
			
		}	
		// Method is GET
		$data['errors'] = 0;
		$data['curricularunits_id'] = $this->session->get('curricularunits_id');		
		$data['curricularchapter']=$this->elearn_model->get_curricularchapters($this->session->get('curricularunits_id'));
		
		return $this->render('curricularunit/curricularsection.html.twig', $data);	
		
	}


	
	/**
	 * @Route("/curricularsections/edit/{id?}", name="edit_curricularsection")
	 */	
		
	public function edit_curricularsection($id,Request $request): Response	
	{
		if ($request->isMethod('POST') && $request->attributes->get('_route') === 'edit_curricularsection') {
          
		   $token = $request->request->get("csrf_token");

		   if (!$this->isCsrfTokenValid('edit_curricularsection', $token)) {
			   return new Response("Operation not allowed", Response::HTTP_OK,
				   ['content-type' => 'text/plain']);
		   }
		   if ( $this->elearn_model->get_curricularunitByID($this->session->get('curricularunits_id'))['users_id'] != $this->getUser()->getId() && !$this->isGranted('ROLE_ADMIN') )
				$this->addFlash(
					'notice', 'Permission denied: Curricular Unit does not belong to you' 
				);
		   else {		
						$name=$request->request->get('name');
						if ( $this->elearn_model->get_curricularsectionById($id)['users_id'] == $this->getUser()->getId() || $this->isGranted('ROLE_ADMIN') )
							$this->elearn_model->update_curricularsection($id, $name);
						// everything ok, close window
						return new Response('<html><body onload="window.close()"><script>
				window.onunload = refreshParent;
				function refreshParent() {
					window.opener.location.reload();
				}
			</script></body></html>', 
													Response::HTTP_OK,
												['content-type' => 'text/html']);
			}
		}
		
		//method is GET
				
		$data['curricularsection']=$this->elearn_model->get_curricularsectionById($id);
		$data['errors'] = 0;
		return $this->render('curricularunit/edit_curricularsection.html.twig', $data);
	}


	/**
	 * @Route("/curriculargoals/add", name="add_curriculargoals")
	 */	
		
	public function add_curriculargoals(Request $request): Response	
	{
		if ($request->isMethod('POST') && $request->attributes->get('_route') === 'add_curriculargoals') {
          
		   $token = $request->request->get("csrf_token");

		   if (!$this->isCsrfTokenValid('curriculargoal', $token)) {
			   return new Response("Operation not allowed", Response::HTTP_OK,
				   ['content-type' => 'text/plain']);
		   }

		   if ( $this->elearn_model->get_curricularunitByID($this->session->get('curricularunits_id'))['users_id'] != $this->getUser()->getId() && !$this->isGranted('ROLE_ADMIN') )
				$this->addFlash(
					'notice', 'Permission denied: Curricular Unit does not belong to you' 
				);
		   else {
			
				$description=$request->request->get('description');
				$curricularsection=$request->request->get('curricularsection');
				if ( array_search("", $description) === false  &&  $curricularsection !== null && count($description)==count($curricularsection)) {
					// insert goals in database
					
					for ($i = 0; $i < count($description); $i++) 
						$this->elearn_model->add_curriculargoal($description[$i], 
											$curricularsection[$i],
											$this->getUser()->getId()
											);	
									

					// everything ok, close window
					return new Response('<html><body onload="window.close()"><script>
					window.onunload = refreshParent;
					function refreshParent() {
						window.opener.location.reload();
					}
				</script></body></html>', 
														Response::HTTP_OK,
													['content-type' => 'text/html']);
					
				}
				elseif (array_search("", $description) !== false) 		
					$this->addFlash(
						'notice', 'Please enter a Curricular Goal'
					);
				elseif ($curricularsection === null || count($description)!=count($curricularsection))
					$this->addFlash(
						'notice', 'Please choose a Curricular Section Name'
					);
			}		
					   
			
		}	
		// Method is GET
		$data['errors'] = 0;
		
		$curricularchapter=$this->elearn_model->get_curricularchapters($this->session->get('curricularunits_id'));	

		$total_curricularsections = array();
		for ($i = 0; $i < count($curricularchapter); $i++){
				$curricularsections=$this->elearn_model->get_curricularsections($curricularchapter[$i]['curricularchapters_id']);
				$total_curricularsections = array_merge($total_curricularsections, $curricularsections);
		}

		$data['curricularsection']=$total_curricularsections;
		$data['curricularunits_id'] = $this->session->get('curricularunits_id');		
		return $this->render('curricularunit/curriculargoal.html.twig', $data);	
		
	}
	

	
	/**
	 * @Route("/curriculargoals/edit/{id?}", name="edit_curriculargoal")
	 */	
		
	public function edit_curriculargoal($id,Request $request): Response	
	{
		if ($request->isMethod('POST') && $request->attributes->get('_route') === 'edit_curriculargoal') {
          
		   $token = $request->request->get("csrf_token");

		   if (!$this->isCsrfTokenValid('edit_curriculargoal', $token)) {
			   return new Response("Operation not allowed", Response::HTTP_OK,
				   ['content-type' => 'text/plain']);
		   }
		   if ( $this->elearn_model->get_curricularunitByID($this->session->get('curricularunits_id'))['users_id'] != $this->getUser()->getId() && !$this->isGranted('ROLE_ADMIN') )
				$this->addFlash(
					'notice', 'Permission denied: Curricular Unit does not belong to you' 
				);
		   else {		
							$description=$request->request->get('description');
							if ( $this->elearn_model->get_curriculargoalById($id)['users_id'] == $this->getUser()->getId() || $this->isGranted('ROLE_ADMIN') )			
								$this->elearn_model->update_curriculargoal($id, $description);
							// everything ok, close window
							return new Response('<html><body onload="window.close()"><script>
					window.onunload = refreshParent;
					function refreshParent() {
						window.opener.location.reload();
					}
				</script></body></html>', 
														Response::HTTP_OK,
													['content-type' => 'text/html']);
			}
		}
		
		//method is GET
				
		$data['curriculargoal']=$this->elearn_model->get_curriculargoalById($id);
		$data['errors'] = 0;
		return $this->render('curricularunit/edit_curriculargoal.html.twig', $data);
	}	

		
	private function requestValidation($input, $constraints)
   {
      
           $violations = $this->validator->validate($input, $constraints);
      
               $errorMessages = [];
          
           if (count($violations) > 0) {

               $accessor = PropertyAccess::createPropertyAccessor();

               foreach ($violations as $violation) {

                   $accessor->setValue($errorMessages,
                       $violation->getPropertyPath(),
                       $violation->getMessage());
               }
          
           }   
               $data['errors'] = count($violations);
               $data['errorMessages'] = $errorMessages;
                
           return $data;
   }
	
	
	
}
