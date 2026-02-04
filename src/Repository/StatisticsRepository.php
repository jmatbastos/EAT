<?php

namespace App\Repository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\DBAL\Connection;

class StatisticsRepository extends AbstractController
{
	private $connection;	
	public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


	public function get_questions_total_by_curricularunit()
	{
	
		$sql='select cu.curricularunits_id, cu.name as curricularunit_name,count(q.questions_id) as questions_total from questions as q
				inner join curricularunits as cu
				on q.curricularunits_id=cu.curricularunits_id				
				group by q.curricularunits_id';

		return $this->connection->fetchAllAssociative($sql);
	}				

	public function get_questions_total_by_curricularchapter($curricularunits_id)
	{
		$sql='select chpt.curricularchapters_id, chpt.description as chapter_description,count(q.questions_id) as questions_total from questions as q
				inner join curricularchapters as chpt
				on q.curricularchapters_id=chpt.curricularchapters_id
				where q.curricularunits_id=:id
				group by q.curricularchapters_id';
	   
	   $stmt = $this->connection->prepare($sql);
	   $stmt->bindValue('id', $curricularunits_id);
       $rst=$stmt->executeQuery();
	   return $rst->fetchAllAssociative();
	}

		
	public function get_questions_total_by_curricularsection($curricularchapters_id)
	{		
		$sql='select s.description as section_description,count(q.questions_id) as questions_total from questions as q
				inner join curricularsections as s
				on q.curricularsections_id=s.curricularsections_id
				where q.curricularchapters_id=:id				
				group by q.curricularsections_id';	

	   $stmt = $this->connection->prepare($sql);
	   $stmt->bindValue('id', $curricularchapters_id);
       $rst=$stmt->executeQuery();
	   return $rst->fetchAllAssociative();
	}

	public function get_exams_total_by_curricularunit()
	{
	
		$sql='select ex.curricularunits_id, cu.name,count(ex.exams_id) as exams_total from exams as ex
				inner join curricularunits as cu
				on ex.curricularunits_id=cu.curricularunits_id				
				group by ex.curricularunits_id';
		return $this->connection->fetchAllAssociative($sql);
	}


	public function get_examsByCurricularunitID($id)
	{			
	   	$sql = "SELECT e.exams_id, e.exams_name, u.attempts_total FROM `exams` as e left join usermadeexams_total as u
				on e.exams_id=u.exams_id
				where e.curricularunits_id=:id
				order by e.creation_date desc";
	   $stmt = $this->connection->prepare($sql); 
	   $stmt->bindValue('id', $id);		   
	   $rst=$stmt->executeQuery();           
	   return $rst->fetchAllAssociative();
	}
	
				
}




