<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use App\Controller\Elearn_modelController;
use Knp\Snappy\Pdf;

class ExamController extends AbstractController
{
    
	private $session;
	private $elearn_model;
	private $validator;
    private $filesystem;
	private $pdf;
	
	public function __construct(SessionInterface $session, Elearn_modelController $elearn_model, ValidatorInterface $validator, Filesystem $filesystem, Pdf $pdf)
    {
		$this->session = $session;
		$this->elearn_model = $elearn_model;
        $this->validator = $validator;
		$this->filesystem = $filesystem;
		$this->pdf = $pdf;
    }
	
	/**
	 * @Route("/exams/{id?}", name="exams")
	 */	
		
	public function exams($id = FALSE): Response	
	{
		$this->session->set('curricularunits_id', $id);
		if ($id)
			$data['exams']=$this->elearn_model->get_examsByCurricularunitID($id);
		else
			$data['exams']=$this->elearn_model->get_exams();
		
		$data['users_id']= $this->getUser()->getId();
		return $this->render('exam/exams_list.html.twig', $data);
	}

	/**
	 * @Route("/exam/show/{id?}", name="exam/show")
	 */	
		
	public function exam_show($id = FALSE): Response
	{
		
		$exam=$this->elearn_model->get_exam($id);
		
		// get questions
		
		$questions=$this->elearn_model->get_questions_in_exam($exam['exams_id']);
		
		// get total points in exam
		
		$result = $this->elearn_model->get_total_points_in_exam($exam['exams_id']);
		
		$exam['total_points'] = $result['total_points'];
		$data['exam'] = $exam;
		$data['questions'] = $questions;
		$data['curricularunits_id'] = $this->session->get('curricularunits_id');
		$data['users_id']= $this->getUser()->getId();
		$this->session->set('exam_name', $exam['exams_name']);
		return $this->render('exam/exam_show.html.twig', $data);
		
	}

	/**
	 * @Route("/exam/pdf/{id?}", name="exam/pdf")
	 */	
		
	 public function exam_pdf($id = FALSE): Response
	 {
		   
		if ($this->getUser()) {


			$this->session->save();
			$pageUrl = $this->generateUrl('exam/show', array('id' => $id), false);
			return new Response(
				$this->pdf->getOutput($pageUrl,array('cookie' => array($this->session->getName() => $this->session->getId()))),
				200,
				array(
					'Content-Type'          => 'application/pdf',
					'Content-Disposition'   => 'attachment; filename="' . $this->session->get('exam_name') . '.pdf"'
				)
			);
		}
		return $this->redirectToRoute('login');

	 }

	
	
	/**
     * @Route("/exam/{type}", name="exam")
     */
    public function exam($type, Request $request): Response
    {
        
		if ($this->getUser())
		{			
		   
		   	if ($request->isMethod('POST') && $request->attributes->get('_route') === 'exam') {
          
			   $token = $request->request->get("csrf_token");

			   if (!$this->isCsrfTokenValid('exam', $token)) {
				   return new Response("Operation not allowed", Response::HTTP_OK,
					   ['content-type' => 'text/plain']);
			   }

				
				
				
				switch ($type) {
				case 1:
					$curricularunit=$request->request->get('curricularunit');				
					$curricularchapters=$this->elearn_model->get_curricularchapters($curricularunit);			

					$n_questions = 0;
					for ($i = 0; $i < count($curricularchapters); $i++) {
						$curricularchapters[$i]['n_questions']=$request->request->get($curricularchapters[$i]['curricularchapters_id']);
						$n_questions = $n_questions + intval($curricularchapters[$i]['n_questions']);
					} 
					
					$input = ['n_questions' => $n_questions];

					$constraints = new Assert\Collection([
					'n_questions' => new Assert\Positive([
													 'message' => 'The exam must have at least one question',
													 ]),
					]);
 
					 $data = $this->requestValidation($input, $constraints);
					   
					 if ( $data['errors'] > 0) {
						$data['type'] = 1;
						$data['curricularunits'] = $this->elearn_model->get_curricularunits();
						$data['curricularunits_id'] = $this->session->get('curricularunits_id');						
						return $this->render('exam/exam.html.twig', $data);	
					 }
			
					
					// ok continue
					$this->session->set('curricularunit', $curricularunit);
					$this->session->set('curricularchapters', $curricularchapters);
					$this->session->set('n_questions', $n_questions);					
					return $this->redirectToRoute('exam2');
					break;
				case 2:					 
					 $curricularunit=$request->request->get('curricularunit');
					 $this->session->set('curricularunit', $curricularunit);
					 
				   $name=$request->request->get('name');
				   $length=$request->request->get('length');
				   
				   $content=$request->request->get('content');			 
				   
				   $input = ['name' => $name, 'length' => $length, 'content' => $content];

				   $constraints = new Assert\Collection([
				   'name' => new Assert\NotBlank(['message' => 'The exam name must not be blank']),
				   'length' => new Assert\NotBlank(['message' => 'The exam length must not be blank']),
				   'content' => new Assert\Length(['max' => 102400,
													'maxMessage' => 'The question content cannot be longer than {{ limit }} characters',
													]),
				   ]);

					$data = $this->requestValidation($input, $constraints);
					  
					if ( $data['errors'] > 0) {
						$data['type']= 2;
						$data['curricularunits'] = $this->elearn_model->get_curricularunits();
						$data['curricularunits_id'] = $this->session->get('curricularunits_id');						
						return $this->render('exam/exam.html.twig', $data);
					}

				
					 
					 
					 // generate new exam
					 
					 $exams_id=$this->elearn_model->add_exam($curricularunit, 
											 $this->getUser()->getId(),
											 $name,
											 $content,
											 0,
											 $length
											 );
					 $this->session->set('exams_id', $exams_id);
					 
					 // remove 'curricularsections_id'
					 $this->session->remove('curricularsections_id');
					 

					 return $this->redirectToRoute('exam3');
					 break;	 
				default:
					return $this->redirectToRoute('exams', ['id' => $this->session->get('curricularunits_id')]);				
				}

		   
		   }
								
				
				
			// Method is GET

			$data['type']= $type;
			$data['errors'] = 0;
			$data['curricularunits'] = $this->elearn_model->get_curricularunits();
			$data['curricularunits_id'] = $this->session->get('curricularunits_id');
			return $this->render('exam/exam.html.twig', $data);		
		}
		
		return $this->redirectToRoute('login');
    
	}
	
	/**
     * @Route("/exam2", name="exam2")
     */
    public function exam2(Request $request): Response	
	{
         
		if ($this->getUser())
		{ 
		 
		    if ($request->isMethod('POST') && $request->attributes->get('_route') === 'exam2') {
		 
				$token = $request->get("csrf_token");

			   if (!$this->isCsrfTokenValid('exam', $token))
			   {
				   return new Response("Operation not allowed",  Response::HTTP_BAD_REQUEST,
					   ['content-type' => 'text/plain']);
			   }
		  
			   
			   $name=$request->request->get('name');
			   $length=$request->request->get('length');
			   
			   $content=$request->request->get('content');			 
			   
			   $input = ['name' => $name, 'length' => $length, 'content' => $content];

			   $constraints = new Assert\Collection([
			   'name' => new Assert\NotBlank(['message' => 'The exam name must not be blank.']),
			   'length' => new Assert\NotBlank(['message' => 'The exam length must not be blank.']),
			   'content' => new Assert\Length(['max' => 204800,
												'maxMessage' => 'The introduction content cannot be longer than {{ limit }} characters',
												]),
			   ]);

				$data = $this->requestValidation($input, $constraints);
				  
				if ( $data['errors'] > 0){
					$introduction_content = "public/introductions/" . $this->session->get('curricularunits_id') . ".html";
					if ($this->filesystem->exists($introduction_content)) 
						$data['introduction'] = file_get_contents($introduction_content);
					else
						$data['introduction'] = '';					
					$data['curricularunits_id'] = $this->session->get('curricularunits_id');					
					return $this->render('exam/exam2.html.twig', $data);
				}  				  
			  
				$curricularunit=$this->session->get('curricularunit');
				$curricularchapters=$this->session->get('curricularchapters');
				$n_questions = $this->session->get('n_questions');
			   
			   

			   $exams_id=$this->elearn_model->add_exam($curricularunit, 
											 $this->getUser()->getId(),
											 $name,
											 $content,
											 $n_questions,
											 $length
											 );
			  
			   
			    $this->chooseExamQuestions($exams_id, $curricularchapters);
			   
			   return $this->redirectToRoute('exams', ['id' => $this->session->get('curricularunits_id')]);
			 
			} 
			 
			// method is GET
			$introduction_content = "public/introductions/" . $this->session->get('curricularunits_id') . ".html";
			if ($this->filesystem->exists($introduction_content)) 
				$data['introduction'] = file_get_contents($introduction_content);
			else
				$data['introduction'] = '';
			 $data['errors'] = 0;
			 $data['curricularunits_id'] = $this->session->get('curricularunits_id');
			 return $this->render('exam/exam2.html.twig', $data);

		}
		
		return $this->redirectToRoute('login');
	}
	
	/**
     * @Route("/exam3", name="exam3")
     */
    public function exam3(Request $request): Response	
	{
					 $data['errors'] = 0;
					 $data['curricularchapters'] = $this->elearn_model->get_curricularchapters($this->session->get('curricularunit'));
				 					
					$exams_id=$this->session->get('exams_id');
					$data['exams_id'] = $exams_id;
					
					// get questions in exam
					if ($this->elearn_model->get_exam($exams_id)) 
					{
						$questions=$this->elearn_model->get_questions_in_exam($exams_id);
						$data['questions_in_exam'] = $questions;
					}
					
					
					// get questions not in exam
					if ($this->session->get('curricularsections_id'))
					{						
						$data['curricularsections']=$this->elearn_model->get_curricularsections($this->session->get('curricularchapters_id'));
						$data['curricularsection']=$this->elearn_model->get_curricularsectionById($this->session->get('curricularsections_id'));
						$questions=$this->elearn_model->get_questionsByCurricularsections_id($this->session->get('curricularsections_id'),$exams_id);
						$data['questions_not_in_exam'] = $questions;
						$data['curricularunits_id'] = $this->session->get('curricularunit');
					}
					
					$query = $this->elearn_model->get_total_points_in_exam($exams_id);				
					$data['total_points'] = $query['total_points'];
					$data['curricularunits_id'] = $this->session->get('curricularunits_id');
					 
					 return $this->render('exam/exam3.html.twig', $data);
	}
	
	
	/**
	 * @Route("/exam/edit/{id?}", name="exam/edit")
	 */	
		
	public function exam_edit($id = FALSE): Response
	{
				
		
		if ( !empty($this->elearn_model->get_usermadeexamsByexams_id($id))  )	{	
            $this->addFlash(
                'notice', 'Exam has attempts'
            );
			
			return $this->redirectToRoute('exam/show', ['id' => $id]);

		}
		

	
		$this->session->set('exams_id', $id);
		
		$exam=$this->elearn_model->get_exam($id);
		
		$this->session->set('curricularunit', $exam['curricularunits_id']);
		
		return $this->redirectToRoute('exam3');		
		
	}
	
	/**
	 * @Route("/exam/delete/{id?}", name="exam/delete")
	 */	
		
	public function exam_delete($id = FALSE, Request $request): Response
	{
				
		
		if ( !empty($this->elearn_model->get_usermadeexamsByexams_id($id))  ) {		
            $this->addFlash(
                'notice', 'Exam has attempts' 
            );
			// return $this->redirectToRoute('exams');	
			return new RedirectResponse($request->server->get('HTTP_REFERER'));			
		}
				
		$this->elearn_model->del_exam($id);	
		
		if ( preg_match("/exam3/", $request->server->get('HTTP_REFERER')) )
			return $this->redirectToRoute('exams');
		
		return new RedirectResponse($request->server->get('HTTP_REFERER'));		
		
	}	
	
	
	
	private function chooseExamQuestions($exams_id, $curricularchapters)
	{
		
		for ($i = 0; $i < count($curricularchapters); $i++) {
			
			$questions = $this->elearn_model->get_questionsByCurricularchapters_id($curricularchapters[$i]['curricularchapters_id']);
			
			$j = 0;
			while ( ( $j < $curricularchapters[$i]['n_questions'] ) && ( $j < count($questions) ) ) {
				$random =  mt_rand ( 0 , count($questions)-1 );	
				// check if question is NOT already selected				
				if ($this->elearn_model->get_question_in_exam($exams_id, $questions[$random]['questions_id']) == FALSE) {					
					$this->elearn_model->add_question_to_exam($exams_id, $questions[$random]['questions_id']);
					$j++;
				}
			} 
			
			
		}
		
	} 


	/**
     * @Route("/add_question_to_exam/{id?}", name="add_question_to_exam")
     */
    public function add_question_to_exam($id, Request $request): Response	
	{		
		$exams_id=$this->session->get('exams_id');
		if ($this->elearn_model->get_question_in_exam($exams_id, $id) == FALSE)
			$this->elearn_model->add_question_to_exam($exams_id, $id);
		return $this->redirectToRoute('exam3');	
			
	}

	/**
     * @Route("/update_question_to_exam/{questions_id?}/{marks?}", name="update_question_to_exam")
     */
    public function update_question_to_exam($questions_id, $marks, Request $request): Response	
	{		
		$exams_id=$this->session->get('exams_id');
		$this->elearn_model->update_question_to_exam($exams_id, $questions_id, $marks);
		return $this->redirectToRoute('exam3');				
	}

	/**
     * @Route("/del_question_to_exam/{id?}", name="del_question_to_exam")
     */
    public function del_question_to_exam($id, Request $request): Response	
	{		
		$exams_id=$this->session->get('exams_id');
		$this->elearn_model->del_question_to_exam($exams_id, $id);
		return $this->redirectToRoute('exam3');
	}	


	
	/**
     * @Route("/curricularquestions/{id?}", name="curricularquestions")
     */
    public function curricularquestions($id = FALSE) : Response
    {
      
		$this->session->set('curricularsections_id', $id);
		$json = json_encode($this->elearn_model->get_questionsByCurricularsections_id($id));
		return new Response($json);
    }

	/**
     * @Route("/total_questionsByCurricularunits/{id?}", name="total_questionsByCurricularunits")
     */
    public function total_questionsByCurricularunits($id = FALSE) : Response
    {
		$json = json_encode($this->elearn_model->get_total_questionsByCurricularunits_id($id));
		return new Response($json);
    }

	/**
     * @Route("/total_questionsByCurricularchapters/{id?}", name="total_questionsByCurricularchapters")
     */
    public function total_questionsByCurricularchapters($id = FALSE) : Response
    {
		$json = json_encode($this->elearn_model->get_total_questionsByCurricularchapters($id));
		return new Response($json);
    }
	
	/**
     * @Route("/exam/visibility/{id}", name="exam_toggle_visibility")
     */
    public function toggleExamVisibility($id)
    {
		$result = $this->elearn_model->exam_update_visibility($id);
		$data['visibility'] = $result['visibility'];
        return new Response(json_encode($data));
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
