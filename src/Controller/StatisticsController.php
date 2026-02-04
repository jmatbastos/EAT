<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\StatisticsRepository;

class StatisticsController extends AbstractController
{
    
	private $session;
	private $statistics_model;
	
	public function __construct(SessionInterface $session, StatisticsRepository $statistics_model)
    {
		$this->session = $session;
		$this->statistics_model = $statistics_model;
    }
	
	/**
	 * @Route("/statistics", name="statistics")
	 */	
		
	public function statistics(): Response	
	{		
		$curricularunits=$this->statistics_model->get_questions_total_by_curricularunit();
		
		for ($i = 0; $i <count($curricularunits); $i++) {
			// join chapters to curricularunits 
			$curricularchapters=$this->statistics_model->get_questions_total_by_curricularchapter($curricularunits[$i]['curricularunits_id']);

			for ($j = 0; $j <count($curricularchapters); $j++){
				$curricularunits[$i]['chapters'][$j]= $curricularchapters[$j];
				$curricularsections=$this->statistics_model->get_questions_total_by_curricularsection($curricularchapters[$j]['curricularchapters_id']);
				for ($k = 0; $k <count($curricularsections); $k++)
					$curricularunits[$i]['sections'][]= $curricularsections[$k];				
			}
		}
		
		$data['curricularunits1']=$curricularunits;
		
		$curricularunits=$this->statistics_model->get_exams_total_by_curricularunit();
		
		for ($i = 0; $i <count($curricularunits); $i++) {
			// join exams to curricularunits 
			$exams=$this->statistics_model->get_examsByCurricularunitID($curricularunits[$i]['curricularunits_id']);

			for ($j = 0; $j <count($exams); $j++)
				$curricularunits[$i]['exams'][$j]= $exams[$j];
				
		$data['curricularunits2']=$curricularunits;	
		}
		
		
		return $this->render('statistics/statistics_list.html.twig', $data);
	}
	


	
	
	
}
