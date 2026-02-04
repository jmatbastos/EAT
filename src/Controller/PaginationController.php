<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class PaginationController extends AbstractController
{
    
	

    // back to previous page
    private function previous_page($currentPage){

    $previousPage = $currentPage - 1;
    if($currentPage > 1){
        $previous=array('class' => '','href' => "$previousPage", 'page' => '<');
        return $previous;
    }  
   }
   
   // go to next page
   private function next_page($currentPage, $totalPages){
    $nextPage = $currentPage + 1;
    if($currentPage < $totalPages) {
        $next=array('class' => '','href' => "$nextPage", 'page' => '>');
        return $next;
   }}

   // displaying total pagination numbers
    private function pagination_numbers($currentPage, $totalPages){    
        $adjacents = "2";
        $second_last = $totalPages - 1; // total pages minus 1
        
        $pagelink=[];
        if ($totalPages <= 5){   
        for ($counter = 1; $counter <= $totalPages; $counter++){
        if ($counter == $currentPage) {
            array_push($pagelink,array('class' => 'is-active','href' => "$counter", 'page' => "$counter"));
        }else{
            array_push($pagelink,array('class' => '','href' => "$counter", 'page' => "$counter"));
        }
        }
        }elseif ($totalPages > 5){
        if($currentPage <= 4) { 
            for ($counter = 1; $counter < 8; $counter++){ 
            if ($counter == $currentPage) {
                array_push($pagelink,array('class' => 'is-active','href' => "$counter", 'page' => "$counter"));
            }else{
                array_push($pagelink,array('class' => '','href' => "$counter", 'page' => "$counter"));               
            }
            }

        array_push($pagelink,array('class' => '','href' => '', 'page' => '...'));         
        array_push($pagelink,array('class' => '','href' => "$second_last", 'page' => "$second_last"));         
        array_push($pagelink,array('class' => '','href' => "$totalPages", 'page' => "$totalPages"));          
        }elseif($currentPage > 4 && $currentPage < $totalPages - 4) { 
        array_push($pagelink,array('class' => '','href' => '1', 'page' => '1'));        
        array_push($pagelink,array('class' => '','href' => '2', 'page' => '2')); 
        array_push($pagelink,array('class' => '','href' => '', 'page' => '...'));  
        for (
            $counter = $currentPage - $adjacents;
            $counter <= $currentPage + $adjacents;
            $counter++
            ) { 
            if ($counter == $currentPage) {
            array_push($pagelink,array('class' => 'is-active','href' => "$counter", 'page' => "$counter")); 
            }else{
                array_push($pagelink,array('class' => '','href' => "$counter", 'page' => "$counter"));                 
            }                  
        }
        array_push($pagelink,array('class' => '','href' => '', 'page' => '...'));  
        array_push($pagelink,array('class' => '','href' => "$second_last", 'page' => "$second_last"));          
        array_push($pagelink,array('class' => '','href' => "$totalPages", 'page' => "$totalPages"));         
        }else {
            array_push($pagelink,array('class' => '','href' => '1', 'page' => '1'));        
            array_push($pagelink,array('class' => '','href' => '2', 'page' => '2')); 
        array_push($pagelink,array('class' => '','href' => '', 'page' => '...'));  
        for (
            $counter = $totalPages - 6;
            $counter <= $totalPages;
            $counter++
            ) {
            if ($counter == $currentPage) {
                array_push($pagelink,array('class' => 'is-active','href' => "$counter", 'page' => "$counter"));            
            }else{
                array_push($pagelink,array('class' => '','href' => "$counter", 'page' => "$counter"));                 
            }                   
        }}}
        return $pagelink;
        }

    // final script to create pagination
    public function pagination($totalRecordsPerPage,$currentPage,$totalRecords){



        if (!$currentPage)
            $currentPage=1;
        
        $totalPages = ceil($totalRecords / $totalRecordsPerPage);       
        $pagination=[];

        array_push($pagination, $this->previous_page($currentPage));
        array_push($pagination, $this->pagination_numbers($currentPage,$totalPages));
        array_push($pagination, $this->next_page($currentPage, $totalPages));
    
        return $pagination;
        
        
    }
	
	
	
}
