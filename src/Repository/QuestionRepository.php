<?php

namespace App\Repository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\DBAL\Connection;

class QuestionRepository extends AbstractController
{
	private $connection;	
	public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

	public function get_questions_paginated($limit, $page)
	{			
		
	
		$offset = $page * $limit; 	
		
		$sql = "select q.questions_id as id, q.questiontypes_id as type, q.content as content, q.title as title, q.users_id, u.name as uname, c.description as cdescription, s.description as sdescription, cty.description as ctydescription, src.description as srcdescription, src.href as srchref  from questions as q 
		        inner join (curricularunits as u, curricularchapters as c, curricularsections as s, complexities as cty, sources as src) 
				on (q.curricularunits_id=u.curricularunits_id and  q.curricularchapters_id=c.curricularchapters_id and q.curricularsections_id=s.curricularsections_id and q.complexities_id=cty.complexities_id and q.sources_id=src.sources_id) 
				order by q.questions_id desc LIMIT $limit OFFSET $offset";
                  
		return $this->connection->fetchAllAssociative($sql);	
	}
	
	public function get_questionsByCurricularunitsID_paginated($id, $limit, $page, $cc = FALSE, $cs = FALSE, $cty = FALSE )
	{			

		$offset = $page * $limit; 
	
		$sql = "select q.questions_id as id, q.questiontypes_id as type, q.content as content, q.title as title, q.users_id, u.name as uname, c.description as cdescription, s.description as sdescription, cty.description as ctydescription, src.description as srcdescription, src.href as srchref  from questions as q 
		        inner join (curricularunits as u, curricularchapters as c, curricularsections as s, complexities as cty, sources as src) 
				on (q.curricularunits_id=u.curricularunits_id and  q.curricularchapters_id=c.curricularchapters_id and q.curricularsections_id=s.curricularsections_id and q.complexities_id=cty.complexities_id and q.sources_id=src.sources_id) 
				where q.curricularunits_id=:id
				order by q.questions_id desc LIMIT $limit OFFSET $offset";
		
		if ($cty)	
			$sql = "select q.questions_id as id, q.questiontypes_id as type, q.content as content, q.title as title, q.users_id, u.name as uname, c.description as cdescription, s.description as sdescription, cty.description as ctydescription, src.description as srcdescription, src.href as srchref  from questions as q 
				inner join (curricularunits as u, curricularchapters as c, curricularsections as s, complexities as cty, sources as src) 
				on (q.curricularunits_id=u.curricularunits_id and  q.curricularchapters_id=c.curricularchapters_id and q.curricularsections_id=s.curricularsections_id and q.complexities_id=cty.complexities_id and q.sources_id=src.sources_id) 
				where (q.curricularunits_id=:id and cty.complexities_id=$cty)
				order by q.questions_id desc LIMIT $limit OFFSET $offset";			
		
		if ($cc)	
			$sql = "select q.questions_id as id, q.questiontypes_id as type, q.content as content, q.title as title, q.users_id, u.name as uname, c.description as cdescription, s.description as sdescription, cty.description as ctydescription, src.description as srcdescription, src.href as srchref  from questions as q 
				inner join (curricularunits as u, curricularchapters as c, curricularsections as s, complexities as cty, sources as src) 
				on (q.curricularunits_id=u.curricularunits_id and  q.curricularchapters_id=c.curricularchapters_id and q.curricularsections_id=s.curricularsections_id and q.complexities_id=cty.complexities_id and q.sources_id=src.sources_id) 
				where (q.curricularunits_id=:id and c.curricularchapters_id=$cc)
				order by q.questions_id desc LIMIT $limit OFFSET $offset";	
				
		if ($cc && $cty)	
			$sql = "select q.questions_id as id, q.questiontypes_id as type, q.content as content, q.title as title, q.users_id, u.name as uname, c.description as cdescription, s.description as sdescription, cty.description as ctydescription, src.description as srcdescription, src.href as srchref  from questions as q 
				inner join (curricularunits as u, curricularchapters as c, curricularsections as s, complexities as cty, sources as src) 
				on (q.curricularunits_id=u.curricularunits_id and  q.curricularchapters_id=c.curricularchapters_id and q.curricularsections_id=s.curricularsections_id and q.complexities_id=cty.complexities_id and q.sources_id=src.sources_id) 
				where (q.curricularunits_id=:id and c.curricularchapters_id=$cc and cty.complexities_id=$cty)
				order by q.questions_id desc LIMIT $limit OFFSET $offset";			

		if ($cc && $cs)	
			$sql = "select q.questions_id as id, q.questiontypes_id as type, q.content as content, q.title as title, q.users_id, u.name as uname, c.description as cdescription, s.description as sdescription, cty.description as ctydescription, src.description as srcdescription, src.href as srchref  from questions as q 
				inner join (curricularunits as u, curricularchapters as c, curricularsections as s, complexities as cty, sources as src) 
				on (q.curricularunits_id=u.curricularunits_id and  q.curricularchapters_id=c.curricularchapters_id and q.curricularsections_id=s.curricularsections_id and q.complexities_id=cty.complexities_id and q.sources_id=src.sources_id) 
				where (q.curricularunits_id=:id and c.curricularchapters_id=$cc and s.curricularsections_id=$cs)
				order by q.questions_id desc LIMIT $limit OFFSET $offset";	

		if ($cc && $cs && $cty)	
			$sql = "select q.questions_id as id, q.questiontypes_id as type, q.content as content, q.title as title, q.users_id, u.name as uname, c.description as cdescription, s.description as sdescription, cty.description as ctydescription, src.description as srcdescription, src.href as srchref  from questions as q 
				inner join (curricularunits as u, curricularchapters as c, curricularsections as s, complexities as cty, sources as src) 
				on (q.curricularunits_id=u.curricularunits_id and  q.curricularchapters_id=c.curricularchapters_id and q.curricularsections_id=s.curricularsections_id and q.complexities_id=cty.complexities_id and q.sources_id=src.sources_id) 
				where (q.curricularunits_id=:id and c.curricularchapters_id=$cc and s.curricularsections_id=$cs and cty.complexities_id=$cty)
				order by q.questions_id desc LIMIT $limit OFFSET $offset";	

		$stmt = $this->connection->prepare($sql); 
	    $stmt->bindValue('id', $id);       		
		$rst=$stmt->executeQuery();           
		return $rst->fetchAllAssociative();
	}

	
				
}