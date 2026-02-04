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
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use App\Controller\Elearn_modelController;
use App\Repository\QuestionRepository;
use App\Controller\PaginationController;

class QuestionController extends AbstractController
{
    
	private $session;
	private $elearn_model;
	private $validator;
    private $filesystem;
	private $question_model;
	
	public function __construct(SessionInterface $session, Elearn_modelController $elearn_model, QuestionRepository $question_model, ValidatorInterface $validator, Filesystem $filesystem)
    {
		$this->session = $session;
		$this->elearn_model = $elearn_model;
        $this->validator = $validator;
		$this->filesystem = $filesystem;
		$this->question_model = $question_model;		
    }
	
			
	
	/**
     * @Route("/items/{id?}", name="questions")
     */
    public function questions($id = FALSE, Request $request, PaginationController $paginate): Response
    {
		if ($request->query->get('page'))
			$page = $request->query->get('page');
		else
			$page = 0;
		
		$limit = 5; 
		if ($id && !$page)
			$data['questions']=$this->elearn_model->get_questionsByCurricularunitsID($id);		
		if ($id && $page)
			$data['questions']=$this->question_model->get_questionsByCurricularunitsID_paginated($id,$limit,$page-1);			
		
		if (!$id && !$page)	
			$data['questions'] = $this->elearn_model->get_questions();
		
		if (!$id && $page)	
			$data['questions'] = $this->question_model->get_questions_paginated($limit,$page-1);	

		$data['users_id'] = $this->getUser()->getId();

		if ($id) 
			$totalRecords=count($this->elearn_model->get_questionsByCurricularunitsID($id));
		else
			$totalRecords=count($this->elearn_model->get_questions());

		$data['pagination']=$paginate->pagination($limit,$page,$totalRecords);

		if ($id){			
			$data['curricularchapters'] = $this->elearn_model->get_curricularchapters($id);			
			$data['complexities'] = $this->elearn_model->get_complexities();
		}

		$data['id'] =  $id ;

		if ( $this->elearn_model->get_user_reviewer($id, $this->getUser()->getId()) )	
			$data['reviewer'] =  true;
		else 
			$data['reviewer'] =  false;

		$this->session->set('curricularunits_id', $id);
		return $this->render('question/questions.html.twig', $data);
	}

	/**
     * @Route("/items/{id?}/filter", name="filter")
     */
    public function filter($id, Request $request, PaginationController $paginate): Response
    {

		if ($request->query->get('page')){
			$page = $request->query->get('page');
		}
		else
			$page = 1;

		$data['filter'] = '';
		
		if ($request->query->get('chapter')){
			$chapter = $request->query->get('chapter');
			$data['filter'] = $data['filter'] . "chapter=$chapter" . '&';
		}
		else
			$chapter = FALSE;	
		
		if ($request->query->get('section')){
			$section = $request->query->get('section');
			$data['filter'] = $data['filter'] . "section=$section" . '&';
		}
		else
			$section = FALSE;					

		if ($request->query->get('complexity')){
			$complexity = $request->query->get('complexity');
			$data['filter'] = $data['filter'] . "complexity=$complexity" . '&';		
		}
		else
			$complexity = FALSE;			
			
		$limit = 5; 
	

		$data['questions']=$this->question_model->get_questionsByCurricularunitsID_paginated($id,$limit,$page-1,$chapter,$section,$complexity);			
		$totalRecords=count($this->elearn_model->get_questionsByCurricularunitsID($id,$chapter,$section,$complexity));
		

	
		$data['users_id'] = $this->getUser()->getId();




		$data['pagination']=$paginate->pagination($limit,$page,$totalRecords);

		$data['id'] =  $id ;
		$data['curricularchapters'] = $this->elearn_model->get_curricularchapters($id);			
		$data['complexities'] = $this->elearn_model->get_complexities();	
		
		
		if ( $this->elearn_model->get_user_reviewer($id, $this->getUser()->getId()) )	
		 	$data['reviewer'] =  true;
		else 
			$data['reviewer'] =  false;


		$this->session->set('curricularunits_id', $id);
		return $this->render('question/questions_filtered.html.twig', $data);
	}	
	
	/**
     * @Route("/item/view/{id?}", name="view_question")
     */
    public function view_question($id, Request $request): Response
    {
		$data['question'] = $this->elearn_model->get_questionById($id);
		return $this->render('question/view_question.html.twig', $data);
	}
	
	/**
     * @Route("/item/edit/{id}", name="edit_question")
     */
    public function edit_question($id, Request $request): Response
    {
	
		if ( $this->isGranted('ROLE_ADMIN') || $this->elearn_model->get_questionById($id)['users_id'] == $this->getUser()->getId() || $this->elearn_model->get_user_reviewer($this->elearn_model->get_questionById($id)['curricularunits_id'], $this->getUser()->getId()) )	
		{
			
		   if ($request->isMethod('POST') && $request->attributes->get('_route') === 'edit_question') {
          
			   $token = $request->request->get("csrf_token");

			   if (!$this->isCsrfTokenValid('question', $token)) {
				   return new Response("Operation not allowed", Response::HTTP_OK,
					   ['content-type' => 'text/plain']);
			   }

				$type=$request->request->get('type');
				if ($type != 5)
					return $this->redirectToRoute('questions');
				
				$curricularunit=$request->request->get('curricularunit');
				$chapter=$request->request->get('chapter');
				$section=$request->request->get('section');
				$complexity=$request->request->get('complexity');
				$content=$request->request->get('content');
				$correct_answer=$request->request->get('correct_answer');				
				$title=$request->request->get('title');				
				
				$input = ['content' => $content, 'correct_answer' => $correct_answer, 'title' => $title];

				$constraints = new Assert\Collection([
				'content' => new Assert\Length(['max' => 1024000,
												'maxMessage' => 'The question content cannot be longer than {{ limit }} bytes',
												]),
				'correct_answer' => new Assert\Length(['max' => 1024000,
												'maxMessage' => 'The question answer cannot be longer than {{ limit }} bytes',
												]),												
				'title' => new Assert\Length(['max' => 80,
												'maxMessage' => 'The question title cannot be longer than {{ limit }} characters',
												]),												
			   ]);

				$data = $this->requestValidation($input, $constraints);
				  
				if ( $data['errors'] > 0) {
						$question = $this->elearn_model->get_questionById($id);
						$question['content'] = $content ;
						$question['correct_answer'] = $correct_answer ;	
						$question['title'] = $title ;											
						$data['question'] = $question;
						$data['curricularunits'] = $this->elearn_model->get_curricularunits();
						$data['complexities'] = $this->elearn_model->get_complexities();
						$data['referer'] = 	$this->session->get('referer')	;					
						return $this->render('question/edit_question.html.twig', $data);
				}  				  
			  
 
			   $this->elearn_model->update_question($id, 
												 $curricularunit,
												 $chapter,
												 $section,
												 $complexity,
												 $content,
												 $title,
												 $correct_answer
												 );			  
			   
				return new RedirectResponse($this->session->get('referer'));

		   }
		    $data['errors'] = 0;
			$data['question'] = $this->elearn_model->get_questionById($id);
			$data['curricularunits'] = $this->elearn_model->get_curricularunits();
			$data['complexities'] = $this->elearn_model->get_complexities();
			$data['referer'] = $request->headers->get('referer');
			$this->session->set('referer', $data['referer'] )	;				
			return $this->render('question/edit_question.html.twig', $data);
		
		}
		
		return $this->redirectToRoute('login');	
		
	}

	/**
     * @Route("/item2/edit/{id}", name="edit_question2")
     */
    public function edit_question2($id, Request $request): Response
    {
		
		if ( $this->isGranted('ROLE_ADMIN') || $this->elearn_model->get_questionById($id)['users_id'] == $this->getUser()->getId() || $this->elearn_model->get_user_reviewer($this->elearn_model->get_questionById($id)['curricularunits_id'], $this->getUser()->getId()))
		{
			
		   if ($request->isMethod('POST') && $request->attributes->get('_route') === 'edit_question2') {
          
			   $token = $request->request->get("csrf_token");

			   if (!$this->isCsrfTokenValid('question', $token)) {
				   return new Response("Operation not allowed", Response::HTTP_OK,
					   ['content-type' => 'text/plain']);
			   }

				$type=$request->request->get('type');
				if ($type != 4)
					return $this->redirectToRoute('questions');
				
				$curricularunit=$request->request->get('curricularunit');
				$chapter=$request->request->get('chapter');
				$section=$request->request->get('section');
				$complexity=$request->request->get('complexity');
				$content=$request->request->get('content');
				$correct_answer=$request->request->get('correct_answer');				
				$title=$request->request->get('title');				
				
				$input = ['content' => $content, 'correct_answer' => $correct_answer, 'title' => $title];

				$constraints = new Assert\Collection([
				'content' => new Assert\Length(['max' => 1024000,
												'maxMessage' => 'The question content cannot be longer than {{ limit }} characters',
												]),
				'correct_answer' => new Assert\Length(['max' => 1024000,
												'maxMessage' => 'The question answer cannot be longer than {{ limit }} characters',
												]),												
				'title' => new Assert\Length(['max' => 80,
												'maxMessage' => 'The question title cannot be longer than {{ limit }} characters',
												]),													
			   ]);

				$data = $this->requestValidation($input, $constraints);
				  
				if ( $data['errors'] > 0) {
						$question = $this->elearn_model->get_questionById($id);
						$question['content'] = $content ;
						$question['correct_answer'] = $correct_answer ;	
						$question['title'] = $title ;												
						$data['question'] = $question;
						$data['curricularunits'] = $this->elearn_model->get_curricularunits();
						$data['complexities'] = $this->elearn_model->get_complexities();
						$data['referer'] = 	$this->session->get('referer')	;						
						return $this->render('question/edit_question2.html.twig', $data);
				}  				  
			  
 
			   $this->elearn_model->update_question($id, 
												 $curricularunit,
												 $chapter,
												 $section,
												 $complexity,
												 $content,
												 $title,
												 $correct_answer												 
												 );			  
			   

				return new RedirectResponse($this->session->get('referer'));

		   }
		    $data['errors'] = 0;
			$data['question'] = $this->elearn_model->get_questionById($id);
			$data['curricularunits'] = $this->elearn_model->get_curricularunits();
			$data['complexities'] = $this->elearn_model->get_complexities();
			$data['referer'] = $request->headers->get('referer');
			$this->session->set('referer', $data['referer'] )	;	
			return $this->render('question/edit_question2.html.twig', $data);
		
		}
		
		return $this->redirectToRoute('login');	
		
	}


   /**
	 * @Route("/item/delete/{id}", name="del_question")
	 */   
  
   public function del_question($id, Request $request)
   {
	   if ( $this->isGranted('ROLE_ADMIN') || $this->elearn_model->get_questionById($id)['users_id'] == $this->getUser()->getId() || $this->elearn_model->get_user_reviewer($this->elearn_model->get_questionById($id)['curricularunits_id'], $this->getUser()->getId()))
	   {
		   	if ( !empty($this->elearn_model->exam_has_questionsByquestions_id($id))  )		
            $this->addFlash(
                'notice', 'Question is in use in exams'
			);
		   else{
		   
			   $question = $this->elearn_model->del_question($id);
					   
				if( $question['type'] == 6 )
				{
				   $filename = $question['content'];		  
				   $result = $this->filesystem->remove("uploads/questions/$filename");
				  
				   if ($result === false) {
					   throw new \Exception(sprintf('Error deleting "%s"', $filename));
				   }
				}
		   }
	   }
	   else
		$this->addFlash(
			'notice', 'Permission denied: question does not belong to you'
		);
	   $referer=$request->headers->get('referer');
	   return new RedirectResponse($referer);
	  
   }	
	
	
	
	/**
     * @Route("/item/{type}", name="question")
     */
    public function question($type, Request $request): Response
    {
        
		if ($this->getUser())
		{
			
		   if ($request->isMethod('POST') && $request->attributes->get('_route') === 'question') {
          
			   $token = $request->request->get("csrf_token");

			   if (!$this->isCsrfTokenValid('question', $token)) {
				   return new Response("Operation not allowed", Response::HTTP_OK,
					   ['content-type' => 'text/plain']);
			   }

				$curricularunit=$request->request->get('curricularunit');
				$chapter=$request->request->get('chapter');
				$section=$request->request->get('section');
				$complexity=$request->request->get('complexity');
				if ($request->request->get('resolution_time'))
					$resolution_time=$request->request->get('resolution_time');
				else
					$resolution_time=5;			
				$cognitivegoals=$request->request->get('cognitivegoals');	
				$curricularskills=$request->request->get('curricularskills');					
				
				$this->session->set('curricularunit', $curricularunit);
				$this->session->set('chapter', $chapter);
				$this->session->set('section', $section);
				$this->session->set('complexity', $complexity);
				$this->session->set('resolution_time', $resolution_time);				
				$this->session->set('cognitivegoals', $cognitivegoals);
				$this->session->set('curricularskills', $curricularskills);				

				switch ($type) {
				case 6:
					 return $this->redirectToRoute('question2');
					 break;
				case 5:
					 return $this->redirectToRoute('question3');
					 break;
				case 4:
					 return $this->redirectToRoute('question4');
					 break;	 
				} 
		   
		   }
		   
		   		$data['curricularunits'] = $this->elearn_model->get_curricularunits();
				$data['complexities'] = $this->elearn_model->get_complexities();
				$data['curricularskills'] = $this->elearn_model->get_curricularskills();
				$data['cognitivegoals'] = $this->elearn_model->get_cognitivegoals();
				$data['referer'] = 	$request->headers->get('referer');
				$this->session->set('referer', $data['referer']);
				$data['curricularunits_id'] = $this->session->get('curricularunits_id');					
				return $this->render('question/question.html.twig', $data);
		
		}
		
		return $this->redirectToRoute('login');
    
	}
	
	
	/**
     * @Route("/item2", name="question2")
     */
    public function question2(Request $request): Response
    {
         
		if ($this->getUser())
		{ 
		 
		    if ($request->isMethod('POST') && $request->attributes->get('_route') === 'question2') {
		 
				$token = $request->get("csrf_token");

			   if (!$this->isCsrfTokenValid('question', $token))
			   {
				   return new Response("Operation not allowed",  Response::HTTP_BAD_REQUEST,
					   ['content-type' => 'text/plain']);
			   }
		  
			   $file = $request->files->get('file');

			   if (empty($file))
			   {
				   return new Response("No file specified",
					   Response::HTTP_UNPROCESSABLE_ENTITY, ['content-type' => 'text/plain']);
			   }
			  
			   
			   
			   $filename = $file->getClientOriginalName();
			   
			   $question = $this->elearn_model->get_question($filename);
			   if ($question == false)
				   $value = '';
			   else
				   $value = $question['content'];
			   
			   $input = ['file' => $file, 'filename' => $filename];

			   $constraints = new Assert\Collection([
			   'file' => new Assert\File(['maxSize' => '1024k',
										  'mimeTypes' => [
											   'image/gif',
											   'image/jpeg',
											   'image/png',
										   ],
										   'mimeTypesMessage' => 'Please upload a valid image (gif, jpg, png)',
										   'maxSizeMessage' => 'The image is too large. Allowed maximum size is {{ limit }} {{ suffix }}',
									   ]),
				'filename' => new Assert\NotEqualTo(['value' => $value, 'message' => "This image is already in the database"]),
			   ]);

				$data = $this->requestValidation($input, $constraints);
				  
				if ( $data['errors'] > 0)
					   return $this->render('question/question2.html.twig', $data);
				  				  

			   try {
				   $file->move("uploads/questions", $filename);
			   } catch (FileException $e){
				   throw new FileException('Failed to upload file ' . $e->getMessage());
			   }
			  
 
			   $questions_id = $this->elearn_model->add_question(
												$this->session->get('curricularunit'), 
												$this->session->get('chapter'),
												$this->session->get('section'),
												$this->session->get('complexity'),
												'',
												'',
												$filename,
												6,
												$this->getUser()->getId(),
												$this->session->get('resolution_time')
												);
			  
			   $this->question_has_cognitivegoals($questions_id);
			   $this->question_has_curricularskills($questions_id);
			   
			   return $this->redirectToRoute('questions');
			 
			} 
			 
			 $data['errors'] = 0;
			 return $this->render('question/question2.html.twig', $data); 

		}
		
		return $this->redirectToRoute('login');
	}
	
	
	/**
     * @Route("/item3", name="question3")
     */
    public function question3(Request $request): Response	
	{
         
		if ($this->getUser())
		{ 
		 
		    if ($request->isMethod('POST') && $request->attributes->get('_route') === 'question3') {
		 
				$token = $request->get("csrf_token");

			   if (!$this->isCsrfTokenValid('question', $token))
			   {
				   return new Response("Operation not allowed",  Response::HTTP_BAD_REQUEST,
					   ['content-type' => 'text/plain']);
			   }
		  
			   $content=$request->request->get('content');
			   $correct_answer=$request->request->get('correct_answer');			   
			   
			   $title=$request->request->get('title');	
			   $source=$request->request->get('source');
			   if ($source == NULL )
			   		$source	= 1 ;	   		   
			   
			   $input = ['content' => $content, 'correct_answer' => $correct_answer, 'title' => $title];

			   $constraints = new Assert\Collection([
				'content' => [new Assert\Length(['max' => 1024000,
												'maxMessage' => 'The question content cannot be longer than {{ limit }} bytes',
												]),
							new Assert\NotBlank(['message' => 'The question content must not be blank']),
											],												
				'correct_answer' => new Assert\Length(['max' => 1024000,
												'maxMessage' => 'The question answer cannot be longer than {{ limit }} bytes',
												]),													
				'title' => [new Assert\Length(['max' => 80,
												'maxMessage' => 'The question title cannot be longer than {{ limit }} characters',
												]),
							new Assert\NotBlank(['message' => 'The question title must not be blank']),
							]											
			   ]);

				$data = $this->requestValidation($input, $constraints);
				  
				if ( $data['errors'] > 0) {
					$data['title'] = $title;	
					$data['content'] = $content;
					$data['correct_answer'] = $correct_answer;
					$data['referer'] = $this->session->get('referer');
					$data['sources'] = $this->elearn_model->get_sources();					
					return $this->render('question/question3.html.twig', $data);
				}

				  				  
			  
 
			   $questions_id = $this->elearn_model->add_question(
												$this->session->get('curricularunit'), 
												 $this->session->get('chapter'),
												 $this->session->get('section'),
												 $this->session->get('complexity'),
												 $content,
												 $correct_answer,
												 $title,
												 5,
												 $this->getUser()->getId(),
												 $this->session->get('resolution_time'),
												 $source												 
												 );
			  
			   $this->question_has_cognitivegoals($questions_id);
			   $this->question_has_curricularskills($questions_id);
			   
			   return $this->redirectToRoute('questions', ['id' => $this->session->get('curricularunit'), 'page'=> '1']);
			 
			} 
			 
			 $data['errors'] = 0;
			 $data['title'] = '';					
			 $data['content'] = '';
			 $data['correct_answer'] = '';
			 $data['referer'] = $this->session->get('referer');
			 $data['sources'] = $this->elearn_model->get_sources();
			 return $this->render('question/question3.html.twig', $data);

		}
		
		return $this->redirectToRoute('login');
	}
	
	
	/**
     * @Route("/item4", name="question4")
     */
    public function question4(Request $request): Response	
	{
         
		if ($this->getUser())
		{ 
		 
		    if ($request->isMethod('POST') && $request->attributes->get('_route') === 'question4') {
		 
				$token = $request->get("csrf_token");

			   if (!$this->isCsrfTokenValid('question', $token))
			   {
				   return new Response("Operation not allowed",  Response::HTTP_BAD_REQUEST,
					   ['content-type' => 'text/plain']);
			   }
		  
			   $content=$request->request->get('content');
			   $correct_answer=$request->request->get('correct_answer');			   
			   $title=$request->request->get('title');			   
			   
			   $source=$request->request->get('source');
			   if ($source == NULL )
			   		$source	= 1 ;

			   $input = ['content' => $content, 'correct_answer' => $correct_answer, 'title' => $title];

			   $constraints = new Assert\Collection([
				'content' => [new Assert\Length(['max' => 1024000,
												'maxMessage' => 'The question content cannot be longer than {{ limit }} bytes',
												]),					
							new Assert\NotBlank(['message' => 'The question content must not be blank']),
							],												
				'correct_answer' => new Assert\Length(['max' => 1024000,
												'maxMessage' => 'The question answer cannot be longer than {{ limit }} bytes',
												]),												
				'title' => [new Assert\Length(['max' => 80,
												'maxMessage' => 'The question title cannot be longer than {{ limit }} characters',
												]),					
							new Assert\NotBlank(['message' => 'The question title must not be blank']),
							]												
			   ]);

				$data = $this->requestValidation($input, $constraints);
				  
				if ( $data['errors'] > 0) {
					$data['title'] = $title;					
					$data['content'] = $content;
					$data['correct_answer'] = $correct_answer;
					$data['curricularunits_id'] = $this->session->get('curricularunits_id');
					$data['referer'] = $this->session->get('referer');
					$data['sources'] = $this->elearn_model->get_sources();					
					return $this->render('question/question4.html.twig', $data);
				}

				  				  
			  
 
			   $questions_id = $this->elearn_model->add_question(
												 $this->session->get('curricularunit'), 
												 $this->session->get('chapter'),
												 $this->session->get('section'),
												 $this->session->get('complexity'),
												 $content,
												 $correct_answer,
												 $title,
												 4,
												 $this->getUser()->getId(),
												 $this->session->get('resolution_time'),
												 $source												 												 
												 );
												 
			   $this->question_has_cognitivegoals($questions_id);
			   $this->question_has_curricularskills($questions_id);			  
			   
			   return $this->redirectToRoute('questions', ['id' => $this->session->get('curricularunit'), 'page'=> '1']);
			 
			} 
			 
			 $data['errors'] = 0;
			 $data['title'] = '';					
			 $data['content'] = '';
			 $data['correct_answer'] = '';
			 $data['curricularunits_id'] = $this->session->get('curricularunits_id');
			 $data['referer'] = $this->session->get('referer');
			 $data['sources'] = $this->elearn_model->get_sources();			 
			 return $this->render('question/question4.html.twig', $data);

		}
		
		return $this->redirectToRoute('login');
	}	
	
	/**
	 * @Route("/addsource", name="add_source")
	 */	
		
	 public function add_source(Request $request): Response	
	 {
		 if ($request->isMethod('POST') && $request->attributes->get('_route') === 'add_source') {
		 
			 
			$description=$request->request->get('description');
			$href=$request->request->get('href');
			
			//check if source already exists in database
			$sources = $this->elearn_model->get_sources();

			
			foreach ( $sources as $source ) {
				if ( $description == '' || $description == $source['description']) {
					return new RedirectResponse($request->headers->get('referer'));
				}
			}

			$this->elearn_model->add_source($description, $href, $this->getUser()->getId());
			return new RedirectResponse($request->headers->get('referer'));

		 
		 
		 }
		 
	 }

	
	/**
     * @Route("/curricularchapters/{id?}", name="curricularchapters")
     */
    public function curricularchapters($id = FALSE) : Response
    {
      
		$json = json_encode($this->elearn_model->get_curricularchapters($id));
		return new Response($json);
    } 
	
	/**
     * @Route("/curricularsections/{id?}", name="curricularsections")
     */
    public function curricularsections($id = FALSE) : Response
    {
       	$this->session->set('curricularchapters_id', $id);
		$json = json_encode($this->elearn_model->get_curricularsections($id));
		return new Response($json);
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
   
   private function question_has_cognitivegoals($questions_id)
   {
	   $cognitivegoals=$this->session->get('cognitivegoals');
	   if(!empty($cognitivegoals))
	   for ($i = 0; $i < count($cognitivegoals); $i++) 
			$this->elearn_model->add_to_question_has_cognitivegoals($questions_id, $cognitivegoals[$i]);	   
   }
   
    private function question_has_curricularskills($questions_id)
   {	   
	   $curricularskills=$this->session->get('curricularskills');
	   if(!empty($curricularskills))
	   for ($i = 0; $i < count($curricularskills); $i++) 
			$this->elearn_model->add_to_question_has_curricularskills($questions_id, $curricularskills[$i]);				   	   
   }
	
	
	
}
