<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\DBAL\Connection;

class Elearn_modelController extends AbstractController
{
	private $connection;	
	public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
	
	public function get_languages()
	{			
		$sql = 'select * from languages';
		return $this->connection->fetchAllAssociative($sql);
	}
	
	public function get_sources()
	{			
		$sql = 'select * from sources';
		return $this->connection->fetchAllAssociative($sql);
	}

	public function add_source($description, $href, $user_id)
	{
		$sql = "INSERT INTO sources (description, href, created_at, updated_at, users_id) 
		VALUES (:description,  :href, NOW(), NOW(), '$user_id')";
		$stmt = $this->connection->prepare($sql);
		$stmt->bindValue('description', $description);	
		$stmt->bindValue('href', $href);				 		 
		$rst=$stmt->executeQuery();
	}
	
	
	public function get_curricularunits()
	{			
		$sql = 'select c.curricularunits_id, c.name, c.created_at, c.users_id, u.name as uname from curricularunits as c
				inner join users as u
				on c.users_id=u.users_id
				order by c.created_at desc';
		return $this->connection->fetchAllAssociative($sql);
	}
	
	public function get_curricularunitByID($id)
	{			
		$sql = 'select c.curricularunits_id, c.name, c.year, c.created_at, c.updated_at, c.users_id, u.name as uname, l.name as lname from curricularunits as c
				inner join (users as u, languages as l)
				on (c.users_id=u.users_id and c.languages_id=l.languages_id)
				where curricularunits_id=:id';
		
	   $stmt = $this->connection->prepare($sql);
	   $stmt->bindValue('id', $id);
       $rst=$stmt->executeQuery();
	   return $rst->fetchAssociative();
	}
	
	public function update_curricularunit($id, $name, $year)
	{			
		$sql = "update curricularunits 
				set name=:name, year=:year, updated_at=NOW() 
				where curricularunits_id='$id'";
		$stmt = $this->connection->prepare($sql);
	    $stmt->bindValue('name', $name);
	    $stmt->bindValue('year', $year);			
		$rst=$stmt->executeQuery();           
		return true;
	}	
	
	
	public function get_curriculargoalsByID($id)
	{
		$sql = "SELECT * FROM `curriculargoals` 
				where curricularsections_id=:id";
	   $stmt = $this->connection->prepare($sql);
	   $stmt->bindValue('id', $id);
       $rst=$stmt->executeQuery();
	   return $rst->fetchAllAssociative();		
	}
	
	public function get_cognitivegoals()
	{
	   $sql = "SELECT * FROM `cognitivegoals`";
	   $stmt = $this->connection->prepare($sql);
       $rst=$stmt->executeQuery();
	   return $rst->fetchAllAssociative();		
	}

	public function get_curricularskills()
	{
	   $sql = "SELECT * FROM `curricularskills`";
	   $stmt = $this->connection->prepare($sql);
       $rst=$stmt->executeQuery();
	   return $rst->fetchAllAssociative();		
	}	
	
	public function del_curricularunit($id)
    {
       $sql = "DELETE FROM curricularunits WHERE curricularunits_id=:id";
       $stmt = $this->connection->prepare($sql);
	   $stmt->bindValue('id', $id);
       $rst=$stmt->executeQuery();
	   return true;
    }
	
	public function add_curricularunit($name,$year,$languages_id,$users_id)
    {
		 $sql = "INSERT INTO curricularunits (name, year, languages_id, users_id, created_at, updated_at) 
		         VALUES (:name,:year, '$languages_id', '$users_id', NOW(), NOW())";
		 $stmt = $this->connection->prepare($sql);
		 $stmt->bindValue('name', $name);
		 $stmt->bindValue('year', $year);		 
         $rst=$stmt->executeQuery();
		 
		// get last curricularunits_id
		$sql = "select * from curricularunits order by curricularunits_id desc limit 1";
        $stmt = $this->connection->prepare($sql);	
		$rst=$stmt->executeQuery();			
		$last_curricularunit=$rst->fetchAssociative();
		return $last_curricularunit['curricularunits_id'];
				
	}
	
	public function add_curricularchapter($description,$curricularunits_id,$users_id)
    {
		 $sql = "INSERT INTO curricularchapters (description, curricularunits_id, created_at, updated_at, users_id) 
		         VALUES (:description,'$curricularunits_id', NOW(), NOW(), '$users_id')";
		 $stmt = $this->connection->prepare($sql);
		 $stmt->bindValue('description', $description);		 
         $rst=$stmt->executeQuery();
		 return true;
	}
	
	public function get_curricularchapters($id = FALSE)
	{			
		$sql = 'select curricularchapters_id, description from curricularchapters where curricularunits_id=' . $id;
		return $this->connection->fetchAllAssociative($sql);
	}

	public function get_curricularchapterById($id = FALSE)
	{			
		$sql = 'select curricularunits_id, description, users_id from curricularchapters where curricularchapters_id=:id';
		$stmt = $this->connection->prepare($sql);
		$stmt->bindValue('id', $id);		 
        $rst=$stmt->executeQuery();
		return $rst->fetchAssociative();
	}

	public function update_curricularchapter($id, $description)
	{			
		$sql = "update curricularchapters 
				set description=:description, updated_at=NOW() 
				where curricularchapters_id='$id'";
		$stmt = $this->connection->prepare($sql);
	    $stmt->bindValue('description', $description);		
		$rst=$stmt->executeQuery();           
		return true;
	}	
	

	public function add_curricularsection($description,$curricularchapters_id,$users_id)
    {
		 $sql = "INSERT INTO curricularsections (description, curricularchapters_id, created_at, updated_at, users_id) 
		         VALUES (:description,'$curricularchapters_id', NOW(), NOW(), '$users_id')";
		 $stmt = $this->connection->prepare($sql);
		 $stmt->bindValue('description', $description);		 
         $rst=$stmt->executeQuery();
		 return true;
	}	
	
	
	
	public function get_curricularsections($id = FALSE)
	{			
		$sql = 'select curricularsections_id, description, curricularchapters_id from curricularsections where curricularchapters_id=' . $id;
		return $this->connection->fetchAllAssociative($sql);
	}
	
	public function get_curricularsectionById($id = FALSE)
	{			
		$sql = 'select s.curricularsections_id, s.description as section_description, s.users_id, c.curricularchapters_id, c.description  as chapter_description, c.curricularunits_id from curricularsections as s 
		inner join curricularchapters as c
		on s.curricularchapters_id=c.curricularchapters_id
		where curricularsections_id=:id';
		$stmt = $this->connection->prepare($sql); 
		$stmt->bindValue('id', $id);		
		$rst=$stmt->executeQuery();           
		return $rst->fetchAssociative();
	}
	
	public function update_curricularsection($id, $description)
	{			
		$sql = "update curricularsections 
				set description=:description, updated_at=NOW() 
				where curricularsections_id='$id'";
		$stmt = $this->connection->prepare($sql);
	    $stmt->bindValue('description', $description);		
		$rst=$stmt->executeQuery();           
		return true;
	}

	public function add_curriculargoal($description,$curricularsections_id,$users_id)
    {
		 $sql = "INSERT INTO curriculargoals (description, curricularsections_id, created_at, updated_at, users_id) 
		         VALUES (:description,'$curricularsections_id', NOW(), NOW(), '$users_id')";
		 $stmt = $this->connection->prepare($sql);
		 $stmt->bindValue('description', $description);		 
         $rst=$stmt->executeQuery();
		 return true;
	}
	
	public function get_curriculargoalById($id = FALSE)
	{			
		$sql = 'select curricularsections_id, description, users_id from curriculargoals where curriculargoals_id=:id';
		$stmt = $this->connection->prepare($sql);
		$stmt->bindValue('id', $id);		 
        $rst=$stmt->executeQuery();
		return $rst->fetchAssociative();
	}

	public function update_curriculargoal($id, $description)
	{			
		$sql = "update curriculargoals 
				set description=:description, updated_at=NOW() 
				where curriculargoals_id='$id'";
		$stmt = $this->connection->prepare($sql);
	    $stmt->bindValue('description', $description);		
		$rst=$stmt->executeQuery();           
		return true;
	}	
	
	
	public function get_complexities()
	{			
		$sql = 'select complexities_id, description from complexities';
		return $this->connection->fetchAllAssociative($sql);
	}
	
	public function get_questions()
	{			
		$sql = "select q.questions_id as id, q.questiontypes_id as type, q.content as content, q.title as title, q.users_id, u.name as uname, c.description as cdescription, s.description as sdescription, cty.description as ctydescription, src.description as srcdescription, src.href as srchref  from questions as q 
		        inner join (curricularunits as u, curricularchapters as c, curricularsections as s, complexities as cty, sources as src) 
				on (q.curricularunits_id=u.curricularunits_id and  q.curricularchapters_id=c.curricularchapters_id and q.curricularsections_id=s.curricularsections_id and q.complexities_id=cty.complexities_id and q.sources_id=src.sources_id) 
				order by q.questions_id desc";
		return $this->connection->fetchAllAssociative($sql);
	}
	
	public function get_questionsByCurricularunitsID($id, $cc = FALSE, $cs = FALSE, $cty = FALSE )
	{			
		$sql = "select q.questions_id as id, q.questiontypes_id as type, q.content as content, q.title as title, q.users_id, u.name as uname, c.description as cdescription, s.description as sdescription, cty.description as ctydescription, src.description as srcdescription, src.href as srchref  from questions as q 
		        inner join (curricularunits as u, curricularchapters as c, curricularsections as s, complexities as cty, sources as src) 
				on (q.curricularunits_id=u.curricularunits_id and  q.curricularchapters_id=c.curricularchapters_id and q.curricularsections_id=s.curricularsections_id and q.complexities_id=cty.complexities_id and q.sources_id=src.sources_id) 
				where q.curricularunits_id=:id
				order by q.questions_id desc";
		
		if ($cty)	
			$sql = "select q.questions_id as id, q.questiontypes_id as type, q.content as content, q.title as title, q.users_id, u.name as uname, c.description as cdescription, s.description as sdescription, cty.description as ctydescription, src.description as srcdescription, src.href as srchref  from questions as q 
				inner join (curricularunits as u, curricularchapters as c, curricularsections as s, complexities as cty, sources as src) 
				on (q.curricularunits_id=u.curricularunits_id and  q.curricularchapters_id=c.curricularchapters_id and q.curricularsections_id=s.curricularsections_id and q.complexities_id=cty.complexities_id and q.sources_id=src.sources_id) 
				where (q.curricularunits_id=:id and cty.complexities_id=$cty)
				order by q.questions_id desc";			
			
		if ($cc)	
			$sql = "select q.questions_id as id, q.questiontypes_id as type, q.content as content, q.title as title, q.users_id, u.name as uname, c.description as cdescription, s.description as sdescription, cty.description as ctydescription, src.description as srcdescription, src.href as srchref  from questions as q 
				inner join (curricularunits as u, curricularchapters as c, curricularsections as s, complexities as cty, sources as src) 
				on (q.curricularunits_id=u.curricularunits_id and  q.curricularchapters_id=c.curricularchapters_id and q.curricularsections_id=s.curricularsections_id and q.complexities_id=cty.complexities_id and q.sources_id=src.sources_id) 
				where (q.curricularunits_id=:id and c.curricularchapters_id=$cc)
				order by q.questions_id desc";	
					
		if ($cc && $cty)	
			$sql = "select q.questions_id as id, q.questiontypes_id as type, q.content as content, q.title as title, q.users_id, u.name as uname, c.description as cdescription, s.description as sdescription, cty.description as ctydescription, src.description as srcdescription, src.href as srchref  from questions as q 
				inner join (curricularunits as u, curricularchapters as c, curricularsections as s, complexities as cty, sources as src) 
				on (q.curricularunits_id=u.curricularunits_id and  q.curricularchapters_id=c.curricularchapters_id and q.curricularsections_id=s.curricularsections_id and q.complexities_id=cty.complexities_id and q.sources_id=src.sources_id) 
				where (q.curricularunits_id=:id and c.curricularchapters_id=$cc and cty.complexities_id=$cty)
				order by q.questions_id desc";			
	
		if ($cc && $cs)	
			$sql = "select q.questions_id as id, q.questiontypes_id as type, q.content as content, q.title as title, q.users_id, u.name as uname, c.description as cdescription, s.description as sdescription, cty.description as ctydescription, src.description as srcdescription, src.href as srchref  from questions as q 
				inner join (curricularunits as u, curricularchapters as c, curricularsections as s, complexities as cty, sources as src) 
				on (q.curricularunits_id=u.curricularunits_id and  q.curricularchapters_id=c.curricularchapters_id and q.curricularsections_id=s.curricularsections_id and q.complexities_id=cty.complexities_id and q.sources_id=src.sources_id) 
				where (q.curricularunits_id=:id and c.curricularchapters_id=$cc and s.curricularsections_id=$cs)
				order by q.questions_id desc";	
	
		if ($cc && $cs && $cty)	
			$sql = "select q.questions_id as id, q.questiontypes_id as type, q.content as content, q.title as title, q.users_id, u.name as uname, c.description as cdescription, s.description as sdescription, cty.description as ctydescription, src.description as srcdescription, src.href as srchref  from questions as q 
				inner join (curricularunits as u, curricularchapters as c, curricularsections as s, complexities as cty, sources as src) 
				on (q.curricularunits_id=u.curricularunits_id and  q.curricularchapters_id=c.curricularchapters_id and q.curricularsections_id=s.curricularsections_id and q.complexities_id=cty.complexities_id and q.sources_id=src.sources_id) 
				where (q.curricularunits_id=:id and c.curricularchapters_id=$cc and s.curricularsections_id=$cs and cty.complexities_id=$cty)
				order by q.questions_id desc";					
		
		$stmt = $this->connection->prepare($sql); 
	    $stmt->bindValue('id', $id);		
		$rst=$stmt->executeQuery();           
		return $rst->fetchAllAssociative();
	}
	
	public function get_questionsByCurricularchapters_id($curricularchapters_id)
	{			
		$sql = "select q.questions_id from questions as q where q.curricularchapters_id='$curricularchapters_id'";
		return $this->connection->fetchAllAssociative($sql);
	}

	public function get_total_questionsByCurricularunits_id($curricularunits_id)
	{			
		$sql = "select curricularunits_id,count(curricularunits_id) AS questions_total from questions where curricularunits_id='$curricularunits_id' group by curricularunits_id";
		return $this->connection->fetchAllAssociative($sql);
	}

	public function get_total_questionsByCurricularchapters($curricularunits_id)
	{			
		$sql = "select c.description,count(q.curricularchapters_id) AS questions_total from questions as q inner join curricularchapters as c on q.curricularchapters_id=c.curricularchapters_id where q.curricularunits_id='$curricularunits_id' group by q.curricularchapters_id";
		return $this->connection->fetchAllAssociative($sql);
	}

	
	
	public function get_questionsByCurricularsections_id($curricularsections_id, $exams_id = FALSE)
	{				
		if ($exams_id)
			$sql = "select q.questions_id, q.content, q.title from questions as q where (q.curricularsections_id='$curricularsections_id'
			and q.questions_id 
			not in 
			(select questions_id from exam_has_questions where exams_id='$exams_id'))";
		else
			$sql = "select q.questions_id, q.content, q.title from questions as q where q.curricularsections_id='$curricularsections_id'";					
				
		return $this->connection->fetchAllAssociative($sql);
	}
	
	
	public function get_question_in_exam($exams_id, $questions_id)
	{			
		$sql = "select e.questions_id from exam_has_questions as e 
				where (e.exams_id='$exams_id' AND e.questions_id='$questions_id')";
		$stmt = $this->connection->prepare($sql);   
		$rst=$stmt->executeQuery();           
		return $rst->fetchAssociative();
	}
	
	public function get_questions_in_exam($exams_id)
	{			
		$sql = "select e.questions_id as id, e.marks as marks, q.content, q.title, q.complexities_id , q.questiontypes_id as type from exam_has_questions as e 
				inner join questions as q
				on e.questions_id=q.questions_id
				where e.exams_id='$exams_id'
				order by q.curricularchapters_id desc";
		return $this->connection->fetchAllAssociative($sql);		

	}
	
	public function get_total_points_in_exam($exams_id)
	{
		$sql = "select t.exams_id, SUM(marks) as total_points
				from (select e.exams_id, e.marks, q.complexities_id from exam_has_questions as e inner join questions as q on e.questions_id=q.questions_id where e.exams_id='$exams_id') as t";
		$stmt = $this->connection->prepare($sql);   
		$rst=$stmt->executeQuery();           
		return $rst->fetchAssociative();
	}
	
	public function get_answers_in_attempt($attempt_id)
	{			
		$sql = "SELECT answers_id, questions_id as id, answer, grade, answertypes_id FROM `answerstoquestions` WHERE usermadeexams_id='$attempt_id'";
		return $this->connection->fetchAllAssociative($sql);		

	}
	
	public function get_total_grade_in_attempt($attempt_id)
	{
		$sql = "select t.usermadeexams_id, SUM(grade) as total_grade
				from (SELECT usermadeexams_id, grade FROM `answerstoquestions` WHERE usermadeexams_id='$attempt_id') as t";
		$stmt = $this->connection->prepare($sql);   
		$rst=$stmt->executeQuery();           
		return $rst->fetchAssociative();
	}
	
		
	
	public function get_questionById($id)
	{			
		$sql = "select q.questions_id as id, q.complexities_id as points, q.questiontypes_id as type, q.content, q.correct_answer, q.title, q.users_id, u.curricularunits_id, u.name, c.description as cdescription, s.description as sdescription, cty.description as ctydescription, src.description as srcdescription, src.href as srchref from questions as q 
		        inner join (curricularunits as u, curricularchapters as c, curricularsections as s, complexities as cty, sources as src) 
				on (q.curricularunits_id=u.curricularunits_id and  q.curricularchapters_id=c.curricularchapters_id and q.curricularsections_id=s.curricularsections_id and q.complexities_id=cty.complexities_id and q.sources_id=src.sources_id) 
				where q.questions_id='$id'";
		$stmt = $this->connection->prepare($sql);   
		$rst=$stmt->executeQuery();           
		return $rst->fetchAssociative();
	}
	
	public function update_question($id, $curricularunit,$chapter, $section,$complexity, $content, $title, $correct_answer)
	{			
		$sql = "update questions 
				set curricularunits_id='$curricularunit', curricularchapters_id='$chapter', curricularsections_id='$section', complexities_id='$complexity', content=:content, title=:title, correct_answer=:correct_answer  
				where questions_id='$id'";
		$stmt = $this->connection->prepare($sql);
	    $stmt->bindValue('content', $content);
	    $stmt->bindValue('title', $title);
	    $stmt->bindValue('correct_answer', $correct_answer);		
		$rst=$stmt->executeQuery();           
		return true;
	}
	
	public function update_answer_in_attempt($answers_id, $comments, $grade)
	{			
		$sql = "update `answerstoquestions` 
				set grade='$grade', comments='$comments'
				where answers_id='$answers_id'";
		
		$stmt = $this->connection->prepare($sql);		
		$rst=$stmt->executeQuery();           
		return true;	

	}
	
	
	public function get_question($filename)
	{
		   $sql = "SELECT * FROM questions WHERE content='$filename'";
		   $stmt = $this->connection->prepare($sql);   
		   $rst=$stmt->executeQuery();           
		   return $rst->fetchAssociative(); 
	}
	
	public function add_question($curricularunit, $chapter, $section, $complexity, $content, $correct_answer, $title, $type, $user_id, $resolution_time, $sources_id = 1)
   {
         
		 $sql = "INSERT INTO questions (curricularunits_id, curricularchapters_id, curricularsections_id, complexities_id, content, correct_answer, title, languages_id, questiontypes_id, created_at, updated_at, users_id, resolution_time, sources_id) 
		         VALUES ('$curricularunit','$chapter', '$section', '$complexity', :content, :correct_answer, :title, '2', '$type', NOW(), NOW(), '$user_id', :resolution_time, :sources_id)";
		 $stmt = $this->connection->prepare($sql);
		 $stmt->bindValue('content', $content);
		 $stmt->bindValue('correct_answer', $correct_answer);		 
		 $stmt->bindValue('title', $title);	
		 $stmt->bindValue('resolution_time', $resolution_time);
		 $stmt->bindValue('sources_id', $sources_id);		 				 		 
         $rst=$stmt->executeQuery();
         
		// get last question_id
		$sql = "select * from questions order by questions_id desc limit 1";
        $stmt = $this->connection->prepare($sql);	
		$rst=$stmt->executeQuery();			
		$last_question=$rst->fetchAssociative();
		return $last_question['questions_id'];
      
   }
   
    public function exam_has_questionsByquestions_id($questions_id)	
   {
	  	$sql = "SELECT * FROM exam_has_questions WHERE questions_id='$questions_id'";
		   $stmt = $this->connection->prepare($sql);   
		   $rst=$stmt->executeQuery();           
		   return $rst->fetchAllAssociative(); 
   }   
   
   public function add_question_to_exam($exams_id, $questions_id)
   {
	   	$sql = "INSERT INTO exam_has_questions (exams_id, questions_id) 
		         VALUES ('$exams_id','$questions_id')";
		 $stmt = $this->connection->prepare($sql);
         $rst=$stmt->executeQuery();
		 
		 $sql = "update exams set exams_questions = exams_questions + 1
		         where exams_id='$exams_id'";
		 $stmt = $this->connection->prepare($sql);
         $rst=$stmt->executeQuery();
		 
         return true;
	   
   }

   public function update_question_to_exam($exams_id, $questions_id, $marks)
   {
		 
		 $sql = "update exam_has_questions set marks='$marks' where exams_id='$exams_id' and questions_id='$questions_id'";
		 $stmt = $this->connection->prepare($sql);
         $rst=$stmt->executeQuery();
		 
         return true;
	   
   }
   
   public function del_question_to_exam($exams_id, $questions_id)
   {
       $sql = "DELETE FROM exam_has_questions WHERE (exams_id='$exams_id' AND questions_id='$questions_id')";
       $stmt = $this->connection->prepare($sql);
       $rst=$stmt->executeQuery();
	   
	   	$sql = "update exams set exams_questions = exams_questions - 1
			    where exams_id='$exams_id'";
		$stmt = $this->connection->prepare($sql);
        $rst=$stmt->executeQuery();
	   
       return true;
   }
   
   public function add_exam($curricularunit, $user_id, $name, $introduction, $n_questions, $length)
   {
         
		 $sql = "INSERT INTO exams (curricularunits_id, users_id, exams_name, exams_introduction, exams_questions, resolution_time, creation_date) 
		         VALUES ('$curricularunit','$user_id', :name, :introduction, '$n_questions', :length, NOW())";
		 $stmt = $this->connection->prepare($sql);
		 $stmt->bindValue('name', $name);
		 $stmt->bindValue('length', $length);
		 $stmt->bindValue('introduction', $introduction);
         $rst=$stmt->executeQuery();
         
		 // get last exam_id
		$sql = "select * from exams order by exams_id desc limit 1";
        $stmt = $this->connection->prepare($sql);	
		$rst=$stmt->executeQuery();			
		$last_exam=$rst->fetchAssociative();
		return $last_exam['exams_id'];
      
   }
   
	public function get_exam($id)
	{
		   $sql = "SELECT e.*, t.questions_total FROM exams as e 
				inner join exams_questions_total as t
				on e.exams_id=t.exams_id		   
				WHERE e.exams_id=:id";
		   $stmt = $this->connection->prepare($sql); 
		   $stmt->bindValue('id', $id);		   
		   $rst=$stmt->executeQuery();           
		   return $rst->fetchAssociative();
	}
	
	public function del_exam($id = FALSE)
	{
	   $sql = "DELETE FROM exam_has_questions WHERE exams_id=:id";	   
       $stmt = $this->connection->prepare($sql);
	   $stmt->bindValue('id', $id);		   
	   $rst=$stmt->executeQuery();
	   
	   $sql = "DELETE FROM exams WHERE exams_id=:id";
       $stmt = $this->connection->prepare($sql);
	   $stmt->bindValue('id', $id);		   
	   $rst=$stmt->executeQuery();
	   return true; 
	}
	
	public function exam_update_visibility($id)
	{
		$sql = "update exams set visibility = !visibility where exams_id=:id";		
		$stmt = $this->connection->prepare($sql); 
		$stmt->bindValue('id', $id);		   
		$rst=$stmt->executeQuery(); 

	   $sql = "SELECT visibility FROM exams WHERE exams_id=:id";
	   $stmt = $this->connection->prepare($sql); 
	   $stmt->bindValue('id', $id);		   
	   $rst=$stmt->executeQuery();           
	   return $rst->fetchAssociative();		
				
	}
	
	
	public function get_attempt($id)
	{
		   	$sql = "select a.usermadeexams_id, a.exams_id, a.start_date, a.finish_date, e.exams_name, u.name as user from usermadeexams as a
		        inner join (users as u,  exams as e)
				on (a.users_id=u.users_id and a.exams_id=e.exams_id)
				where usermadeexams_id=:id";
		   $stmt = $this->connection->prepare($sql); 
		   $stmt->bindValue('id', $id);		   
		   $rst=$stmt->executeQuery();           
		   return $rst->fetchAssociative();
	}	
	
	public function get_exams()
	{			
		$sql = "select e.exams_id, e.users_id, e.creation_date, e.exams_name, e.visibility, u.name as user from exams as e
		        inner join users as u 
				on e.users_id=u.users_id
				order by e.creation_date desc";
		return $this->connection->fetchAllAssociative($sql);
	}
	
	public function get_examsByCurricularunitID($id)
	{			
	   	$sql = "select e.exams_id, e.users_id, e.creation_date, e.exams_name, e.visibility, u.name as user from exams as e
		        inner join users as u 
				on e.users_id=u.users_id
				where e.curricularunits_id=:id
				order by e.creation_date desc";
	   $stmt = $this->connection->prepare($sql); 
	   $stmt->bindValue('id', $id);		   
	   $rst=$stmt->executeQuery();           
	   return $rst->fetchAllAssociative();
	}
	
	
	public function get_attempts($id = FALSE)
	{			
		if (!$id)
		$sql = "select a.usermadeexams_id, a.users_id, a.start_date, a.finish_date, e.exams_name, e.users_id as exams_users_id, u.name as user from usermadeexams as a
		        inner join (users as u,  exams as e)
				on (a.users_id=u.users_id and a.exams_id=e.exams_id)
				order by a.start_date desc";
		else
		$sql = "select a.usermadeexams_id, a.users_id, a.start_date, a.finish_date, u.name as user, u.email from usermadeexams as a
		        inner join users as u
				on a.users_id=u.users_id
				where a.exams_id='$id'
				order by a.start_date desc";
			
		return $this->connection->fetchAllAssociative($sql);
	}
	
	public function get_attemptsByCurricularunitID($id = FALSE)
	{
		$sql = "select a.usermadeexams_id, a.users_id, a.start_date, a.finish_date, e.exams_name, e.curricularunits_id, e.users_id as exams_users_id, u.name as user from usermadeexams as a
		        inner join (users as u,  exams as e)
				on (a.users_id=u.users_id and a.exams_id=e.exams_id)
				where e.curricularunits_id=:id
				order by a.start_date desc";
	   
	   $stmt = $this->connection->prepare($sql); 
	   $stmt->bindValue('id', $id);		   
	   $rst=$stmt->executeQuery();           
	   return $rst->fetchAllAssociative();					
	}

	public function get_attemptsByUserID($users_id = FALSE)
	{
		$sql = "select * from usermadeexams where users_id=:id";

		$stmt = $this->connection->prepare($sql); 
		$stmt->bindValue('id', $users_id);		   
		$rst=$stmt->executeQuery();           
		return $rst->fetchAllAssociative();					
	}
	
	

	public function del_attempt($id = FALSE)
	{
	   $sql = "DELETE FROM answerstoquestions WHERE usermadeexams_id='$id'";
       $stmt = $this->connection->prepare($sql);
	   $rst=$stmt->executeQuery();
	   $sql = "DELETE FROM usermadeexams WHERE usermadeexams_id='$id'";
       $stmt = $this->connection->prepare($sql);
       $rst=$stmt->executeQuery();
	   return true; 
	}
	
   
    public function del_question($id)
   {
       $sql = "SELECT questiontypes_id as type, content FROM questions WHERE questions_id='$id'";
       $stmt = $this->connection->prepare($sql);   
       $rst=$stmt->executeQuery();           
       $result = $rst->fetchAssociative();
      
       $sql = "DELETE FROM questions WHERE questions_id='$id'";
       $stmt = $this->connection->prepare($sql);
       $rst=$stmt->executeQuery();
       return $result;
   }
   
    public function get_total_curricularchapters($id)
   {
   
	$sql = "SELECT COUNT(c.curricularchapters_id) AS 'total' FROM curricularchapters AS c where curricularunits_id='$id'";
    $stmt = $this->connection->prepare($sql);   
    $rst=$stmt->executeQuery();           
    $result = $rst->fetchAssociative();
	return $result['total'];	   
	}
	
	public function add_usermadeexam($exam_id, $user_id, $timer)
   {
         
		 $sql = "INSERT INTO usermadeexams (exams_id, users_id, start_date, timer) 
		         VALUES (:exam_id,'$user_id', NOW(), :timer)";
		 $stmt = $this->connection->prepare($sql);
		 $stmt->bindValue('exam_id', $exam_id);
		 $stmt->bindValue('timer', $timer*60);
         $rst=$stmt->executeQuery();
         
		 // get last usermadeexam_id
		$sql = "select * from usermadeexams order by usermadeexams_id desc limit 1";
        $stmt = $this->connection->prepare($sql);	
		$rst=$stmt->executeQuery();			
		$last_usermadeexam=$rst->fetchAssociative();
		return $last_usermadeexam['usermadeexams_id'];
      
   }
   
   public function update_timer($usermadeexams_id,$timer)
   {
	   	$sql = "update usermadeexams set timer='$timer' where usermadeexams_id='$usermadeexams_id'";
		$stmt = $this->connection->prepare($sql);	
		$rst=$stmt->executeQuery();           
		return true; 
   }
   
   
   
   	public function close_usermadeexam($usermadeexams_id)
   {
	  	$sql = "update usermadeexams 
				set finish_date=NOW()
				where usermadeexams_id='$usermadeexams_id'";
		$stmt = $this->connection->prepare($sql);	
		$rst=$stmt->executeQuery();           
		return true; 
   }
   
    public function get_usermadeexamsByexams_id($exams_id)
   {
	  	$sql = "select * from usermadeexams where exams_id='$exams_id'";
		return $this->connection->fetchAllAssociative($sql);
   }
   
    public function get_usermadeexamsByID($id)
   {
	  	$sql = "select * from usermadeexams where usermadeexams_id='$id'";
		$stmt = $this->connection->prepare($sql);	
		$rst=$stmt->executeQuery();
		return $rst->fetchAssociative();
   }
   
   public function get_usermadeexamsByusers_idAndByexams_id($users_id, $exams_id)
   {
	  	$sql = "select * from usermadeexams where (exams_id='$exams_id' and users_id='$users_id' and finish_date is null)";
		$stmt = $this->connection->prepare($sql);	
		$rst=$stmt->executeQuery();
		return $rst->fetchAssociative();
   }
   
   
   
   	public function get_answers_to_questions($usermadeexams_id)
   {   
	$sql = "SELECT a.*, q.complexities_id as points FROM answerstoquestions as a 
			        inner join questions  as q
					on a.questions_id=q.questions_id 
					where a.usermadeexams_id='$usermadeexams_id'
					order by q.curricularchapters_id desc";
    return $this->connection->fetchAllAssociative($sql);	   
	}

	public function get_question_marks($exams_id, $questions_id)
	{   
	 $sql = "SELECT marks FROM exam_has_questions 
					 where exams_id='$exams_id' and questions_id='$questions_id'";
	 $stmt = $this->connection->prepare($sql);	
	 $rst=$stmt->executeQuery();
	 return $rst->fetchAssociative();   
	}

	public function get_question_marks_($usermadeexams_id, $questions_id)
	{   
	 $sql = "SELECT marks FROM exam_has_questions 
					 where exams_id=(SELECT exams_id FROM usermadeexams WHERE usermadeexams_id='$usermadeexams_id') and questions_id='$questions_id'";
	 $stmt = $this->connection->prepare($sql);	
	 $rst=$stmt->executeQuery();
	 return $rst->fetchAssociative();   
	}

    
	public function get_answer_to_question($usermadeexams_id, $answer_question_id)
   {   
	$sql = "SELECT * FROM answerstoquestions where (usermadeexams_id='$usermadeexams_id' and questions_id='$answer_question_id')";
    $stmt = $this->connection->prepare($sql);   
    $rst=$stmt->executeQuery();           
    return $rst->fetchAssociative();	   
	}
	
	public function get_answer_to_questionbyID($answers_id)
	{			
		$sql = "SELECT answers_id, usermadeexams_id, questions_id, answer, grade, answertypes_id, comments FROM `answerstoquestions` WHERE answers_id='$answers_id'";
		$stmt = $this->connection->prepare($sql);   
		$rst=$stmt->executeQuery();           
		return $rst->fetchAssociative();
	}
	
	
	
	
	public function add_answer_to_question($usermadeexams_id, $answer_question_id, $answer, $answertypes_id)
   {
		$sql = "INSERT INTO answerstoquestions (usermadeexams_id, questions_id, answer, answertypes_id) 
		         VALUES ('$usermadeexams_id','$answer_question_id', :answer, '$answertypes_id')";
		 $stmt = $this->connection->prepare($sql);
		 $stmt->bindValue('answer', $answer);
         $rst=$stmt->executeQuery();
         return true;
	   
   }
   
   	public function update_answer_to_question($usermadeexams_id, $answer_question_id, $answer)
	{			
		$sql = "update answerstoquestions 
				set answer=:answer
				where (usermadeexams_id='$usermadeexams_id' and questions_id='$answer_question_id')";
		$stmt = $this->connection->prepare($sql);
		$stmt->bindValue('answer', $answer);		
		$rst=$stmt->executeQuery();           
		return true;
	}
	
	public function get_users()
	{			
		$sql = 'select users_id, name, email, created_at, created_by from users order by users_id desc';
		return $this->connection->fetchAllAssociative($sql);
	}
	
	public function get_curricularunits_enrolled($users_id) 
	{
		$sql = "SELECT uc.users_id, c.curricularunits_id, c.name FROM `user_has_curricularunits` as uc 
				inner join curricularunits as c 
				on uc.curricularunits_id=c.curricularunits_id 
				where uc.users_id=:users_id";
	   $stmt = $this->connection->prepare($sql);
	   $stmt->bindValue('users_id', $users_id);
       $rst=$stmt->executeQuery();
	   return $rst->fetchAllAssociative();
	}

	public function get_curricular_units_enrolled($users_id)
	{			
		$sql = "select u.roles, uc.curricularunits_id as id, uc.enrolled_at, c.year, c.name, r.curricularunit_has_reviewers_id  from user_has_curricularunits as uc 
		inner join curricularunits as c on uc.curricularunits_id=c.curricularunits_id
		inner join users as u on uc.users_id=u.users_id
		left join curricularunit_has_reviewers as r on (uc.users_id=r.users_id and uc.curricularunits_id=r.curricularunits_id)				
		where uc.users_id='$users_id'
		order by uc.enrolled_at asc";
		return $this->connection->fetchAllAssociative($sql);		

	}
	
	public function get_curricular_units_not_enrolled($users_id)
	{				

			$sql = "select c.curricularunits_id as id, c.year, c.name from curricularunits as c where 
			c.curricularunits_id 
			not in 
			(select curricularunits_id from user_has_curricularunits where users_id='$users_id')";				
				
		return $this->connection->fetchAllAssociative($sql);
	}
   
    public function user_unenroll_unit($id, $users_id)
   {
       $sql = "DELETE FROM user_has_curricularunits WHERE (curricularunits_id='$id' AND users_id='$users_id')";
       $stmt = $this->connection->prepare($sql);
       $rst=$stmt->executeQuery();
	   
       return true;
   }
  
    public function user_enroll_unit($id, $users_id)
   {
	   	$sql = "INSERT INTO user_has_curricularunits (curricularunits_id, users_id, enrolled_at) 
		         VALUES ('$id','$users_id', NOW())";
		 $stmt = $this->connection->prepare($sql);
         $rst=$stmt->executeQuery();
		 		 
         return true;
	   
   }

   public function user_unenroll_reviewer($id, $users_id)
   {
       $sql = "DELETE FROM curricularunit_has_reviewers WHERE (curricularunits_id='$id' AND users_id='$users_id')";
       $stmt = $this->connection->prepare($sql);
       $rst=$stmt->executeQuery();
	   
       return true;
   }

   public function user_enroll_reviewer($id, $users_id)
   {
	   	$sql = "INSERT INTO curricularunit_has_reviewers (curricularunits_id, users_id) 
		         VALUES ('$id','$users_id')";
		 $stmt = $this->connection->prepare($sql);
         $rst=$stmt->executeQuery();
		 		 
         return true;
	   
   }


   public function get_user_reviewer($id, $users_id)
   {
       $sql = "SELECT curricularunits_id FROM curricularunit_has_reviewers WHERE (curricularunits_id='$id' AND users_id='$users_id')";
       $stmt = $this->connection->prepare($sql);
       $rst=$stmt->executeQuery();
	   return $rst->fetchAssociative();
   }

   
    public function add_to_question_has_cognitivegoals($questions_id, $cognitivegoals_id)
   {
	   	$sql = "INSERT INTO question_has_cognitivegoals (questions_id, cognitivegoals_id) 
		         VALUES ('$questions_id','$cognitivegoals_id')";
		 $stmt = $this->connection->prepare($sql);
         $rst=$stmt->executeQuery();		 		 
         return true;	   
   }
   
    public function add_to_question_has_curricularskills($questions_id, $curricularskills_id)
   {
	   	$sql = "INSERT INTO question_has_curricularskills(questions_id, curricularskills_id) 
		         VALUES ('$questions_id','$curricularskills_id')";
		 $stmt = $this->connection->prepare($sql);
         $rst=$stmt->executeQuery();		 		 
         return true;	   
   }

   public function del_user($users_id)
   {
	  $sql = "DELETE FROM users WHERE users_id=:id";
	  $stmt = $this->connection->prepare($sql);
	  $stmt->bindValue('id', $users_id);
	  $rst=$stmt->executeQuery();
	  return true;
   }
   
   
   
   
	
	
}
