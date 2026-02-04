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

class UserController extends AbstractController
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
	 * @Route("/users", name="users")
	 */	
		
	public function users(): Response	
	{
		
		$users = $this->elearn_model->get_users();

		$filtered_arr = [];
		foreach($users as $user) {
				if($user['created_by'] == $this->getUser()->getId() || $user['users_id'] == $this->getUser()->getId() || $this->isGranted('ROLE_ADMIN')) {
					$filtered_arr[] = $user;
			}
		}
		$data['users'] = $filtered_arr;
 
		return $this->render('user/users_list.html.twig', $data);
	}

	/**
	 * @Route("/user/delete/{users_id?}", name="user_delete")
	 */	
		
	public function user_delete($users_id): Response	
	{		
		if ( !empty($this->elearn_model->get_attemptsByUserID($users_id))  )		
            $this->addFlash(
                'notice', 'User has attempts'
			);
		else
			$this->elearn_model->del_user($users_id);	
		
		return $this->redirectToRoute('users');	
	}

	/**
	 * @Route("/user/enroll/{users_id?}", name="user_enroll")
     */
    public function user_enroll($users_id = FALSE, Request $request): Response	
	{
					$data['users_id']=$users_id;
					
					// get enrolled curricular units

						$enrolled_curricular_units=$this->elearn_model->get_curricular_units_enrolled($users_id);
						$data['enrolled_curricular_units'] = $enrolled_curricular_units; 

					
					
					// get curricular units not enrolled

						$curricular_units_not_enrolled=$this->elearn_model->get_curricular_units_not_enrolled($users_id);
						$data['curricular_units_not_enrolled'] = $curricular_units_not_enrolled;

					
					 
					 return $this->render('user/user_enroll.html.twig', $data);
	}




	/**
     * @Route("/user/enroll/unit/{id?}/{users_id?}", name="user_enroll_unit")
     */
    public function user_enroll_unit($id, $users_id): Response	
	{		


		$this->elearn_model->user_enroll_unit($id, $users_id);
		return $this->redirectToRoute('user_enroll', ['users_id' => $users_id]);	
			
	}

	/**
     * @Route("/user/unenroll/unit/{id?}/{users_id?}", name="user_unenroll_unit")
     */
    public function user_unenroll_unit($id, $users_id): Response	
	{		
		$this->elearn_model->user_unenroll_unit($id, $users_id);
		$this->elearn_model->user_unenroll_reviewer($id, $users_id);	
		return $this->redirectToRoute('user_enroll' , ['users_id' => $users_id]);
	}


	/**
     * @Route("/user/enroll/reviewer/{id?}/{users_id?}", name="user_enroll_reviewer")
     */
    public function user_enroll_reviewer($id, $users_id): Response	
	{		


		$this->elearn_model->user_enroll_reviewer($id, $users_id);
		return $this->redirectToRoute('user_enroll', ['users_id' => $users_id]);	
			
	}

	/**
     * @Route("/user/unenroll/reviewer/{id?}/{users_id?}", name="user_unenroll_reviewer")
     */
    public function user_unenroll_reviewer($id, $users_id): Response	
	{		
		$this->elearn_model->user_unenroll_reviewer($id, $users_id);
		return $this->redirectToRoute('user_enroll' , ['users_id' => $users_id]);
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
