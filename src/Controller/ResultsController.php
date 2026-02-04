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

class ResultsController extends AbstractController
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
     * @Route("/exam/results/{id?}", name="exam/results")
     */
    public function exam_results($id = FALSE, Request $request): Response
    {
		
		$data['exams_id']= $id;
		
		// get total points in exam		
		$query = $this->elearn_model->get_total_points_in_exam($id);		
		$data['total_points'] = $query['total_points'];
		
		
		// get questions
		
		$data['questions']=$this->elearn_model->get_questions_in_exam($id);
		$attempts=$this->elearn_model->get_attempts($id);
	
 

		
		for ($i = 0; $i <count($attempts); $i++) {
			// join answers to questions 
			$attempts[$i]['results'] = $this->elearn_model->get_answers_to_questions($attempts[$i]['usermadeexams_id']);

			// join maximum mark to questions
			for ($j = 0; $j < count($attempts[$i]['results']); $j++) {	
				$attempts[$i]['results'][$j]['marks']=$this->elearn_model->get_question_marks($id, $attempts[$i]['results'][$j]['questions_id'])['marks'];
			};		

			// get total grade in attempt
			$query = $this->elearn_model->get_total_grade_in_attempt($attempts[$i]['usermadeexams_id']);
			$attempts[$i]['total_grade'] = $query['total_grade'];
		} 


		$data['attempts']= $attempts;
		 
		$data['curricularunits_id'] = $this->session->get('curricularunits_id');
		
		return $this->render('results/results.html.twig', $data);		
	}
	
	/**
     * @Route("/exam/csvresults/{id?}", name="exam/csv_results")
     */
    public function csv_results($id = FALSE): Response
    {
        
		$exam=$this->elearn_model->get_exam($id);
		$attempts=$this->elearn_model->get_attempts($id);
		
		$list[0] = array('name' => 'Name',
						'email' => 'Email',
						'start_date' => 'Started on',
						'finish_date' => 'Completed',
						'total_grade' => 'Grade/20'); 
		for ($i = 0; $i <$exam['exams_questions']; $i++) {
			$index = $i+1 	;		
			$list[0][$i]= "#" . $index ;
		}
		
		for ($i = 0; $i < count($attempts); $i++) {
			$list[$i+1]['user']=$attempts[$i]['user'];
			$list[$i+1]['email']=$attempts[$i]['email'];
			$list[$i+1]['start_date']=$attempts[$i]['start_date'];	
			$list[$i+1]['finish_date']=$attempts[$i]['finish_date'];			
			// get total grade in attempt
			$query = $this->elearn_model->get_total_grade_in_attempt($attempts[$i]['usermadeexams_id']);
			$list[$i+1]['total_grade'] = $query['total_grade'];			
			
			// join answers 
			$answers = $this->elearn_model->get_answers_to_questions($attempts[$i]['usermadeexams_id']);
			for ($j = 0; $j <count($answers); $j++) 
				$list[$i+1][$j]=$answers[$j]['grade'];						
			for ($j = count($answers); $j <$exam['exams_questions']; $j++) 
				$list[$i+1][$j]='0';
		} 
			

		$fp = fopen('php://temp', 'w');
		foreach ($list as $key => $field) {
			fputcsv($fp, $field);
		}

		rewind($fp);
		$response = new Response(stream_get_contents($fp));
		fclose($fp);

		$response->headers->set('Content-Type', 'text/csv');
		$filename=$exam['exams_name'] .  '-' . date("YmdHis") . '.csv';
		$response->headers->set('Content-Disposition', "attachment; filename=$filename");

		return $response;
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
