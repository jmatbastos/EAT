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

class AnswerController extends AbstractController
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
	 * @Route("/exam/answer/{id?}", name="exam/answer")
	 */	
		
	public function exam_answer($id = FALSE, Request $request): Response
	{
		
		if (!$this->getUser()) 
             return $this->redirectToRoute('login');
       
		
		if ($request->isMethod('POST') && $request->attributes->get('_route') === 'exam/answer') {
          
			   $token = $request->request->get("csrf_token");

			   if (!$this->isCsrfTokenValid('answer', $token)) {
				   return new Response("Operation not allowed", Response::HTTP_OK,
					   ['content-type' => 'text/plain']);
			   }
		
			    
				// get answer type
				$answer_type=$request->request->get('type');	
				$this->session->set('$answer_type', $answer_type);
				$this->session->set('$exams_id', $id);
				
				//check if previous attempt is not finished
				if ( $attempt=$this->elearn_model->get_usermadeexamsByusers_idAndByexams_id($this->getUser()->getId(), $id) )
					$this->session->set('$usermadeexams_id', $attempt['usermadeexams_id']);
				else {
					// generate new usermadeexam
					 $exam = $this->elearn_model->get_exam($id);
					 $usermadeexams_id=$this->elearn_model->add_usermadeexam($id, $this->getUser()->getId(), $exam['resolution_time']);				 
					 $this->session->set('$usermadeexams_id', $usermadeexams_id);				 				 
				} 
				
				return $this->redirectToRoute('answer_question');
		
		}		
		
		// get exam
		$exam=$this->elearn_model->get_exam($id);
		

		
		// get total points in exam		
		$query = $this->elearn_model->get_total_points_in_exam($id);
		
		
		$exam['total_points'] = $query['total_points'];
		$data['exam'] = $exam;

		return $this->render('answer/exam_answer.html.twig', $data);
		
	}
	
	
	/**
     * @Route("/question/answer", name="answer_question")
     */
    public function answer_question(Request $request): Response
    {
			
		if (!$this->getUser()) 
		 return $this->redirectToRoute('login');
       
		
		$exams_id=$this->session->get('$exams_id');
		$usermadeexams_id=$this->session->get('$usermadeexams_id');		
		$questions=$this->elearn_model->get_questions_in_exam($exams_id);
		
		
		if ($request->isMethod('POST') && $request->attributes->get('_route') === 'answer_question') {
          
			   $token = $request->request->get("csrf_token");

			   if (!$this->isCsrfTokenValid('answer', $token)) {
				   return new Response("Operation not allowed", Response::HTTP_OK,
					   ['content-type' => 'text/plain']);
			   }
		
			
			$answer_question_id=$this->session->get('$answer_question_id');
			$paginate_button=$request->request->get('paginate_button');		
			$content=$request->request->get('content');
			$timer=$request->request->get('timer');
			//$time_array=explode(":", $timer);
			//$timer=$time_array[0]*60+$time_array[1];
		
			
			// update timer
			$this->elearn_model->update_timer($usermadeexams_id,$timer);
			

			// validate answer
			$input = ['content' => $content];

			$constraints = new Assert\Collection([
				'content' => new Assert\Length(['max' => 102400,
												'maxMessage' => 'The answer content cannot be longer than {{ limit }} characters',
												]),											
			]);

			$data = $this->requestValidation($input, $constraints);

			
			
			// if answer content has no errors save or update answer to question
			if ( $data['errors'] == 0 ) {
				if ($this->elearn_model->get_answer_to_question($usermadeexams_id, $questions[$answer_question_id]['id']))
					$this->elearn_model->update_answer_to_question($usermadeexams_id, $questions[$answer_question_id]['id'], $content);
				else
					if ( $this->session->get('$answer_type') == 'text' )
						$this->elearn_model->add_answer_to_question($usermadeexams_id, $questions[$answer_question_id]['id'], $content, 5);
					else
						$this->elearn_model->add_answer_to_question($usermadeexams_id, $questions[$answer_question_id]['id'], $content, 6);
			}
			
			
			
			if ($paginate_button == "Next") {			
				$data['show_back']= true;
				if ( $data['errors'] == 0 ) $answer_question_id++;
				$this->session->set('$answer_question_id', $answer_question_id);
				if ($answer_question_id < (count($questions)-1))
					$data['show_next']= true;
			}
			
			if ($paginate_button == "Back") {
				$data['show_next']= true;
				if ( $data['errors'] == 0 ) $answer_question_id--;
				$this->session->set('$answer_question_id', $answer_question_id);				
				if ($answer_question_id > 0) 
					$data['show_back']= true;				
			}
			
			
			if ($paginate_button == "Submit" and $data['errors'] == 0) {				
				$this->elearn_model->close_usermadeexam($usermadeexams_id);				
				return new Response('<html><body onload="window.close()"></body></html>', 
										Response::HTTP_OK,
									   ['content-type' => 'text/html']);
			}
			
			if ($paginate_button == NULL) {
				$this->elearn_model->close_usermadeexam($usermadeexams_id);				
				return new Response('<html><body onload="window.close()"></body></html>', 
										Response::HTTP_OK,
									   ['content-type' => 'text/html']);
			}


			
			
			$data['q'] = $questions[$answer_question_id];
			if ($question=$this->elearn_model->get_answer_to_question($usermadeexams_id, $questions[$answer_question_id]['id']))
				$data['q']['answer'] = $question['answer'];
			

			$data['timer']= $timer;
			 
			 if ( $this->session->get('$answer_type') == 'text' )
				return $this->render('answer/answer_question.html.twig', $data);
			else
				return $this->render('answer/answer_question2.html.twig', $data);
				
		
		}
		// end method POST						
		
		
		// method is GET
		$this->session->set('$answer_question_id', 0);
		$data['errors'] = 0;
		$data['q'] = $questions[0];
		if ($question=$this->elearn_model->get_answer_to_question($usermadeexams_id, $questions[0]['id']))
				$data['q']['answer'] = $question['answer'];
		
		$data['show_next']= true;
		
		$exam = $this->elearn_model->get_usermadeexamsByID($this->session->get('$usermadeexams_id'));
		$data['timer']= $exam['timer'];
		if ( $this->session->get('$answer_type') == 'text' )
			return $this->render('answer/answer_question.html.twig', $data);
		else
			return $this->render('answer/answer_question2.html.twig', $data);
	
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
