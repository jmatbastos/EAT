<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Controller\Elearn_modelController;

class AttemptController extends AbstractController
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
	 * @Route("/attempts/{id?}", name="attempts")
	 */	
		
	public function attempts($id = FALSE): Response	
	{
		if ($id)
			$attempts=$this->elearn_model->get_attemptsByCurricularunitID($id);		
		else		
			$attempts=$this->elearn_model->get_attempts();
	
		

			$filtered_arr = [];
			foreach($attempts as $attempt) {
					if($attempt['exams_users_id'] == $this->getUser()->getId() || $attempt['users_id'] == $this->getUser()->getId() || $this->isGranted('ROLE_ADMIN')) {
						$filtered_arr[] = $attempt;
				}
			}
			$data['attempts'] = $filtered_arr;	



		
		$data['users_id']=$this->getUser()->getId();	
		
		$this->session->set('curricularunitID', $id); 
		
		return $this->render('attempts/attempts_list.html.twig', $data);
	}
	
	
	/**
	 * @Route("/attempt/del/{id?}", name="attempt/del")
	 */	
		
	public function attempt_del($id = FALSE, Request $request): Response
	{
		
		$this->denyAccessUnlessGranted('ROLE_TEACHER', null, 'Access Denied.');

		$this->elearn_model->del_attempt($id);
		$referer=$request->headers->get('referer');
		return new RedirectResponse($referer);
		
    }


	
	/**
	 * @Route("/attempt/show/{id?}", name="attempt/show")
	 */	
		
	public function attempt_show($id = FALSE, Request $request): Response
	{
		
		$attempt=$this->elearn_model->get_attempt($id);
		$exam=$this->elearn_model->get_exam($attempt['exams_id']);
		
		
		// get questions & answers  in attempt
		
		$questions=$this->elearn_model->get_questions_in_exam($attempt['exams_id']);
		$answers=$this->elearn_model->get_answers_in_attempt($id);
		
		
		// join queries together
		
		
		for ($i = 0; $i < count($questions); $i++) {					
					for ($j = 0; $j < count($answers); $j++) {
						if ( $answers[$j]['id'] == $questions[$i]['id'] ){ 
							$questions[$i]['answer'] = $answers[$j]['answer'] ;
							$questions[$i]['answertypes_id'] = $answers[$j]['answertypes_id'] ;
						}	
					}
		}

		
		
		$total_points = 0;
		for ($i = 0; $i < count($questions); $i++) {
			$total_points = $total_points + $questions[$i]['complexities_id'];
		}
		
		
		
		
		$exam['total_points'] = $total_points;
		$data['attempt'] = $attempt;		
		$data['exam'] = $exam;
		$data['questions'] = $questions;
		$data['referer'] = $request->headers->get('referer');
		return $this->render('attempts/attempt_show.html.twig', $data);
		

		
		
	}
	
	/**
	 * @Route("/attempt/show/answer/{id?}", name="attempt/show/answer")
	 */	
		
	public function attempt_show_answer($id = FALSE, Request $request): Response
	{
	
		if ($request->isMethod('POST') && $request->attributes->get('_route') === 'attempt/show/answer') {
          
			$token = $request->request->get("csrf_token");

			if (!$this->isCsrfTokenValid('show', $token)) {
				return new Response("Operation not allowed", Response::HTTP_OK,
					['content-type' => 'text/plain']);
			}
		
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
		
		// method is GET
		
		$data['a']=$this->elearn_model->get_answer_to_questionbyID($id);	
		$data['q']=$this->elearn_model->get_questionbyID($data['a']['questions_id']);
		$data['q']['marks']=$this->elearn_model->get_question_marks_($data['a']['usermadeexams_id'],$data['a']['questions_id'])['marks'];
		$data['id'] = $id;
		return $this->render('attempts/attempt_show_answer.html.twig', $data); 
		
	}
	
	
	/**
	 * @Route("/attempt/grade/{id?}", name="attempt/grade") 
	 */	
		
	public function attempt_grade($id = FALSE, Request $request): Response
	{
		
		// $this->denyAccessUnlessGranted('ROLE_TEACHER', null, 'Access Denied.');
		
		$data['curricularunitID']=$this->session->get('curricularunitID');
	
		
		
		$attempt=$this->elearn_model->get_attempt($id);
		$exam=$this->elearn_model->get_exam($attempt['exams_id']);
		
		
		// get questions & answers  in attempt
		
		$questions=$this->elearn_model->get_questions_in_exam($attempt['exams_id']);
		$answers=$this->elearn_model->get_answers_in_attempt($id);
		
		
		// join queries together
		
		
		for ($i = 0; $i < count($questions); $i++) {
					$questions[$i]['answer'] = '';
					for ($j = 0; $j < count($answers); $j++) {
						if ( $answers[$j]['id'] == $questions[$i]['id'] ) {
							$questions[$i]['answer'] = $answers[$j]['answer'] ;
							$questions[$i]['answers_id'] = $answers[$j]['answers_id'] ;
							$questions[$i]['grade'] = $answers[$j]['grade'] ;
							$questions[$i]['answertypes_id'] = $answers[$j]['answertypes_id'] ;
						}
					}
		}

		
		
		$total_points = 0;
		for ($i = 0; $i < count($questions); $i++) {
			$total_points = $total_points + $questions[$i]['marks'];
		}
		
	

		if (preg_match("/exam\/results/", $request->server->get('HTTP_REFERER')))
			$this->session->set('referer', $request->headers->get('referer'));
		
		if (preg_match("/attempts/", $request->server->get('HTTP_REFERER')))
			$this->session->set('referer', $request->headers->get('referer'));
		

		//$data['referer'] = $request->headers->get('referer');		
		$data['referer'] = $this->session->get('referer');
		$exam['total_points'] = $total_points;
		$data['attempt'] = $attempt;		
		$data['exam'] = $exam;
		$data['questions'] = $questions;
		return $this->render('attempts/attempt_grade.html.twig', $data);
	}
	
	/**
	 * @Route("/attempt/grade/answer/{id?}", name="attempt/grade/answer") 
	 */	
		
	public function attempt_grade_answer($id = FALSE, Request $request): Response
	{
		if ($this->getUser())
		{
			
		   if ($request->isMethod('POST') && $request->attributes->get('_route') === 'attempt/grade/answer') {
          
			   $token = $request->request->get("csrf_token");

			   if (!$this->isCsrfTokenValid('grade', $token)) {
				   return new Response("Operation not allowed", Response::HTTP_OK,
					   ['content-type' => 'text/plain']);
			   }


				$content=$request->request->get('content');
				$grade=$request->request->get('grade');				
				
				$input = ['content' => $content, 'grade' => $grade];

				$constraints = new Assert\Collection([
				'content' => new Assert\Length(['max' => 102400,
												'maxMessage' => 'The comments content cannot be longer than {{ limit }} characters',
												]),
				'grade' => new Assert\LessThan(['value' => 20,
											   'message' => 'The grade cannot be longer than {{ compared_value }} points',
												]),													
			   ]);

				$data = $this->requestValidation($input, $constraints);
				  
				if ( $data['errors'] > 0) {
						$data['answer'] = $this->elearn_model->get_answer_to_questionbyID($id);
						$data['question'] = $this->elearn_model->get_questionById($data['answer']['questions_id']);						
						return $this->render('attempts/attempt_grade_answer.html.twig', $data);
				}  				  
			  
 
			   $this->elearn_model->update_answer_in_attempt($id, 
												 $content,
												 $grade
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
		    
			// method is GET
			
			$data['errors'] = 0;
			$data['answer'] = $this->elearn_model->get_answer_to_questionbyID($id);
			$data['question'] = $this->elearn_model->get_questionById($data['answer']['questions_id']);
			$data['question']['marks']=$this->elearn_model->get_question_marks_($data['answer']['usermadeexams_id'],$data['answer']['questions_id'])['marks'];
			return $this->render('attempts/attempt_grade_answer.html.twig', $data);
		
		}
		
		return $this->redirectToRoute('login');	
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
