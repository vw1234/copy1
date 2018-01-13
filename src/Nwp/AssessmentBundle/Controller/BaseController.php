<?php

namespace Nwp\AssessmentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Doctrine\ORM\Query;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class BaseController extends Controller
{
    
    public function  getApplicationValues($className, $alias, $fieldNames=null, $fieldNamesSpecial=null, $classSession=null, $joinTable=null, $joinTableAlias=null, $WhereStatement=null, $OrderBy=null, $OrderDirection=null)        
    {  
        $values="";
        $em = $this->get('doctrine')->getEntityManager();
         
        if (($classSession != "") && ($classSession !=null)) {
            $request = $this->getRequest();
            $session = $request->getSession();
            
            if ($session->has($classSession)) {
               $values = $session->get($classSession); 
               //this code fixes "Entities passed to the choice field must be managed" symfony error message  
               foreach ($values as $key => $value) { 
                    if (is_object($value)) {
                        $values[$key] = $em->merge($value);
                    }
                }
               //echo "got values from session: ".$classSession."</br>";
            }
       } 
   
       if ($values == "") {
            
           if (($fieldNames)==null ||($fieldNames=="")) {
               $fields=$alias;  //select * fields
           } else {
                $fields="";
                $field_number=1;
                $fields_array = explode(",", $fieldNames);
            
                foreach ($fields_array as $fieldName) {
                    $fields .= $fieldName;
                    if ($field_number !=count($fields_array)) {
                        $fields .=",";
                    }
                    $field_number++;
                }
                
           }
           
           
            $qb = $em->createQueryBuilder();
            $qb ->select($fields);
            if (($fieldNamesSpecial !='') && ($fieldNamesSpecial !=null)) {
                $qb ->addSelect($fieldNamesSpecial);
            }
            $qb ->from($className, $alias);
            if (($joinTable !="") && ($joinTable !=null)){
                $qb ->leftJoin($alias.".".$joinTable,$joinTableAlias);
            }
            if (($WhereStatement !="") && ($WhereStatement !=null)){
                $qb ->where($WhereStatement);
            } 
            if (($OrderBy !="") && ($OrderBy !=null)){
                $qb ->orderBy($OrderBy, $OrderDirection);
            }
            //echo $qb->getDql()."<br>";  
          
            $query = $qb->getQuery();
            $values= $query->getResult();
            
            if ($classSession != "") {
               $session->set($classSession, $values);
               // echo "created session ".$classSession."</br>";    
            }         
       }        
       return $values;    
    }
    
    public function arrayMultiSearch($arrayName,$searchValue,$field) {
        //this function searches for value in associative multidimensional array
        //if value is not found, $arr returns null
        $arr = array_filter($arrayName, 
         function($ar) use ($searchValue,$field) {
            return ($ar[$field] == $searchValue);//return ($ar['name'] == 'cat 1' AND $ar['id'] == '3');// you can add multiple conditions
         }); 
         return $arr;
    }
    
    
    public function batchApplicationActionDelete($entities_array, $entity=null, $dependency=null){
        
        $scoring_item_ids="";
        $exts="";
        
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
                
            for($en=0;$en<sizeof($entities_array);$en++){ 
                $numUpdated=0;
                $Ids = implode(",", $entities_array[$en]['ids']);
               
                $sql="DELETE FROM ".$entities_array[$en]['classname']." ".$entities_array[$en]['alias']." WHERE ".
                      $entities_array[$en]['alias'].".".$entities_array[$en]['fieldname']." IN (".$Ids.")";
             
                //echo $sql."<br>";
                $q = $em->createQuery($sql);
                $numUpdated = $q->execute();
                
                if (($numUpdated>0) && ($entities_array[$en]['classname']=="Nwp\AssessmentBundle\Entity\ScoringItem")) {
                    
                    $scoring_item_ids= $entities_array[$en]['ids'];
                    if (isset ($entities_array[$en]['exts'])) {
                        $exts = $entities_array[$en]['exts'];
                    }
                }
             }
                
             $em->getConnection()->commit();
             

             if (($scoring_item_ids!="") && ($exts!="")){ //delete the physical paper files
                $folder = __DIR__.'/../../../../'.$this->container->getParameter('nwp_assessment.file_uploads').'/papers/';
                for($i=0;$i<count($scoring_item_ids);$i++){ 
                    $filename = $folder. $scoring_item_ids[$i].".".$exts[$i];
                    if (file_exists($filename)) {
                        unlink ($filename);
                    }
                }
            } 
  
              
         } catch(\Doctrine\DBAL\DBALException $e) {
            $em->getConnection()->rollback();
           
            $this->get('session')->getFlashBag()->add('error', 'The '.$entity.' could not be deleted due to dependencies.  To delete these '.$entity.', you must first delete the '.$dependency. ' that are using these '.$entity.'.');
         }
      
    }
    
    public function batchApplicationAction($className=null){
        
        if ($this->get('request')->get('btn_batch_action_confirm')) {
            $idx_array = array();
            $all_elements = array();
            $items_array = array();
            $action="";
            $entities="";
            $exts="";
            
            if ($request = json_decode($this->get('request')->get('request_data'), true)) { 
                if (isset($request['idx'])) {
                    $idx_array = $request['idx'];
                }
                if (isset($request['all_elements'])) {
                    $all_elements  = $request['all_elements'];
                }
                if (isset($request['action'])) {
                    $action  = $request['action'];
                }
            } else {
                $request = $this->getRequest();
                $action = $this->get('request')->get('action');
                $idx_array = $this->get('request')->get('idx');
                $all_elements = $this->get('request')->get('all_elements');
            }
         
            if (count($idx_array) != 0) {
                foreach($idx_array as $idx) {
                    if ($className=="ScoringItem") {
                        list($id, $ext) = explode('.', $idx);
                        $ids[]=$id;
                        $exts[]=$ext;
                    } else {
                       $ids[]=$idx; 
                    }
                }  
            }
         
            if (($all_elements) && (count($idx_array) == 0)) { //all elements will be ignored if checkboxes are selected to prevent unintended user batch actions for all elements
                list($filterForm, $queryBuilder) = $this->filter();
                $sql = $queryBuilder->getDql();
                $em = $this->getDoctrine()->getManager();
                $q = $em->createQuery($sql);
                $entities= $q->execute();
       
                foreach($entities as $entity) {
                    $ids[]=$entity->getId();
                    if ($className=="ScoringItem") {
                        $exts[]=$entity->getFileType();
                    }
                }
           
            }
            
            if ((($all_elements) && ($entities != "")) || (count($idx_array) != 0)) {
               $items_array =array('ids' => $ids,'exts' => $exts, 'entities' => $entities);
            } //nothing was selected if the above if statement did not execute
            
            return $items_array;
        }//confirm button was clicked
   } 
   
   public function batchApplicationActionExport($className,$fields,$data) { 
       
       
       $filename = "export_".$className."_".date("Y_m_d_His").".csv"; 
      
       $response = $this->render('NwpAssessmentBundle:Default:export_csv.html.twig', array('fields' => $fields, 'data' => $data)); 
       $response->headers->set('Content-Type', 'text/csv');
       $response->headers->set('Content-Disposition', 'attachment; filename='.$filename); 
       return $response; 
    }
   
   public function getUserProjects($fieldNames=null,$entity=null, $action=null) {
       $role_admin_id=$this->isRoleAdmin();
       $Ids="";
       
        if ($action !="") {
           $project_capability_array = $this->getUserProjectRoleCapabilities();
           
          
            foreach ($project_capability_array as $p) {
                $new_project_capability_array=$p;
                $project_capability_array_size = sizeof($new_project_capability_array);
                
                for($n=0;$n<$project_capability_array_size;$n++){  
                    if (($new_project_capability_array[$n]['action_name']==$action) && ($new_project_capability_array[$n]['object']==$entity)) {
                       $Ids.=$new_project_capability_array[$n]['project_id'].",";
                    }
                }       
            }  
            
           $Ids = substr($Ids, 0, -1); //strip last comma         
       }
       
       if ($role_admin_id !="") {
           return $this->getApplicationValues("NwpAssessmentBundle:Project", "p", $fieldNames, null, null, null,null, null,'p.startDate');
       } elseif ($Ids!="") {
           //$entities= $this->getApplicationValues("NwpAssessmentBundle:Project", "p", $fieldNames, null, null, "pu","pu", "pu.user=".$this->container->get('security.context')->getToken()->getUser()->getId(),'p.name');           
           return $this->getApplicationValues("NwpAssessmentBundle:Project", "p", $fieldNames, null, null, null,null, "p.id IN (".$Ids.")",'p.startDate');      
           
       }     
    }
    
    public function getUserEvents($site=null,$fieldNames=null,$entity=null, $action=null,$project_id=null) {
        //call this function in EventController also if the function remains the same after testing
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
       
        $events_with_access="";
        $entities="";     
        
        if ($site=="projectsite") {  //The url of the page designates that it's the Project Site      
            if ($action !="") {
                $project_event_capability_array = $this->getUserProjectRoleEventCapabilities();

                if ($project_id != null) {
                    if (isset ($project_event_capability_array[$project_id])) {
                        foreach ($project_event_capability_array[$project_id] as $pe) {
                            $new_project_event_capability_array=$pe;
                            $project_event_capability_array_size = sizeof($new_project_event_capability_array);
                            for($n=0;$n<$project_event_capability_array_size;$n++){  
                                if (($new_project_event_capability_array[$n]['action_name']==$action) && ($new_project_event_capability_array[$n]['object']==$entity)) {
                                   $events_with_access.=$new_project_event_capability_array[$n]['event_id'].",";
                                }
                            }       
                        } 
                    }
                } else { //project id is not known, looop through all project/events combo and extract unique values from array
                    foreach ($project_event_capability_array as $pe) {
                        foreach ($pe as $e) {             
                            $new_project_event_capability_array=$e;
                            $project_event_capability_array_size = sizeof($new_project_event_capability_array);
                            for($n=0;$n<$project_event_capability_array_size;$n++){  
                                if (($new_project_event_capability_array[$n]['action_name']==$action) && ($new_project_event_capability_array[$n]['object']==$entity)) {
                                    $events_with_access.=$new_project_event_capability_array[$n]['event_id'].",";
                                }
                            }  
                        }       
                    }
                }
           }
           
           $events_with_access_unique=array_unique(explode(',', $events_with_access));
           $events_with_access = implode(",", $events_with_access_unique);
          
       } else { //event site or default
            $current_event = $this->getCurrentEvent();
            $events_with_access_array=$session->get("EventsWithAccessUserSession");
            $events_with_access="";
        
            foreach($events_with_access_array as $ea) {
                $events_with_access .= $ea.",";
            }  
       }
        
       $events_with_access = substr($events_with_access, 0, -1); //strip last comma
        
        if ($events_with_access!="") {
           //$entities= $this->getApplicationValues("NwpAssessmentBundle:Project", "p", $fieldNames, null, null, "pu","pu", "pu.user=".$this->container->get('security.context')->getToken()->getUser()->getId(),'p.name');           
           $entities= $this->getApplicationValues("NwpAssessmentBundle:Event", "e", $fieldNames, null, null, null,null, "e.id IN (".$events_with_access.")",'e.startDate');      
           
        }  
         
         return $entities;
            
    }
      
    public function getUserProjectRoleCapabilities() {
       $project_capability_array=array();
       $request = $this->getRequest();
       $session = $request->getSession();
       $user_id=$this->container->get('security.context')->getToken()->getUser()->getId();
       
        
       if  (($session->has("ProjectRoleUserSession")) && ($session->get("ProjectRoleUserSession") !="") 
           
             && ((isset(array_keys( $session->get("ProjectRoleUserSession"))[0])) 
                && (array_keys( $session->get("ProjectRoleUserSession"))[0]['user_id']==$user_id))   
            )   
            { 
          // $session_project_capability_array = $session->get("ProjectRoleUserSession"); 
           //$first_key=array_keys($session_project_capability_array)[0];          
          // if ($session_project_capability_array[$first_key][0]['user_id']==$user_id) {
             $project_capability_array = $session->get("ProjectRoleUserSession");  
          // }
       } else  {  
           $role_admin_id=$this->isRoleAdmin();
           if ($role_admin_id !="") { //Admins have access to all projects    
                $sql="SELECT p.id project_id, p.name project_name, p.start_date, p.end_date, 
                   rc.role_id, r.name role_name, e.id object_id, e.name object, a.id action_id, a.name action_name,".$user_id." user_id 
                   FROM role_capability rc
                   LEFT JOIN role r ON rc.role_id = r.id
                   LEFT JOIN system_entity e ON rc.entity_id = e.id
                   LEFT JOIN system_action a ON rc.action_id = a.id
                   CROSS JOIN project p
                   LEFT JOIN structure s ON s.id=rc.structure_id
                   WHERE s.name='Project' AND rc.role_id=".$role_admin_id;
           } else {
                
                $sql = "SELECT pu.project_id, p.name project_name, p.start_date, p.end_date, 
                   rc.role_id, r.name role_name, e.id object_id, e.name object, a.id action_id, a.name action_name,".$user_id." user_id 
                   FROM role_capability rc
                   LEFT JOIN role r ON rc.role_id = r.id
                   LEFT JOIN system_entity e ON rc.entity_id = e.id
                   LEFT JOIN system_action a ON rc.action_id = a.id
                   LEFT JOIN project_user pu ON pu.role_id = r.id AND pu.role_id = rc.role_id
                   LEFT JOIN project p on p.id=pu.project_id
                   LEFT JOIN structure s on s.id=rc.structure_id
                   WHERE s.name='Project' and pu.user_id=".$user_id;
           }
           
           $dbh= $this->get('database_connection');
           $stmt = $dbh->query($sql); 
           $capability_array = $stmt->fetchAll(\PDO::FETCH_ASSOC);
           
           $capability_array_size=sizeof($capability_array);
           
           for($c=0;$c<$capability_array_size;$c++){  
                if ($this->checkAccessTimeframe("projectsite", null, $capability_array[$c]["role_id"], $capability_array[$c]["action_name"], $capability_array[$c]["project_id"], $capability_array[$c]["object"], null,$capability_array[$c]["start_date"],$capability_array[$c]["end_date"])) {
                    $project_capability_array[$capability_array[$c]["project_id"]][]=$capability_array[$c];
                }
           }  
           
           //$project_capability_array=array_values($project_capability_array);
           //var_dump($project_capability_array);
                    
           $session->set("ProjectRoleUserSession",  $project_capability_array);
       }
       
        
        return $project_capability_array;
    }
    
    public function getUserProjectRoleEventCapabilities() {
       //This is used for projectsite, events that projectsite users have access to 
       $session_exists=0; //assume previous session does not exist
       $project_event_capability_array=array();
       $request = $this->getRequest();
       $session = $request->getSession();
       $user_id=$this->container->get('security.context')->getToken()->getUser()->getId();
        
       //check if previous session exists
       if (($session->has("ProjectRoleEventUserSession")) && ($session->get("ProjectRoleEventUserSession") !=""))   { 
           
           $session_project_event_capability_array = $session->get("ProjectRoleEventUserSession"); 
           
           if (isset(array_keys($session_project_event_capability_array)[0])) {
                $first_key=array_keys($session_project_event_capability_array)[0]; 
                 //echo "first key is ".$first_key."<br>";
                 if (isset(array_keys($session_project_event_capability_array[$first_key])[0])) {
                    $second_key=array_keys($session_project_event_capability_array[$first_key])[0];
                    //echo "second key is ".$second_key."<br>";
                    if ($session_project_event_capability_array[$first_key][$second_key][0]['user_id']==$user_id) {
                        $project_event_capability_array = $session->get("ProjectRoleEventUserSession"); 
                        $session_exists=1;
                       // echo "user id is ".$session_project_event_capability_array[$first_key][$second_key][0]['user_id']."<br>";    
                    }
                 }
           }
       }  
         
               
       if ($session_exists==0)  {  
           //echo "session DOES NOT exist";
           $role_admin_id=$this->isRoleAdmin();
           if ($role_admin_id !="") { //Admins have access to all projects    
                $sql="SELECT ".$user_id." user_id,e.id event_id,
                     e.name,e.start_date,e.end_date, 11 role_id 
                     from event e";
           } else {
                
                $sql = "SELECT eu.user_id,e.id event_id,
                        e.name,e.start_date,e.end_date, eu.role_id role_id
                        FROM event e
                        JOIN event_user eu on e.id=eu.event_id
                        WHERE eu.user_id=".$user_id;
           }
           
           $dbh= $this->get('database_connection');
           $stmt = $dbh->query($sql); 
           $capability_array = $stmt->fetchAll(\PDO::FETCH_ASSOC);
           
           $capability_array_size=sizeof($capability_array);
           
           for($c=0;$c<$capability_array_size;$c++){        
                $project_event_capability_array_original[$capability_array[$c]["event_id"]][]=$capability_array[$c];      
           }    
           
           //now loop through project_capability_array to build project_event_array
           $project_capability_array = $this->getUserProjectRoleCapabilities();
      
            foreach ($project_capability_array as $p) {
                $new_project_capability_array=$p;
                $project_capability_array_size = sizeof($new_project_capability_array);
                $project_id = $p[0]['project_id'];
                $role_id=$p[0]['role_id'];
               
                for($p=0;$p<$project_capability_array_size;$p++){
                    if (($new_project_capability_array[$p]['object']=="EventScoringItem") || ($new_project_capability_array[$p]['object']=="ScoringItem")) {
                        foreach($project_event_capability_array_original as $pe) {
                           $event_id=$pe[0]['event_id'];
                           $event_start_date=$pe[0]['start_date'];
                           $event_end_date=$pe[0]['end_date'];
                           $event_role_id=$pe[0]['role_id'];
                               if ($this->checkAccessTimeframe("projectsite", null, $role_id, $new_project_capability_array[$p]["action_name"], $project_id, 'Event', null,$event_start_date,$event_end_date)) {                                
                                    $new_project_capability_array[$p]['event_id']= $event_id;
                                    $new_project_capability_array[$p]['event_start_date']= $event_start_date;
                                    $new_project_capability_array[$p]['event_end_date']= $event_end_date;
                                    $new_project_capability_array[$p]['event_role_id']= $event_role_id;
                                    $project_event_capability_array[$project_id][$event_id][]=$new_project_capability_array[$p];   
                              }
                        }    
                    } 
                }
             } 
             //var_dump($project_event_capability_array);
           
           $session->set("ProjectRoleEventUserSession",  $project_event_capability_array);
                  
       }
       
        
        return $project_event_capability_array;
    }
    
     public function getCurrentEvent() {
        $events_with_access=array();
        
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $multiple =  $request->get("multiple");
          
         if ($session->has("CurrentEventUserSession") && ($session->get("CurrentEventUserSession") !="") && ($multiple !=1)) {  
             return $session->get("CurrentEventUserSession");
               
         } else {
             $today= date('Y-m-d H:i:s');
             
             $sql="SELECT DISTINCT e.id event_id
                   FROM event e
                   LEFT JOIN event_user eu ON eu.event_id=e.id
                   LEFT JOIN role r ON eu.role_id = r.id
                   LEFT JOIN structure s ON s.id=r.structure_id
                   WHERE s.name='Event' 
                   AND e.end_date >='$today'
                   AND eu.user_id= ".$this->container->get('security.context')->getToken()->getUser()->getId()."
                   AND ((e.start_date <= '$today' AND e.end_date >= '$today' AND r.name!='Event Leader' OR r.name!='Room Leader')
                   OR (e.end_date >= '$today' AND (r.name='Event Leader' OR r.name='Room Leader')))";
             
             $dbh= $this->get('database_connection');
             $stmt = $dbh->query($sql); 
             $events_array = $stmt->fetchAll(\PDO::FETCH_ASSOC);
             $events_array_size=sizeof($events_array);
             
             $Ids="";
             for($e=0;$e<$events_array_size;$e++){  
                $Ids .=$events_array[$e]["event_id"].",";
             }
               
             $Ids = substr($Ids, 0, -1); //strip last comma
            
             $role_admin_id=$this->isRoleAdmin();
             if ($role_admin_id !="") { //Admins have access to all current and future events that they don't already have a role in the previous query
                $sql_admin="SELECT DISTINCT e.id event_id
                            FROM event e 
                            WHERE e.end_date >='$today'";

                if ($Ids !="") {
                    $sql_admin.=" AND e.id NOT IN (".$Ids.")";
                }
                
                $stmt = $dbh->query($sql_admin);
                $events_admin_array = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $events_admin_array_size=sizeof($events_admin_array);
                
                if ($events_admin_array_size >0) {
                    if ($Ids !="") {
                       $Ids .=","; 
                    }
                    for($ea=0;$ea<$events_admin_array_size;$ea++){  
                        $Ids .=$events_admin_array[$ea]["event_id"].",";
                    }
                    $Ids = substr($Ids, 0, -1); //strip last comma
                }
               
             } //end of Admin processing
           
             $entity_ids = explode(",",$Ids);
             foreach ($entity_ids as $e) { 
                if ($this->checkAccessTimeframe("eventsite", null, null, "show", null, "Event", $e,null,null)) {
                   $events_with_access[]=$e;
                } 
             }  
             
            
             $session->set('EventCountUserSession',count($events_with_access));
             if (count($events_with_access) == 1) {
                $session->set('CurrentEventUserSession',$events_with_access[0]);
                return $session->get("CurrentEventUserSession");  
             } else {
                $session->remove('CurrentEventUserSession');
                $session->remove('EventRoleUserSession');
                $session->set('EventsWithAccessUserSession',$events_with_access);
                return $this->redirect($this->generateUrl('eventsite_event'));       
              }
              
        }

     }
    
    public function getUserEventRoleCapabilities() {
       $event_capability_array=array();
       $request = $this->getRequest();
       $session = $request->getSession();
       $user_id=$this->container->get('security.context')->getToken()->getUser()->getId();
       
       
       #08/08/13 added logic to match on user id, to make sure it's the same user as in previous session
       if (($session->has("EventRoleUserSession") && ($session->get("EventRoleUserSession") !=""))
               && ($session->has('CurrentEventUserSession') && ($session->get('CurrentEventUserSession') !=""))
               && (isset ($session->get("EventRoleUserSession")[$session->get('CurrentEventUserSession')][0]['user_id']))
               && ($session->get("EventRoleUserSession")[$session->get('CurrentEventUserSession')][0]['user_id']==$user_id)) {
                    
           $event_capability_array = $session->get("EventRoleUserSession");
     
       } else {
           $today= date('Y-m-d H:i:s');
           
           $sql = "SELECT eu.id, eu.user_id, eu.event_id,eu.role_id, r.name role_name, r.display_name AS role_display_name,
                   g.id grade_level_id, g.name grade_level_name,eu.table_id,s.id structure_id, e.event_type_id,
                   e.start_date, e.end_date,
                   se.id object_id, se.name object, a.id action_id, a.name action_name 
                   FROM role_capability rc
                   LEFT JOIN role r ON rc.role_id = r.id
                   LEFT JOIN system_entity se ON rc.entity_id = se.id
                   LEFT JOIN system_action a ON rc.action_id = a.id
                   LEFT JOIN event_user eu ON eu.role_id = r.id AND eu.role_id = rc.role_id
                   LEFT JOIN structure s ON s.id=rc.structure_id
                   LEFT JOIN event e ON e.id=eu.event_id
                   LEFT JOIN grade_level g ON eu.grade_level_id=g.id
                   WHERE s.name='Event' ".
                   #AND e.start_date <='$today' 
                   " AND e.end_date >='$today'
                   AND eu.user_id= ".$user_id." ORDER by role_id DESC";
           
           $dbh= $this->get('database_connection');
           $stmt = $dbh->query($sql); 
           $capability_array = $stmt->fetchAll(\PDO::FETCH_ASSOC);
           $capability_array_size=sizeof($capability_array);
           for($c=0;$c<$capability_array_size;$c++){  
                $event_capability_array[$capability_array[$c]["event_id"]][]=$capability_array[$c];
           }
            
           $role_admin_id=$this->isRoleAdmin();
           if ($role_admin_id !="") { //Admins have access to all current and future events that they don't already have a role in the previous query
              
               $Ids="";
               foreach ($event_capability_array as $key => $value) {
                   $Ids .=$key.",";
               }
               $Ids = substr($Ids, 0, -1); //strip last comma
                
               $sql_admin="SELECT ".$user_id.", e.id event_id,rc.role_id, r.name role_name, r.display_name AS role_display_name,
                   NULL grade_level_id, NULL grade_level_name,NULL table_id,s.id structure_id,
                   e.start_date, e.end_date, e.event_type_id,
                   se.id object_id, se.name object, a.id action_id, a.name action_name 
                   FROM role_capability rc
                   LEFT JOIN role r ON rc.role_id = r.id
                   LEFT JOIN system_entity se ON rc.entity_id = se.id
                   LEFT JOIN system_action a ON rc.action_id = a.id
                   LEFT JOIN structure s ON s.id=rc.structure_id
                   CROSS JOIN event e
                   WHERE s.name='Event' AND rc.role_id=".$role_admin_id."
                   AND e.end_date >='$today'";
               if ($Ids !="") {
                    $sql_admin.=" AND e.id NOT IN (".$Ids.")";
               }
               $stmt = $dbh->query($sql_admin); 
               $capability_array = $stmt->fetchAll(\PDO::FETCH_ASSOC);
               $capability_array_size=sizeof($capability_array);
                for($c=0;$c<$capability_array_size;$c++){  
                    $event_capability_array[$capability_array[$c]["event_id"]][]=$capability_array[$c];
                }
               
           } 
           
         $session->set("EventRoleUserSession",  $event_capability_array);
       } 
       
        if (count($event_capability_array) ==1) { //if they have access to only one curent event, we set it as CurrentEvent
            foreach ($event_capability_array as $key => $value) {
                 $session->set('CurrentEventUserSession',$key);
            }
        }
       
        return $event_capability_array;
    }
    
    public function checkAccessTimeframe($site, $component_id=null,$role_id=null, $action, $id=null,$object=null,$current_event_id=null,$start_date=null,$end_date=null) {
        
        $access=false; 
        
        $today= date('Y-m-d H:i:s');
        
        //get System Roles
         $system_roles_array = $this->getSystemRoles();
       
        if ($site == 'projectsite') {
            
            $role_pi_id=$system_roles_array['Principal Investigator'];
            $role_co_pi_id=$system_roles_array['Co-Principal Investigator'];
            $role_delegate_id=$system_roles_array['Delegate'];
            $role_project_staff_id=$system_roles_array['Project Staff'];
            $role_scoring_conf_coordinator_id = $system_roles_array['Scoring Conference Coordinator']; 
            $role_admin_id=$system_roles_array['Admin'];
            
            if ($id != null) { //Project Id (or Event ID if checking event access in project site) is not null
                
                if (($object=="Event")) {
                     if (($action=="create") || ($action=="edit") || ($action=="create multiple")) {
                        if ($role_id==$role_admin_id) {
                               $access=true;
                        } elseif (($role_id!=$role_admin_id)  && ($end_date >=$today)) {
                               $access=true;
                        }  
                     } elseif(($action=="delete") || ($action=="unassign")) {
                         if ($role_id==$role_admin_id) {
                               $access=true;
                         } elseif (($role_id!=$role_admin_id)  && ($start_date >=$today)) { //allow non-admins to delete only if event hasn't started yet
                               $access=true;
                        }  
                     } elseif(($action=="list") || ($action=="show") || ($action=="download")) {
                           $access=true;
                     }
                } else {
                    if (($action=="create") || ($action=="edit") || ($action=="delete")  || ($action=="unassign") || ($action=="create multiple")) {
                        if ($role_id==$role_admin_id) {
                               $access=true;
                        } elseif (($role_id!=$role_admin_id)  && ($end_date >=$today)) {
                               $access=true;
                        } 

                    } elseif(($action=="list") || ($action=="show") || ($action=="download")) {
                           $access=true;
                    }
                }
            }
            
        } else { //Event Site
  
            //If user is an Admin, Event Leader or Room Leader, they can "list" and "show" any current or future events
            //If user is any other role, they can "list" and show" only current events
            //All roles, except Admins, can "create" and "edit" current events only, NOT future events
            
            $role_event_leader_id=$system_roles_array['Event Leader'];
            $role_room_leader_id=$system_roles_array['Room Leader'];
            $role_table_leader_id=$system_roles_array['Table Leader'];
            $role_scorer1_id=$system_roles_array['Scorer 1'];
            $role_scorer2_id = $system_roles_array['Scorer 2']; 
            $role_admin_id=$system_roles_array['Admin']; 

            $event_capability_array = $this->getUserEventRoleCapabilities();   

            if ($role_id==null) { //get user role id in case it was not passed into the function     
               $role_id = $event_capability_array[$current_event_id][0]['role_id'];
            }

            if ($component_id==null) { //get user role id in case it was not passed into the function     
               $component_id = 1;
            }

            if ($current_event_id != null) {
                $current_event_start_date = $event_capability_array[$current_event_id][0]['start_date'];
                $current_event_end_date = $event_capability_array[$current_event_id][0]['end_date'];

                if (($action=="create") || ($action=="edit")) {
                   if ($component_id==1) {
                       if (($current_event_end_date >=$today) && ($current_event_start_date <=$today) && ($role_id!=$role_admin_id) ) {
                           $access=true;
                       }
                   } else { //component_id =2
                       if (($current_event_end_date >=$today) && ($current_event_start_date <=$today) && (($role_id==$role_admin_id) || ($role_id==$role_scorer1_id) || ($role_id==$role_scorer2_id)) ) {
                           $access=true;
                       } elseif (($role_id==$role_admin_id) && ($current_event_end_date >=$today)) {
                           $access=true;
                       }
                   }
                } elseif(($action=="list") || ($action=="show") || ($action=="download")) {
                       if ((($role_id==$role_event_leader_id) || ($role_id==$role_room_leader_id) || ($role_id==$role_admin_id)) && ($current_event_end_date >=$today)) {
                           $access=true;
                       } elseif (($role_id!=$role_event_leader_id) && ($role_id!=$role_room_leader_id)  && ($role_id!=$role_admin_id) && ($current_event_end_date >=$today) && ($current_event_start_date <=$today)) {
                           $access=true;
                       } 
                 }
              }
          }     
                      
           return $access;
    }
    
    public function checkAccess($action, $id=null,$object=null) {
        
        $access=false;
        $site="";
        $url =  $this->getRequest()->getPathInfo();
       
        if (strpos($url, "/projectsite")!== false) {  //The url of the page designates that it's the Project Site
            
            if ($object=="EventScoringItem") { //checks project id and event id
                $project_event_capability_array = $this->getUserProjectRoleEventCapabilities();
                if (($action == "create") || ($action == "create multiple") || ($action == "list")){
                    foreach ($project_event_capability_array as $pe) {
                        foreach ($pe as $e) {             
                            $new_project_event_capability_array=$e;
                            $project_event_capability_array_size = sizeof($new_project_event_capability_array);
                            for($n=0;$n<$project_event_capability_array_size;$n++){  
                                if (($new_project_event_capability_array[$n]['action_name']==$action) && ($new_project_event_capability_array[$n]['object']==$object)) {
                                    $access=true;
                                    break;
                                }
                            }  
                        }       
                    }
                } elseif (($action == "edit") ||($action == "delete") ||($action == "unassign") ||($action == "show")) {
                   if ($id==null) {
                        $id=$this->getRequest()->attributes->get('id');
                    }  
                     $id = array_unique(explode(';', $id)); //if it's a batch action, only check unique project ids
                     foreach ($id as $item) { //loop through ids in case it's a batch action
                       $combo=explode(',', $item);
                       $project_id = $combo[0];
                       $event_id=$combo[1];
                       
                       if  (isset ($project_event_capability_array[$project_id][$event_id])) {
                            $new_project_event_capability_array=$project_event_capability_array[$project_id][$event_id];
                            $project_event_capability_array_size = sizeof($new_project_event_capability_array);
                            for($p=0;$p<$project_event_capability_array_size;$p++){  
                                if (($new_project_event_capability_array[$p]['action_name']==$action) && ($new_project_event_capability_array[$p]['object']==$object)) {
                                    $access=true;
                                    break;
                                }
                            } 
                            if ($access!=true) { //it's gone through one combo and not found it, no reason to go on to then next
                                break;
                            }
                        } 
                     }
                    
                }
                
            } else { //check only based on project id
                $project_capability_array = $this->getUserProjectRoleCapabilities();

               if (($action == "create") || ($action == "create multiple") || ($action == "list")){  //we don't know which project they will select, so check that they have rights for the object they are trying to create or list, project list will be limited in Project dropdown list

                   foreach ($project_capability_array as $p) {
                        foreach ($p as $i) {
                            if (($i['action_name']==$action) && ($i['object']==$object)) {
                               $access=true;
                                break; 
                            }
                        }
                        if ($access==true) {//has access at least to one project, no need to loop through others
                            break;
                        }
                    } 
                } else if (($action == "edit") ||($action == "delete") ||($action == "unassign") ||($action == "show") ||($action == "download")) {
                    if ($id==null) {
                        $id=$this->getRequest()->attributes->get('id');
                    } 

                   $id = array_unique(explode(',', $id)); //if it's a batch action, only check unique project ids

                   foreach ($id as $item) { //loop through ids in case it's a batch action
                    if  (isset ($project_capability_array[$item])) {
                        $new_project_capability_array=$project_capability_array[$item];
                        $project_capability_array_size = sizeof($new_project_capability_array);
                        for($p=0;$p<$project_capability_array_size;$p++){  
                            if (($new_project_capability_array[$p]['action_name']==$action) && ($new_project_capability_array[$p]['object']==$object)) {
                                $access=true;
                                break;
                            }
                        } 
                        if ($access!=true) { //it's gone through one project and not found it, no reason to go on to then next
                                break;
                        }
                    } 
                   }

                }
            }
        } elseif (strpos($url, "/eventsite")!== false) { //Event Site
             $event_capability_array = $this->getUserEventRoleCapabilities();
           
             if ($action == "list"){  //check that they have access to at least one current or future event
      
                 foreach ($event_capability_array as $key =>$e) { 
                     if ($this->checkAccessTimeframe("eventsite", null,null, $action, null, $object, $key,null,null)) {
                         foreach ($e as $i) {
                            if (($i['action_name']==$action) && ($i['object']==$object) && ($i['structure_id']== 2)) {
                                $access=true;
                                 break; 
                            }
                         }
                     } 
                  } 
               
            } else if (($action == "show") || ($action == "download")) {
               
                if ($id==null) {
                    $id=$this->getRequest()->attributes->get('id');
                } 
                
          
                if ($this->checkAccessTimeframe("eventsite", null,null, $action, null, $object, $id,null,null)) {
                  
                    if  (isset ($event_capability_array[$id])) {
                       foreach ($event_capability_array[$id] as $e) { //check whether user has access to current event and event roles
                           if (($e['action_name']==$action) && ($e['object']==$object) && ($e['structure_id']== 2)) {
                                $access=true;
                                break;    
                           }

                        }
                    } 
                }
                
            }
             
        } else { //Common urls for both sites (Project and Event)
           if ($action == "download") {
               $projects = $this->getUserProjects(null,"ScoringItem", "download"); //
               foreach($projects as $project) {
                    if ($id==$project->getId()) {
                        $access=true;
                        break;
                    }
                }
                if ($access==false) {
                    //getUserEvents, it may be an event role trying to access the papers
                }
           }
        }
        
        return $access;
    }
    
    public function getUserStatusPathways($role_id) {
       $status_pathway_capability_array=array();
       $request = $this->getRequest();
       $session = $request->getSession();
       if ($session->has("StatusPathwaysUserSession")) {
               $status_pathway_capability_array = $session->get("StatusPathwaysUserSession");
       } else  {
           $sql = "SELECT p.status_id,p.pathway_id,p.component_id FROM scoring_item_status s
                   JOIN scoring_item_status_pathway p ON p.status_id=s.id
                   JOIN scoring_item_status_role_capability rc ON rc.status_id=p.pathway_id AND rc.component_id=p.component_id
                   JOIN role r ON r.id = p.role_id AND r.id=rc.role_id
                   JOIN system_action sa ON sa.id=rc.action_id
                   AND r.id=$role_id AND sa.name='create'";
           
           $dbh= $this->get('database_connection');
           $stmt = $dbh->query($sql); 
           $capability_array = $stmt->fetchAll(\PDO::FETCH_ASSOC);
           
           $capability_array_size=sizeof($capability_array);
           for($c=0;$c<$capability_array_size;$c++){  
                $status_pathway_capability_array[$capability_array[$c]["status_id"]][]=$capability_array[$c];
            }     
                    
            
         $session->set("StatusPathwaysUserSession",  $status_pathway_capability_array);
       }
       
        return $status_pathway_capability_array;
    }
    
    public function getUserStatusListQueue($role_id) {
     
       $request = $this->getRequest();
       $session = $request->getSession();
   
       if ($session->has("StatusListQueueUserSession")) {
            $status_list_queue_capability = $session->get("StatusListQueueUserSession");
       } else  {
            $sql = "SELECT rc.*,sa.name 'action_name' FROM scoring_item_status s
                   JOIN scoring_item_status_role_capability rc
                   ON rc.status_id=s.id
                   JOIN role r ON r.id=rc.role_id
                   JOIN system_action sa ON sa.id=rc.action_id
                   WHERE r.id=$role_id AND (sa.name = 'list' OR sa.name = 'edit' OR sa.name='show') order by component_id";
          
            $dbh= $this->get('database_connection');
            $stmt = $dbh->query($sql); 
            $capability_array = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $capability_array_size=sizeof($capability_array);
            for($c=0;$c<$capability_array_size;$c++){  
                $component_id=$capability_array[$c]["component_id"];
                $status_id=$capability_array[$c]["status_id"];
                if ($capability_array[$c]["subrole_id"]=="") {
                    $subrole_id=0;
                } else {
                    $subrole_id=$capability_array[$c]["subrole_id"];
                }
                if ($capability_array[$c]["structure_id"]=="") {
                    $structure_id=0;
                } else {
                    $structure_id=$capability_array[$c]["structure_id"];
                }
                $combo_id=$component_id."_".$status_id."_".$subrole_id."_".$structure_id;
                $status_list_queue_capability[$combo_id][]=$capability_array[$c];
            }
            
            $session->set("StatusListQueueUserSession",  $status_list_queue_capability);
        }
        
        return $status_list_queue_capability;
    }
    
    public function checkStatusAccess($component_id,$role_id, $action, $id=null,$object=null,$start_status=null,$end_status=null, $current_event_id=null, $check_status_exists=false) {
             $access=false;
             
             if ($this->checkAccessTimeframe("eventsite", $component_id, null, $action, null, $object, $current_event_id,null,null)) {
                if ($action == "list"){  //check that they have access at least to one current event, they will need to select the current event if there's more than one
                    $status_capability_array = $this->getUserStatusListQueue($role_id);
                    
                    foreach ($status_capability_array as $s) {
                       foreach ($s as $i) {
                           if (($i['action_name']==$action) && ($i['component_id']==$component_id)) {
                              $access=true;
                               break; 
                           }
                       }
                       if ($access==true) {//has access at least to one status, no need to loop through others
                           break;
                       }
                   } 

               } else if (($action == "show") || ($action == "edit")) {
                   $status_capability_array = $this->getUserStatusListQueue($role_id);
                   if ($id==null) {
                       $id=$this->getRequest()->attributes->get('id');
                   } 

                   if  (isset ($status_capability_array[$id])) {
                      foreach ($status_capability_array[$id] as $s) { //check whether user has access to current event and event roles
                          if ($action=="edit") {
                               if ($s['action_name']==$action) {
                                   $access=true;
                                   break;    
                               }
                          } else if ($action=="show") { 
                              if ($object !="EventScoringItemStatus") { //if user has edit access, the user can also view the item
                                if (($s['action_name']==$action) || ($s['action_name']=="edit")) {
                                     $access=true;
                                     break;    
                                 }
                              } else {
                                  if ($s['action_name']==$action)  { //edit access does not give user "show" access
                                     $access=true;
                                     break;    
                                 }
                              }
                          }

                       }
                   } 


                } else if ($action=="create") {
                   
                   if ($check_status_exists==true) {
                       $status_exists_count = $this->getStatusExists($end_status,$current_event_id);
                   }

                   if (($check_status_exists==false) || ($status_exists_count==0)) {
                       if (($start_status !="") && ($end_status !="")) {
                           $status_capability_array = $this->getUserStatusPathways($role_id);
                           
                           if (isset ($status_capability_array[$start_status])) {
                               foreach ($status_capability_array[$start_status] as $usp) {
                                   if (($usp['pathway_id']==$end_status) && ($usp['component_id']==$component_id)) {
                                       $access=true;
                                       break;
                                   }    
                               }
                           }
                       }
                   }

                 }
             } //end of event timeframe 
             return $access;
    }
    
     public function getStatusExists($status,$current_event_id){
        $em = $this->getDoctrine()->getManager(); 
        
        $queryBuilder = $em->getRepository('NwpAssessmentBundle:EventScoringItemStatus')
                            ->createQueryBuilder('esu')
                            ->select('esu')
                            ->leftJoin('esu.eventScoringItem', 'esi')
                            ->where('esu.assignedTo='.$this->container->get('security.context')->getToken()->getUser()->getId())  
                            ->andWhere('esi.maxEventScoringItemStatus=esu.id')
                            ->andWhere('esu.status = '.$status)
                            ->andWhere('esi.event='.$current_event_id)
                ;
      
        $query = $queryBuilder->getQuery();
        $status_exists=$query->getResult();
        
        return count($status_exists);
     }
     
    public function getStatusCombo($user_role_id,$role_event_leader_id,$role_room_leader_id,$role_table_leader_id,$role_admin_id,$role_scorer1_id,$role_scorer2_id,$action,$structure,$previous_entity){
        
        //get combo id
        $subrole="";
        $space="";  
        $subrole_user_id="";
        
        if ((($user_role_id==$role_scorer1_id)||($user_role_id==$role_scorer2_id)) && ($action=="show")) {
            $subrole=3; //For Scorer 1 and Scorer 2 Recent Paper view, use whom item was originally assigned to (status_assigned_assigned_to)   
            $subrole_user_id=$previous_entity->getStatusAssignedAssignedTo();
        } elseif ($previous_entity->getAssignedTo() !="") {
            $subrole=2; //For all other cases, look at whom the item is currently assigned_to 
            $subrole_user_id = $previous_entity->getAssignedTo();
        } else {  
            $subrole=1; //if current assigned_to values is empty, look at created_by   
        }
        
        if (($subrole_user_id !="") && ($subrole_user_id ==$this->container->get('security.context')->getToken()->getUser())) {
            $space=5; //individual
        } else {
            $space=$structure;
        }   
        
        $combo_id =$previous_entity->getComponent()->getId()."_".$previous_entity->getStatus()->getId()."_".$subrole."_".$space;
        
        return $combo_id;      
    }
    
    public function getStatusStructure($user_role_id,$role_event_leader_id,$role_room_leader_id,$role_table_leader_id,$role_admin_id) {
        
        $structure="";
    
        if (($user_role_id==$role_event_leader_id) || ($user_role_id==$role_admin_id)) {
            $structure=2; //event
        } elseif ($user_role_id==$role_room_leader_id) {
            $structure=3; //room
        } elseif ($user_role_id==$role_table_leader_id) {
            $structure=4; //table
        } else {
            $structure=5; //individual
        }
        
        return $structure;
    }
    
   public function getSystemRoles() {
       $system_roles_array=array();
       
       $request = $this->getRequest();
       $session = $request->getSession();
       
       if ($session->has("SystemRolesUserSession")) {
               $system_roles_array = $session->get("SystemRolesUserSession");
       } else  {
           
           $em = $this->getDoctrine()->getManager();
           $queryBuilder = $em->getRepository('NwpAssessmentBundle:Role')
                            ->createQueryBuilder('r')
                            ->select('r')
                            ->orderBy('r.id', 'ASC');
                            
            $query = $queryBuilder->getQuery();
            $values= $query->getResult();
            
            foreach($values as $v) {
                 $system_roles_array[$v->getName()]=$v->getId();
            }
           
           $session->set("SystemRolesUserSession",  $system_roles_array);
       }
        
       return $system_roles_array;
    }
    
    
    public function isRoleAdmin() {
       $role_admin_id="";
       $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('ROLE_ADMIN')) {
           $system_roles_array = $this->getSystemRoles();
           $role_admin_id=$system_roles_array['Admin']; 
        } 
        return $role_admin_id;
    }
    
    
    public function downloadFile($folder, $mimeType, $filename) {
       
        $headers = array(header('X-Sendfile: '.$folder),
                                'Content-Type'     => 'application/'.$mimeType,
                                'Content-Disposition' => 'inline; filename="'.$filename."'");
     
        if (!$folder) {
            $response = false;
        } else {     
            $response =  new Response(file_get_contents($folder), 200, $headers); 
        }
        
        return $response;
    }
    
    public function getTableLeader($user_id,$current_event_id,$role_table_leader_id) {
        $table_leader="";
        
        if ($user_id !="") {
           $em = $this->getDoctrine()->getManager();
           
           $queryBuilder =$em->getRepository('NwpAssessmentBundle:EventUser')
                          ->createQueryBuilder('eu')
                          ->select('eu2')
                          ->leftJoin('NwpAssessmentBundle:EventUser','eu2',
                                                 \Doctrine\ORM\Query\Expr\Join::WITH,
                                                'eu.event=eu2.event and eu.gradeLevel=eu2.gradeLevel and eu.tableId=eu2.tableId')
                          ->Where('eu.user='.$user_id)
                          ->andWhere('eu.event='.$current_event_id)
                          ->andWhere('eu2.role='.$role_table_leader_id)
                          ; 
           
           $query = $queryBuilder->getQuery();
           $table_leader= $query->getResult();
        }
        
        return $table_leader;
    }
    
    
    public function errorCheckCsv($check,$val,$key,$column_array,$error_type_array,$project_id=null,$row=null,$new_data=null,$data=null) {
        $error_msg_spec ="";
       
        if ($check !="") {
            $valid=true;
            
            switch ($check)
            {   
                case 0://required
                if (($val==null) ||($val=="")) {
                    $valid=false;
                } 
                break;
                case 1://numeric
                if ((($val!=null) && ($val!="")) && (!is_numeric($val))) {
                    $valid=false;
                }
                break;
                case 2:
                if (isset($column_array[$key]["maxlength"])) {
                    if ((($val!=null) && ($val!="")) && (strlen($val)>$column_array[$key]["maxlength"])) {
                        $valid=false;
                    }
                }
                break;
                case 3:
                if (isset($column_array[$key]["length"])) {
                    if ((($val!=null) && ($val!="")) && (strlen($val)!=$column_array[$key]["length"])) {
                        $valid=false;
                    }
                }
                break;
                case 4:
                if (isset($column_array[$key]["search_array"]) && ($column_array[$key]["search_array_size"]>0)) {             
                    if (($val!=null) && ($val!="")) {
                        $found=false;
      
                        for($s=0;$s<$column_array[$key]["search_array_size"];$s++){    
                            if ($column_array[$key]["column_name"]=="county_id") { 
                               
                                if (isset ($new_data[$row-1][13])) { //if state was not empty
                                    
                                    if ((strpos($column_array[$key]["search_array"][$s]['name'], $val)!== false) && ($column_array[$key]["search_array"][$s]['state']==$new_data[$row-1][13])) {
                                        $found=true; 
                                    } 
                                }
                            } else if ($column_array[$key]["column_name"]=="prompt_id") {
                                 if ($project_id==0) { //admins get project id from excel file, otherwise it is sent into this funciton for non-admins  
                                    if (isset ($new_data[$row-1][5]) ) {
                                        $project_id=$new_data[$row-1][5];
                                    } else {
                                        $project_id="";
                                    }   
                                  }
                                if (($project_id !="") && ($project_id !=0)) { //if project was not empty
                                   // echo "Comparing: ".strpos($column_array[$key]["search_array"][$s]['name'], $val).",".$column_array[$key]["search_array"][$s]['project']."<br>";
                                    if ((strpos($column_array[$key]["search_array"][$s]['name'], $val)!== false) && ($column_array[$key]["search_array"][$s]['project']==$project_id)) {
                                        $found=true;   
                                    } 
                                }
                            } else if (($column_array[$key]["column_name"]=="id") || ($column_array[$key]["column_name"]=="scoring_item_type_id")) {
                                if ($column_array[$key]["search_array"][$s]['id']==$val) {//user does not have the right to update this paper id (user does not have access to original project this paper belongs to) 
                                    $found=true; 
                                    break;
                                }
                            } else {
                                if ($column_array[$key]["search_array"][$s]['name']==$val) {  
                                    $found=true; 
                                }
                            }
                                                   
                            if ($found==true) {
                                $val=$column_array[$key]["search_array"][$s]['id'];
                                $new_data[$row-1][$column_array[$key]["column_id"]-1]=$val; //keep track of id associated with written in 
                                break;
                            }
                         }
                                               
                         if ($found==false) {
                            $valid=false;
                         }
                        }
                    } else { //array we are searching against is not set or has 0 items, for example user might not have any projects assigned to them
                        $valid=false;
                    }
                    break;
                    case 5: //required dependent
                    if (isset($column_array[$key]["dependent_column_id"])) {
                        $dependent_val = $data[$row-1][$column_array[$key]["dependent_column_id"]+1];
                        if (($dependent_val!=null) && ($dependent_val!="")) {
                            if ((($val==null) || ($val==""))) {
                                $valid=false;
                            }
                        }
                    }
                    break;
                    case 6://valid string
                    
                    if ((($val!=null) && ($val!="")) && (!$this->validateFilename($val))) {
                        $valid=false;
                    }
                    break;
                    case 7://valid date format
                    
                    if ((($val!=null) && ($val!="")) && (!$this->validateDate($val))) {
                        $valid=false;
                    }
                    break;
                    case 8://date greater than today
                    
                    if ((($val!=null) && ($val!="") && ($this->validateDate($val)))&& (!$this->compareDate($val))) {
                        $valid=false;
                    }
                    break;
                    default: 
               }
               
               if ($valid==false) {
                    if ($error_type_array[$check]["error_id"]=="2") {
                        $error_msg_spec = $column_array[$key]["maxlength"];
                    } else if ($error_type_array[$check]["error_id"]=="3") {
                        $error_msg_spec = $column_array[$key]["length"];
                    } else if (($error_type_array[$check]["error_id"]=="4") && ($column_array[$key]["column_name"]=="county_id")) {
                        $error_msg_spec = " within the specified state";
                    } else if (($error_type_array[$check]["error_id"]=="4") && ($column_array[$key]["column_name"]=="prompt_id")) {
                        
                        $error_msg_spec = " within the specified project";
                    } else if ($error_type_array[$check]["error_id"]=="5") {
                        $error_msg_spec = "if ".$column_array[$column_array[$key]["dependent_column_id"]+1]["column_desc"]." is specified";
                    } else {
                        $error_msg_spec ="";
                    }
                    
                }                                     
         }
     return array ($valid,$val,$error_msg_spec,$new_data);
  }
  
  public function validateFilename($filename) {
      if(preg_match('/^[a-zA-Z0-9-_]+$/',$filename)) { //alphanumeric, "-", and "_" are accepted in filenames
        //$file is valid
          return true;
      } else {
          return false;
      }
  }
  
  function validateDate($dateString) {
    //date has to be in YYYY-mm-dd format, ex: '2017-05-14';
    if(preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $dateString, $matches)) {
        if (checkdate($matches[2], $matches[3], $matches[1])) { 
            $date_to_remove = date('Y-m-d', strtotime($dateString));
            if ($date_to_remove) { //make sure we can convert it to strotime
                return true;
            }
        }
    }
    
  }
 
 function compareDate($dateString) {
     $today = strtotime(date('Y-m-d'));
     
     if(strtotime($dateString) > $today){
         return true;
     }
 }
 
 function checkBlockQuotaAccess ($event_user_id, $user_role_id,$user_block_capability_array,$role_scorer1_id,$role_scorer2_id) {
    //Rule 2 = block quota reached check
    $block_quota_reached_access=true;
     
    if (($user_role_id ==$role_scorer1_id) || ($user_role_id ==$role_scorer2_id)) {
        if (isset($user_block_capability_array[$event_user_id]) ) { 
            if ($user_block_capability_array[$event_user_id][0]['block_quota_reached'] ==1)  {
                //block quota reached message, disable New Paper button 
                $block_quota_reached_access=false;
            }                
        }
    }
    return $block_quota_reached_access;
  }
  
 function checkUserBlockQuotaAccess($event_user_id, $user_role_id,$user_block_capability_array,$role_scorer1_id,$role_scorer2_id) {
    //Rule 3 = user block quota reached check
    $user_block_quota_reached_access=true;
        
    if (($user_role_id ==$role_scorer1_id) || ($user_role_id ==$role_scorer2_id)) {
        if (isset($user_block_capability_array[$event_user_id]) ) { 
            if ($user_block_capability_array[$event_user_id][0]['user_block_quota_reached'] ==1) {
                //user block quota reached message, disable New Paper button 
                $user_block_quota_reached_access=false;
            }
        }
    }
    return $user_block_quota_reached_access;
 }
    
}


