<?php

namespace Nwp\AssessmentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrapView;

use Pagerfanta\Adapter\ArrayAdapter;

use Nwp\AssessmentBundle\Entity\EventScoringItemStatus;
use Nwp\AssessmentBundle\Form\EventScoringItemStatusType;
use Nwp\AssessmentBundle\Form\EventScoringItemStatusFilterType;

use Nwp\AssessmentBundle\Entity\ScoringItemScore;
use Nwp\AssessmentBundle\Form\EventUserType;

/**
 * EventScoringItemStatus controller.
 *
 * @Route("/eventsite")
 */
class EventScoringItemStatusController extends BaseController
{
    /**
     * Lists all EventScoringItemStatus entities.
     *
     * @Route("/eventscoringitemstatus", name="eventsite_eventscoringitemstatus")
     * @Route("/reporting", name="eventsite_reporting")
     * @Template()
     */
    public function indexAction()
    {
        $current_event_id = $this->getCurrentEvent();
        
        $component_id=1;
        
        if ($current_event_id =="" || !(is_numeric($current_event_id))) {
              return $current_event_id; //redirect to index page
        }
        
        $event_capability_array = $this->getUserEventRoleCapabilities();
        $user_role_id = $event_capability_array[$current_event_id][0]['role_id'];
        $user_grade_level_id = $event_capability_array[$current_event_id][0]['grade_level_id'];
        $user_table_id = $event_capability_array[$current_event_id][0]['table_id'];
        $event_type_id = $event_capability_array[$current_event_id][0]['event_type_id'];
        
        if (!$this->checkStatusAccess($component_id,$user_role_id,"list",null,"EventScoringItemStatus",null,null,$current_event_id)) {
            throw new AccessDeniedException();
        }
        
        $status_assigned_access=false;
        $update_roles_access=false;
        
        //Asynchronous Scoring settings
        $block_quota_reached_access=true; 
        $user_block_quota_reached_access=true;
        $error_msg_block_quota="";
        $error_msg_user_block_quota="";
        $paper_count_total=array();
        $block_capability_array=array();
        $user_block_capability_array=array();
        $scorer_block_capability_array=array();
        
        $user_info_msg="";
        $warning_msg_count=0;
        
        //Set whether this is the Queue or Reporting Page
        $reporting = false; //Assume this is the Queue, not Recent Papers reporting page     
        $url =  $this->getRequest()->getPathInfo();
        if (strpos($url, "/reporting")!== false) {  //The url of the page designates that it's the Recent Papers reporting page
            $reporting=true;
        }
  
        $em = $this->getDoctrine()->getManager();
       
        //get System Roles
        $system_roles_array = $this->getSystemRoles();
        $role_event_leader_id=$system_roles_array['Event Leader'];
        $role_room_leader_id=$system_roles_array['Room Leader'];
        $role_table_leader_id=$system_roles_array['Table Leader'];
        $role_scorer1_id=$system_roles_array['Scorer 1'];
        $role_scorer2_id = $system_roles_array['Scorer 2']; 
        $role_admin_id=$system_roles_array['Admin']; 
        
        $scorers = "";
        $roles=array();
        
        $event_user_id="";
        $eu_record ="";
        $target_user="";
        
        if ($user_role_id !=$role_admin_id) { //everyone but Admin should be in event_user table as an attendee
            $event_user_id=$event_capability_array[$current_event_id][0]['id'];
        }
        
        //Extra processing for Queue page
        if ($reporting != true)  {          
    
            $status_ready = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Ready'))->getId();
            $status_assigned = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Assigned'))->getId();
            
            //Rule 1 - check whether user already has papers with status assigned as latest status
            //Set check_status_assigned to true for checkStatusAccess function to check whether the user already has papers assigned (if so, they cannot get a new paper)
            $status_assigned_access = $this->checkStatusAccess($component_id,$user_role_id,"create", null,$object="EventScoringItemStatus",$status_ready,$status_assigned,$current_event_id,$check_status_exists=true);

            $update_roles_access = $this->checkAccessTimeframe("eventsite", $component_id, $user_role_id, "edit", null,"EventScoringItemStatus", $current_event_id,null,null);
            
            //Asynchronous Scoring Processing
            if ($event_type_id ==2) { //asynchronous event processing
            
                //get total papers (reads) in event by grade level
               $paper_count_total = $this->getPaperTotalsByGradeLevel($user_role_id,$user_grade_level_id, $current_event_id);
               
               //get total papers already assigned by grade level
               $paper_count_grade_level_assigned_array=$this->getPapersAssignedByGradeLevel($user_role_id,$user_grade_level_id, $current_event_id,$status_assigned);
               
               //build block capability array by grade level
               $block_capability_array = $this->getBlocksCapability($user_role_id,$user_grade_level_id,$user_table_id,$current_event_id, $paper_count_total,$paper_count_grade_level_assigned_array);
                  
               //now check individual stats and build user block capability array by event_user_id                
               if ($event_user_id !="") {
                    $eu_record = $em->getRepository('NwpAssessmentBundle:EventUser')->findBy(array('id' => $event_user_id)); 
                    if ($eu_record) {
                        $target_percent_user=$eu_record[0]->getTarget();
                        $max_block_user=$eu_record[0]->getMaxBlock();
                       //get total papers (reads) in event that have already been assigned to scorer
                        $paper_count_total_user_assigned=$this->getPapersAssignedUser($event_user_id,$current_event_id,$status_assigned);
                        //set user block statistics
                        if (((isset($paper_count_total[$user_grade_level_id])) && ($paper_count_total[$user_grade_level_id]>0))
                            && (isset($block_capability_array[$user_grade_level_id])))
                        {
                            $user_block_capability_array[$event_user_id] = $this->getUserBlocksCapability($event_user_id,$user_grade_level_id,$user_role_id,$target_percent_user,$max_block_user,$paper_count_total,$paper_count_total_user_assigned,$block_capability_array);
                        }
                            
                        }
               }
              //check Rules 2 and 3 
              $block_quota_reached_access = $this->checkBlockQuotaAccess($event_user_id, $user_role_id,$user_block_capability_array,$role_scorer1_id,$role_scorer2_id);
              $user_block_quota_reached_access = $this->checkUserBlockQuotaAccess($event_user_id, $user_role_id,$user_block_capability_array,$role_scorer1_id,$role_scorer2_id);
              if ($block_quota_reached_access==false)  {
                  $error_msg_block_quota = "There are no papers to score in this block. Please contact your Table Leader.";
              }
              if ($user_block_quota_reached_access==false) {
                  $error_msg_user_block_quota = "You do not have access to score papers. Please contact your Table Leader.";
              } 
             
            } //end of Asynchronous scoring processing
            
             //If Table Leader, get the Scorers at the Table, so that the Table Leader can update their roles
            if ($user_role_id ==$role_table_leader_id) { 

                $queryBuilder =$em->getRepository('NwpAssessmentBundle:EventUser')
                              ->createQueryBuilder('eu')
                              ->select('eu')
                              ->innerJoin('\Application\Sonata\UserBundle\Entity\User', 'u', 'WITH', 'u.id = eu.user')
                              ->Where('eu.event='.$current_event_id)
                              ->andWhere('eu.gradeLevel='.$user_grade_level_id)  
                              ->andWhere('eu.tableId='.$user_table_id)
                              ->andWhere('eu.role IN ('.$role_scorer1_id.",".$role_scorer2_id.')')
                              ->orderBy('u.firstname,u.lastname', 'ASC');

                $query = $queryBuilder->getQuery();
                $scorers= $query->getResult();
                if ($event_type_id == 2) { //asynchronous event processing
                    foreach ($scorers as $scorer) {
                        //get total papers (reads) in event that have already been assigned to scorer
                        $paper_count_total_user_assigned=$this->getPapersAssignedUser($scorer->getId(),$current_event_id,$status_assigned);
                        //build user block capability array
                        $scorer_event_user_id=$scorer->getId();
                        $scorer_grade_level_id=$scorer->getGradeLevel()->getId();
                        $scorer_role_id = $scorer->getRole()->getId();
                        $scorer_target_percent_user=$scorer->getTarget();
                        $scorer_max_block_user=$scorer->getMaxBlock();
                        if (((isset($paper_count_total[$scorer_grade_level_id])) && ($paper_count_total[$scorer_grade_level_id]>0))
                            && (isset($block_capability_array[$scorer_grade_level_id])))
                        {
                            $scorer_block_capability_array_temp = $this->getUserBlocksCapability( $scorer_event_user_id,$scorer_grade_level_id,$scorer_role_id,$scorer_target_percent_user,$scorer_max_block_user,$paper_count_total,$paper_count_total_user_assigned,$block_capability_array);
                            if (sizeof($scorer_block_capability_array_temp) >0) {
                                $scorer_block_capability_array[$scorer_event_user_id] = $scorer_block_capability_array_temp;
                            }  
                        }
                    }
                }
                
                //update Scorer Review Status
                $chk_update_role="";
                if ($this->get('request')->query->get('chk_update_role') !='') {
                    $chk_update_role= $this->get('request')->query->get('chk_update_role');
                }    
                    
                if ($chk_update_role !="") {
                    $chk_update_role_array = explode("_", $chk_update_role);
                    $chk_update_role_event_user_id = $chk_update_role_array[0];
                    $chk_update_role_role_id = $chk_update_role_array[1];
                       
                    if ($chk_update_role_role_id=="true") {
                        $update_role= $role_scorer1_id;
                    } else {
                        $update_role= $role_scorer2_id;
                    }
                    
                    foreach ($scorers as $scorer) {
                        if ($scorer->getId()==$chk_update_role_event_user_id) {
                            $error=$this->UpdateEventUserRole($scorer,$update_role,$current_event_id,$role_scorer2_id); 
                        }
                    }
                } 
                //end of update Scorer Review Status
                
                //update max block for user - either activate all blocks or set max block to current block
                //on/off checkboxes
                $chk_update_max_block="";
                $user_block="";
                
                if ($this->get('request')->query->get('chk_update_max_block') !='') {
                    $chk_update_max_block= $this->get('request')->query->get('chk_update_max_block');
                }
                
                if ($chk_update_max_block !="") {
                    $chk_update_max_block_array = explode("_", $chk_update_max_block);
                    $chk_update_max_block_event_user_id = $chk_update_max_block_array[0];
                    $chk_update_max_block_block_count = $chk_update_max_block_array[1];
                    $chk_update_max_block_current_block = $chk_update_max_block_array[2];
                    $chk_update_max_block_checked = $chk_update_max_block_array[3];
                    
                    if ($chk_update_max_block_checked=="true") {
                        $user_block= $chk_update_max_block_current_block;
                    } else {
                        $user_block= $chk_update_max_block_block_count;
                    }
                    $error=$this->batchActionEventUserUpdateBlocks($chk_update_max_block_event_user_id,$user_block);
                }
                
                //Activate and Deactivate buttons
                $activate__block_button="";
                if ($this->get('request')->get('btn_set_user_block')) {
                    $activate__block_button = $this->get('request')->get('btn_set_user_block');
                    $user_block = $this->get('request')->get('user_block');
                } elseif  ($this->get('request')->get('btn_set_user_block_deactivate_all')) {
                    $activate__block_button = $this->get('request')->get('btn_set_user_block_deactivate_all');
                    $user_block = $this->get('request')->get('user_block_deactivate_all');
                }
                if (($activate__block_button !="") && ( $user_block!="")) {
                    $error=$this->batchActionEventUserUpdateBlocks($activate__block_button,$user_block); 
                    if ($error==false) { //refresh the index page
                        return $this->redirect($this->generateUrl('eventsite_eventscoringitemstatus')."#scorer_mgmt");   
                    }
                }

                $roles_update="Scorer 1, Scorer 2"; //roles that we want to be able to update
                $each_role_update = explode(",", $roles_update);

                for($i=0;$i<count($each_role_update);$i++){  
                    foreach ($system_roles_array as $key => $val) {
                        if (trim($key)==trim($each_role_update[$i])) {
                           $roles[$val] = $key;
                        }
                    }
                }

            } //end of Table Leader Processing
            
        } //end of Queue page extra processing
        
        list($filterForm, $queryBuilder) = $this->filter();
        
        $query = $queryBuilder->getQuery();
        $entities= $query->getResult();

       // echo "<br>next lines of code figure out whether ITEM has groupings<br>";
       
       // foreach($entities as $entity) {
       //     echo "<br>id ".$entity->getEventScoringItem()->getId().":";
       //     $item = $entity->getEventScoringItem()->getGroupings();
       //     foreach ($item as $i) {
       //         echo " group ".$i->getName().",";
       //    }
       // }
        
       // echo "<br>next lines of code figure out whether USER has groupings<br>";
       
       //if ($event_user_id !="") {
       //     $eu_record = $em->getRepository('NwpAssessmentBundle:EventUser')->findBy(array('id' => $event_user_id));
  
       //    if ($eu_record) {
       //        foreach ($eu_record as $eu) { 
       //            echo "<br>id ".$eu->getId().":";
       //            $event_user= $eu->getGroupings();
       //            foreach ($event_user as $eug) {
       //               echo " group ".$eug->getName().",";
       //       
       //           }
       //       }
            
       //   }
       //}

        list($entities, $pagerHtml) = $this->paginatorArray($entities);
        
        //var_dump($block_capability_array);
        //var_dump($user_block_capability_array);
       //var_dump($scorer_block_capability_array);
        
        //set Flash messages for Asynchronous scoring
        if (isset($user_block_capability_array[$event_user_id])) {    
            if ($user_block_capability_array[$event_user_id][0]['user_next_block_ready']==1) {
                $user_next_block_ready_msg = "Congratulations! You have completed Block ".($user_block_capability_array[$event_user_id][0]['user_current_block']-1)
                                             .". Please log out and take a break. Prior to starting your next block, complete the topic immersion process for "
                                             .$block_capability_array[$user_grade_level_id][$user_block_capability_array[$event_user_id][0]['user_current_block']]['block_prompt']
                                             .". When you are done, please contact your Table Leader.";
                $this->get('session')->getFlashBag()->add('success', $user_next_block_ready_msg);
            } else { //display error messages only if congratulations message does not display
                if ($error_msg_block_quota !="") {
                    $this->get('session')->getFlashBag()->add('error', $error_msg_block_quota);
                }
                if ($error_msg_user_block_quota !="") {
                    $this->get('session')->getFlashBag()->add('error', $error_msg_user_block_quota);
                }
            }
            if (($user_role_id !=$role_admin_id) && ($user_role_id !=$role_event_leader_id) && ($user_role_id !=$role_room_leader_id)) {
                $user_info_msg=" You have scored ".$user_block_capability_array[$event_user_id][0]['user_papers_assigned'].
                     " out of ".$user_block_capability_array[$event_user_id][0]['user_total_target_papers']." papers.  "
                     . "You are on Block ".$user_block_capability_array[$event_user_id][0]['user_current_block'].".";
                //$this->get('session')->getFlashBag()->add('info', $user_info_msg);
            }    
        }
        
        foreach ($scorer_block_capability_array as $key => $value) {
            if (($scorer_block_capability_array[$key][0]['block_quota_reached']== 1) || ($scorer_block_capability_array[$key][0]['user_block_quota_reached']== 1)) { //at least one grade level has less than 100% target total
                $warning_msg_count++;   
            }   
        }
        
        return array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'filterData' => $filterForm->getData(),
            'status_assigned_access' =>  $status_assigned_access,
            'paper_count_total' =>  $paper_count_total,
            'block_quota_reached_access' =>  $block_quota_reached_access,
            'user_block_quota_reached_access' =>  $user_block_quota_reached_access,
            'update_roles_access' =>  $update_roles_access,
            'user_role_id' => $user_role_id,
            'role_event_leader_id' => $role_event_leader_id,
            'role_room_leader_id' => $role_room_leader_id,
            'role_table_leader_id' => $role_table_leader_id,
            'role_scorer1_id' => $role_scorer1_id,
            'role_scorer2_id' => $role_scorer2_id,
            'role_admin_id' => $role_admin_id,
            'scorers' => $scorers,
            'roles' => $roles,
            'reporting' => $reporting,
            'block_capability_array' => $block_capability_array,
            'user_block_capability_array' => $user_block_capability_array,
            'scorer_block_capability_array' => $scorer_block_capability_array,
            'warning_msg_count' => $warning_msg_count, 
            'user_info_msg' => $user_info_msg,        
        );

    }
    
    /**
     * Lists all EventScoringItemStatus practice entities.
     *
     * @Route("/calibration", name="eventsite_calibration")
     * @Template()
     */
    public function calibrationAction()
    {
        
      $current_event_id = $this->getCurrentEvent();
      
      $component_id=2;
      $event_user_id="";
      
      if ($current_event_id =="" || !(is_numeric($current_event_id))) {
        return $current_event_id; //redirect to index page
      }
        
      $event_capability_array = $this->getUserEventRoleCapabilities();
      $user_role_id = $event_capability_array[$current_event_id][0]['role_id'];
      $user_grade_level_id = $event_capability_array[$current_event_id][0]['grade_level_id'];
      $user_table_id = $event_capability_array[$current_event_id][0]['table_id'];
      
      if (!$this->checkStatusAccess($component_id,$user_role_id,"list",null,"EventScoringItemStatus",null,null,$current_event_id)) {
            throw new AccessDeniedException();
      }
      
      //get System Roles
      $system_roles_array = $this->getSystemRoles();
      $role_event_leader_id=$system_roles_array['Event Leader'];
      $role_room_leader_id=$system_roles_array['Room Leader'];
      $role_table_leader_id=$system_roles_array['Table Leader'];
      $role_scorer1_id=$system_roles_array['Scorer 1'];
      $role_scorer2_id = $system_roles_array['Scorer 2']; 
      $role_admin_id=$system_roles_array['Admin']; 
      
      if ($user_role_id !=$role_admin_id) { //everyone but Admin should be in event_user table as an attendee
            $event_user_id=$event_capability_array[$current_event_id][0]['id'];
      }
      
      $em = $this->getDoctrine()->getManager();
        
      list($filterForm, $queryBuilder) = $this->filter();   
      
      $query = $queryBuilder->getQuery();
        
      $entities= $query->getResult();
      
      list($entities, $pagerHtml) = $this->paginatorArray($entities);
      
      return array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'filterData' => $filterForm->getData(),
            'user_role_id' => $user_role_id,
            'role_event_leader_id' => $role_event_leader_id,
            'role_room_leader_id' => $role_room_leader_id,
            'role_table_leader_id' => $role_table_leader_id,
            'role_scorer1_id' => $role_scorer1_id,
            'role_scorer2_id' => $role_scorer2_id,
            'role_admin_id' => $role_admin_id,
        );  
        
    }
    
    /**
     * Finds and displays corect scores for calibration papers for EventScoringItem entity.
     * @Route("/calibration/{id}/correct", name="eventsite_calibration_correct_results")
     * @Route("/calibration/{id}/commentary", name="eventsite_calibration_commentary")
     * @Template()
     */
    public function calibrationCorrectResultsAction($id) {
         //is current event set?
        $current_event_id = $this->getCurrentEvent();
        
        $component_id=2;
        $event_user_id="";
       
        if ($current_event_id =="" || !(is_numeric($current_event_id))) {
            return $current_event_id; //redirect to index page
        }
        
        //check access, for now just get role, grade level and table info for the user
        $event_capability_array = $this->getUserEventRoleCapabilities();
        $user_role_id = $event_capability_array[$current_event_id][0]['role_id'];
        $user_grade_level_id = $event_capability_array[$current_event_id][0]['grade_level_id'];
        $user_table_id = $event_capability_array[$current_event_id][0]['table_id'];
        
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        
        //get System Roles
        $system_roles_array = $this->getSystemRoles();
        $role_event_leader_id=$system_roles_array['Event Leader'];
        $role_room_leader_id=$system_roles_array['Room Leader'];
        $role_table_leader_id=$system_roles_array['Table Leader'];
        $role_scorer1_id=$system_roles_array['Scorer 1'];
        $role_scorer2_id = $system_roles_array['Scorer 2']; 
        $role_admin_id = $system_roles_array['Admin'];
        
        $status_accepted = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Accepted'));
        
        if ($user_role_id !=$role_admin_id) { //everyone but Admin should be in event_user table as an attendee
            $event_user_id=$event_capability_array[$current_event_id][0]['id'];
        }
       
        if ($component_id==2) {
            $entity = $this->checkStatusUserAccess($component_id,"show",$event_user_id,$user_role_id,$user_grade_level_id,$user_table_id,$current_event_id,$id,$id,$role_scorer1_id,$role_scorer2_id,$role_table_leader_id,$role_room_leader_id);  
            if (!$entity) {
                throw new AccessDeniedException();
            }        
        }
       
        $dbh= $this->get('database_connection');
        $dbh->beginTransaction();
        
        ///get correct results - Room Leader that scored last
        $max_correct_score_sql = "SELECT MAX(id) FROM event_scoring_item_status_byuser_final lf
                                  WHERE event_scoring_item_id=".$id." AND status_id = ".$status_accepted->getId()." AND role_id_created=$role_admin_id"; 
        
        
        $sth = $dbh->prepare($max_correct_score_sql); 
        $sth->execute();
        $max_correct_score_count = $sth->fetchColumn();
        
        $correct_scores = $em->getRepository('NwpAssessmentBundle:ScoringItemScore')->findBy(array('eventScoringItemStatus'=> $max_correct_score_count), array('id' => 'ASC'));     
        
        return array(
            'entity' => $entity,
            'correct_scores' => $correct_scores,
            'user_role_id' => $user_role_id,
            'role_admin_id' => $role_admin_id,
            'role_event_leader_id' => $role_event_leader_id,
            'role_room_leader_id' => $role_room_leader_id,
            'role_table_leader_id' => $role_table_leader_id,
            'role_scorer1_id' => $role_scorer1_id,
            'role_scorer2_id' => $role_scorer2_id,
        );
    }
    
    /**
     * Finds and displays calibration results for EventScoringItem entity.
     * @Route("/calibration/{id}/results", name="eventsite_calibration_results")
     * @Template()
     */
    public function calibrationResultsAction($id) {
        
         //is current event set?
        $current_event_id = $this->getCurrentEvent();
        
        $component_id=2;
       
        if ($current_event_id =="" || !(is_numeric($current_event_id))) {
            return $current_event_id; //redirect to index page
        }
        
        $calibration_results_count=array();
        $calibration_results=array();
        $calibration_results_table=array();
        $max_table_results_count=0;
        
        //check access, for now just get role, grade level and table info for the user
        $event_capability_array = $this->getUserEventRoleCapabilities();
        $user_role_id = $event_capability_array[$current_event_id][0]['role_id'];
        $user_grade_level_id = $event_capability_array[$current_event_id][0]['grade_level_id'];
        $user_table_id = $event_capability_array[$current_event_id][0]['table_id'];
        
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        
        //get System Roles
        $system_roles_array = $this->getSystemRoles();
        $role_event_leader_id=$system_roles_array['Event Leader'];
        $role_room_leader_id=$system_roles_array['Room Leader'];
        $role_table_leader_id=$system_roles_array['Table Leader'];
        $role_scorer1_id=$system_roles_array['Scorer 1'];
        $role_scorer2_id = $system_roles_array['Scorer 2']; 
        $role_admin_id = $system_roles_array['Admin']; 
        
        $event_user_id="";
        
        $max_table_array=Array();
        $grade_level_array=Array();
        $calibration_results_by_user=Array();
        
        if ($user_role_id !=$role_admin_id) { //everyone but Admin should be in event_user table as an attendee
            $event_user_id=$event_capability_array[$current_event_id][0]['id'];
        }
         
        if ($component_id==2) {
            if ($user_role_id==$role_admin_id) {
                $action="list";
            } else {
                $action="show";
            }
            $entity = $this->checkStatusUserAccess($component_id,$action,$event_user_id,$user_role_id,$user_grade_level_id,$user_table_id,$current_event_id,$id,$id,$role_scorer1_id,$role_scorer2_id,$role_table_leader_id,$role_room_leader_id);  
            if (!$entity) {
                throw new AccessDeniedException();
            }        
        }
        
        //get the scoring rubric used for this event
        $current_event = $em->getRepository('NwpAssessmentBundle:Event')->find($current_event_id);
        
        $user=$this->container->get('security.context')->getToken()->getUser();
        $status_accepted = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Accepted'));
        
        $scoring_rubric_id=$current_event->getScoringRubric()->getId();
        $scoring_rubric = $em->getRepository('NwpAssessmentBundle:ScoringRubric')->find($scoring_rubric_id);
        
        //get scoring scale the rubric uses
        $min_score = $scoring_rubric->getMinScore();
        $max_score = $scoring_rubric->getMaxScore();
        $scoring_scale=array();
        for($c= $min_score;$c<=$max_score;$c++){
            $scoring_scale[$c]=$c;
        }
        
        $attributes = $em->getRepository('NwpAssessmentBundle:ScoringRubricAttribute')->findBy(array('rubric'=> $scoring_rubric_id), array('id' => 'ASC'));

        $dbh= $this->get('database_connection');
        $dbh->beginTransaction();
        
        //get my score results
        $status_list_by_user =  $em->getRepository('NwpAssessmentBundle:EventScoringItemStatusListByUser')->findOneBy(array('eventScoringItem' => $id, 'createdBy' => $user->getId()));
        if ($status_list_by_user) {
            $scores = $em->getRepository('NwpAssessmentBundle:ScoringItemScore')->findBy(array('eventScoringItemStatus'=> $status_list_by_user->getMaxScoringItemScoreStatus()), array('id' => 'ASC'));     
        } else {
            $scores=null;
        }
        
        ///get correct results - Room Leader that scored last
        $max_correct_score_sql = "SELECT MAX(id) FROM event_scoring_item_status_byuser_final lf
                                  WHERE event_scoring_item_id=$id AND role_id_created=$role_admin_id"; 
        $sth = $dbh->prepare($max_correct_score_sql); 
        $sth->execute();
        $max_correct_score_count = $sth->fetchColumn();
        
        $correct_scores = $em->getRepository('NwpAssessmentBundle:ScoringItemScore')->findBy(array('eventScoringItemStatus'=> $max_correct_score_count), array('id' => 'ASC'));     
         
       // if (($user_role_id ==$role_admin_id) || ($user_role_id ==$role_event_leader_id)||($user_role_id ==$role_room_leader_id)) {
            $max_table_sql = "SELECT MAX(table_id) max_table_number,grade_level_id FROM event_user WHERE event_id=".$current_event_id." AND grade_level_id IS NOT NULL GROUP by grade_level_id"; 
            $sth = $dbh->prepare($max_table_sql); 
            $sth->execute();
            $max_table_results_count = $sth->fetchAll(\PDO::FETCH_ASSOC);
        
            $max_table_results_count_array_size=sizeof($max_table_results_count);
        
        
            for($m=0;$m<$max_table_results_count_array_size;$m++){ 
                $grade_level_id = $max_table_results_count[$m]['grade_level_id'];
                $max_table_array[$grade_level_id]['max_table_number']=$max_table_results_count[$m]['max_table_number']; 
            }
        //} 
        
        #dipslay number of people who scored each attribute and each scoring point for callibraton papers
        $calibration_results_sql ="SELECT
                            l2.event_id                    event_id,
                            g.id             	           grade_level_id,
                            g.name			   grade_level_name,
                            l2.event_scoring_item_id       event_scoring_item_id,
                            l2.scoring_item_id 		   scoring_item_id,
                            l2.status_id                   status_id,
                            COUNT(DISTINCT(l2.created_by)) number_of_scorers,
                            s.event_scoring_item_status_id event_scoring_item_status_id,
                            s.scoring_rubric_attribute_id  scoring_rubric_attribute_id,
                            s.score                        score
                            FROM (event_scoring_item_status_byuser_final l2
                            JOIN grade_level g ON g.id=l2.grade_level_id
                            JOIN scoring_item_score s
                            ON ((l2.max_scoring_item_score_status_id = s.event_scoring_item_status_id)))
                            WHERE (l2.status_id = 11) and event_id=$current_event_id AND event_scoring_item_id=$id
                            AND (l2.role_id_created=$role_scorer1_id OR l2.role_id_created=$role_scorer2_id)";
        if (($user_role_id !=$role_admin_id) && ($user_role_id !=$role_event_leader_id)) {
             $calibration_results_sql.=" AND g.id =".$user_grade_level_id;
        }
        $calibration_results_sql.= " GROUP BY l2.event_id,l2.event_scoring_item_id,s.scoring_rubric_attribute_id,s.score
                                    ORDER BY l2.event_scoring_item_id,s.scoring_rubric_attribute_id ASC";
        
        $sth = $dbh->prepare($calibration_results_sql); 
        $sth->execute();
        $calibration_results_count = $sth->fetchAll(\PDO::FETCH_ASSOC);
        
        $calibration_results_count_array_size=sizeof($calibration_results_count);
        
        
        for($c=0;$c<$calibration_results_count_array_size;$c++){  
            if ($c==0) {
              $calibration_results['event_scoring_item_id']= $calibration_results_count[$c]['event_scoring_item_id'];
              $calibration_results['scoring_item_id']= $calibration_results_count[$c]['scoring_item_id'];
            }
            $grade_level_id = $calibration_results_count[$c]['grade_level_id'];
            $grade_level_array[$grade_level_id]['grade_level_name']=$calibration_results_count[$c]['grade_level_name'];
            
            $attribute= $calibration_results_count[$c]['scoring_rubric_attribute_id'];
            $score=$calibration_results_count[$c]['score'];
            $number_of_scorers = $calibration_results_count[$c]['number_of_scorers'];
            
            $calibration_results[$grade_level_id][$attribute][$score]['number_of_scorers']=$number_of_scorers;
            
            
            #keep track of total number of scorers per grade level per attribute
            if (!isset($calibration_results[$grade_level_id]['total'])) {
                    $calibration_results[$grade_level_id]['total']=0;
            }
            $calibration_results[$grade_level_id]['total']+=$number_of_scorers;
                 
        } 
        
        //we need total of scorers (not per attribute, but for all attributes)
        if (isset ($calibration_results[$grade_level_id]['total']) ){
            $calibration_results[$grade_level_id]['total']=$calibration_results[$grade_level_id]['total']/count($attributes);
        }
        
        if (($user_role_id !=$role_scorer1_id) && ($user_role_id !=$role_scorer2_id)) {
            $calibration_results_by_user_sql =
                            "SELECT 
                            l2.event_id event_id, 
                            g.id grade_level_id, 
                            g.name grade_level_name, 
                            l2.table_id_created table_id_created, 
                            l2.event_scoring_item_id event_scoring_item_id, 
                            l2.scoring_item_id scoring_item_id, l2.status_id status_id, 
                            l2.created_by created_by, u.firstname,u.lastname,
                            s.event_scoring_item_status_id event_scoring_item_status_id, 
                            s.scoring_rubric_attribute_id scoring_rubric_attribute_id, s.score score 
                            FROM (event_scoring_item_status_byuser_final l2 
                            JOIN grade_level g ON g.id=l2.grade_level_id 
                            JOIN fos_user_user u ON u.id=l2.created_by
                            JOIN scoring_item_score s ON ((l2.max_scoring_item_score_status_id = s.event_scoring_item_status_id))) 
                            WHERE (l2.status_id = 11) AND event_id=$current_event_id AND event_scoring_item_id=$id 
                            AND (l2.role_id_created=$role_scorer1_id OR l2.role_id_created=$role_scorer2_id)";
            if (($user_role_id !=$role_admin_id) && ($user_role_id !=$role_event_leader_id)) {
                $calibration_results_by_user_sql.=" AND g.id =".$user_grade_level_id;
            }
            if (($user_role_id !=$role_admin_id) && ($user_role_id !=$role_event_leader_id) && ($user_role_id !=$role_room_leader_id)) {
                $calibration_results_by_user_sql.=" AND l2.table_id_created =".$user_table_id;
            }
            $calibration_results_by_user_sql.= " ORDER BY l2.event_scoring_item_id,g.id,l2.table_id_created,u.lastname,u.firstname,l2.created_by, s.scoring_rubric_attribute_id ASC";

            $sth = $dbh->prepare($calibration_results_by_user_sql); 
            $sth->execute();
            $calibration_results_by_user_count = $sth->fetchAll(\PDO::FETCH_ASSOC);
        
            $calibration_results_by_user_array_size=sizeof($calibration_results_by_user_count);
        
            for($cu=0;$cu<$calibration_results_by_user_array_size;$cu++){ 
                $grade_level_id = $calibration_results_by_user_count[$cu]['grade_level_id'];
                $table_id = $calibration_results_by_user_count[$cu]['table_id_created'];
                $user_id = $calibration_results_by_user_count[$cu]['created_by'];
                $attribute =$calibration_results_by_user_count[$cu]['scoring_rubric_attribute_id'];
                $score = $calibration_results_by_user_count[$cu]['score'];
                $firstname = $calibration_results_by_user_count[$cu]['firstname'];
                $lastname = $calibration_results_by_user_count[$cu]['lastname'];
                $calibration_results_by_user[$grade_level_id][$table_id][$user_id][0]['fullname']=$firstname." ".$lastname;
                $calibration_results_by_user[$grade_level_id][$table_id][$user_id][$attribute]['score']=$score;
            }
        }
        
        $dbh=null;
        
         
        return array(
            'correct_scores' => $correct_scores,
            'scores' => $scores,
            'grade_level_array' => $grade_level_array, 
            'calibration_results' => $calibration_results, 
            'calibration_results_by_user' => $calibration_results_by_user, 
            'event_capability_array'=> $event_capability_array, 
            'attributes' => $attributes,
            'scoring_scale' => $scoring_scale,
            'user_role_id' => $user_role_id,
            'role_admin_id' => $role_admin_id,
            'role_event_leader_id' => $role_event_leader_id,
            'role_room_leader_id' => $role_room_leader_id,
            'role_table_leader_id' => $role_table_leader_id,
            'role_scorer1_id' => $role_scorer1_id,
            'role_scorer2_id' => $role_scorer2_id,
            'user_grade_level_id' => $user_grade_level_id,
            'user_table_id' => $user_table_id,
            'max_table_array'=> $max_table_array,
            
        );
        
        
    }
    
    /**
 
     * @Route("/progress", name="eventsite_progress")
     * @Template("NwpAssessmentBundle:EventScoringItemStatus:progress.html.twig")
     */
    public function eventProgress() {
        
        $current_event_id = $this->getCurrentEvent();
        
        if ($current_event_id =="" || !(is_numeric($current_event_id))) {
              return $current_event_id; //redirect to index page
        }
        
        if (!$this->checkAccess("list",null,"EventScoringItem")) {
            throw new AccessDeniedException();
        }
        
        //check access, for now just get role, grade level and table info for the user
        $event_capability_array = $this->getUserEventRoleCapabilities();
        $user_role_id = $event_capability_array[$current_event_id][0]['role_id'];
        $user_grade_level_id = $event_capability_array[$current_event_id][0]['grade_level_id'];
        $user_table_id = $event_capability_array[$current_event_id][0]['table_id'];
        
        //get System Roles
        $system_roles_array = $this->getSystemRoles();
        $role_event_leader_id=$system_roles_array['Event Leader'];
        $role_room_leader_id=$system_roles_array['Room Leader'];
        $role_table_leader_id=$system_roles_array['Table Leader'];
        $role_scorer1_id=$system_roles_array['Scorer 1'];
        $role_scorer2_id = $system_roles_array['Scorer 2']; 
        $role_admin_id = $system_roles_array['Admin']; 
        
        $papers_status_count = "";
        
        $grade_level_array=array();
        $papers_status_count_status=array(); 
        
        $em = $this->getDoctrine()->getManager();
        $queryBuilder =$em->getRepository('NwpAssessmentBundle:ScoringItemStatus')
                          ->createQueryBuilder('s')
                          ->select('s')
                          ->Where('s.name=\'Accepted\'')
                          ->OrWhere('s.name=\'Non-Scorable\'')
                          ->OrWhere('s.name=\'Red Flag\'')
                          ->OrWhere('s.name=\'Ready\'')
                          ->orderBy('s.orderId');
            
        $query = $queryBuilder->getQuery();
        $statuses= $query->getResult();
        
        //calculatee status column, done separately from other statuses, and merged later to get one status array
        $statuses_in_progress[-1] = array ("id"=>"-1", "name"=>"In Progress"); 
        
        $dbh= $this->get('database_connection');
        $dbh->beginTransaction();
       
        $papers_status_sql ="SELECT grade_level_id,grade_level_name,status_id,`name` status_name,SUM(number_of_papers) number_of_papers
                             FROM
                             (SELECT grade_level_id,g.name grade_level_name, status_id,sis.name,COUNT(*) number_of_papers
                             FROM event_scoring_item_status_list_final lf
                             JOIN scoring_item_status sis ON sis.id=lf.status_id
                             JOIN grade_level g ON g.id=lf.grade_level_id
                             WHERE scoring_round_number !=3 and event_id=".$current_event_id; 
                                 
        if (($user_role_id !=$role_admin_id) && ($user_role_id !=$role_event_leader_id)) {
             $papers_status_sql.=" AND grade_level_id =".$user_grade_level_id;
        }
        
        $papers_status_sql.= " GROUP BY grade_level_id,status_id
                             UNION ALL
                             SELECT grade_level_id,g.name grade_level_name,status_id,sis.name,COUNT(*) number_of_papers 
                             FROM event_scoring_item esi
                             JOIN scoring_item_status sis ON sis.id=esi.status_id
                             JOIN scoring_item s ON s.id=esi.scoring_item_id
                             JOIN grade_level g ON g.id=s.grade_level_id
                             WHERE status_id=1 AND scoring_round_number=1 AND component_id=1 AND event_id=".$current_event_id;
        
        if (($user_role_id !=$role_admin_id) && ($user_role_id !=$role_event_leader_id)) {
             $papers_status_sql.=" AND grade_level_id =".$user_grade_level_id;
        }
        $papers_status_sql.= " GROUP BY grade_level_id,status_id
			     UNION ALL
			     SELECT grade_level_id,g.name grade_level_name,0,'In Progress', COUNT(*) number_of_papers
			     FROM event_scoring_item esi
			     JOIN scoring_item_status sis ON sis.id=esi.status_id
			     JOIN scoring_item s ON s.id=esi.scoring_item_id
			     JOIN grade_level g ON g.id=s.grade_level_id
			     WHERE scoring_item_type_id=2 AND esi.status_id !=13 AND esi.status_id !=1 AND scoring_round_number=1  AND component_id=1 AND event_id=".$current_event_id;
        
        if (($user_role_id !=$role_admin_id) && ($user_role_id !=$role_event_leader_id)) {
             $papers_status_sql.=" AND grade_level_id =".$user_grade_level_id;
        }
        $papers_status_sql.= " GROUP BY grade_level_id
			     UNION ALL
			     SELECT grade_level_id,g.name grade_level_name,status_id,sis.name, COUNT(*) number_of_papers
			     FROM event_scoring_item esi
			     JOIN scoring_item_status sis ON sis.id=esi.status_id
			     JOIN scoring_item s ON s.id=esi.scoring_item_id
			     JOIN grade_level g ON g.id=s.grade_level_id
			     WHERE scoring_item_type_id=2 AND esi.status_id =1 AND scoring_round_number=1  AND component_id=1 AND event_id=".$current_event_id;
        
        if (($user_role_id !=$role_admin_id) && ($user_role_id !=$role_event_leader_id)) {
             $papers_status_sql.=" AND grade_level_id =".$user_grade_level_id;
        }
        $papers_status_sql.= " GROUP BY grade_level_id,status_id)
                              A
                             GROUP BY A.grade_level_id,A.status_id";
        
        
        //echo $papers_status_sql;
        
        $sth = $dbh->prepare($papers_status_sql); 
        $sth->execute();
        $papers_status_count = $sth->fetchAll(\PDO::FETCH_ASSOC);
        
        $papers_status_count_array_size=sizeof($papers_status_count);
 
        $papers_status_count_total=0;
        for($p=0;$p<$papers_status_count_array_size;$p++){  
            $grade_level= $papers_status_count[$p]['grade_level_id'];
            $grade_level_array[$grade_level]['grade_level_name']=$papers_status_count[$p]['grade_level_name'];
            if (!isset($grade_level_array[$grade_level]['number_of_papers'])) {
               $grade_level_array[$grade_level]['number_of_papers']=0;
            }
            $grade_level_array[$grade_level]['number_of_papers'] +=$papers_status_count[$p]['number_of_papers'];
            
            $status_found=false;
            foreach ($statuses as $s) {
                $status_id= $papers_status_count[$p]['status_id'];
                
               
                if ($status_id==$s->getId()) {
                    $papers_status_count[$grade_level][$status_id]=$papers_status_count[$p];
                    if (!isset( $papers_status_count_status[$status_id]['number_of_papers'])) {
                        $papers_status_count_status[$status_id]['number_of_papers']=0;
                    }
                    $papers_status_count_status[$status_id]['number_of_papers'] +=$papers_status_count[$p]['number_of_papers'];
                  
                    $status_found=true;
                    break;
                }
            }
            if ($status_found==false) {
                if (!isset($papers_status_count[$grade_level][$statuses_in_progress[-1]['id']]['number_of_papers'])) {
                    $papers_status_count[$grade_level][$statuses_in_progress[-1]['id']]['number_of_papers']=0;
                }
                if (!isset($papers_status_count_status[$statuses_in_progress[-1]['id']]['number_of_papers'])) {
                    $papers_status_count_status[$statuses_in_progress[-1]['id']]['number_of_papers']=0;
                }
                $papers_status_count[$grade_level][$statuses_in_progress[-1]['id']]['grade_level_id'] =$papers_status_count[$p]['grade_level_id'];
                $papers_status_count[$grade_level][$statuses_in_progress[-1]['id']]['grade_level_name'] =$papers_status_count[$p]['grade_level_name'];     
                $papers_status_count[$grade_level][$statuses_in_progress[-1]['id']]['id'] =$statuses_in_progress[-1]['id'];
                $papers_status_count[$grade_level][$statuses_in_progress[-1]['id']]['name'] =$statuses_in_progress[-1]['name'];
                $papers_status_count[$grade_level][$statuses_in_progress[-1]['id']]['number_of_papers'] +=$papers_status_count[$p]['number_of_papers'];   
                $papers_status_count_status[$statuses_in_progress[-1]['id']]['number_of_papers'] +=$papers_status_count[$p]['number_of_papers'];   
            }
           
            $papers_status_count_total +=$papers_status_count[$p]['number_of_papers'];
        } 
        
        //merge the 2 arrays, inserting the 2nd array in the previous to last position of number of items in first array
        $insert_position = count($statuses)-1; 
        $statuses = array_merge(array_slice($statuses, 0, $insert_position), $statuses_in_progress, array_slice($statuses, $insert_position));
        
        return array(
            'statuses' => $statuses,
            'grade_level_array' => $grade_level_array,
            'papers_status_count_total' => $papers_status_count_total,
            'papers_status_count_status' => $papers_status_count_status,
            'papers_status_count' => $papers_status_count, 
        );
    }
    
     /**
 
     * @Route("/reliability", name="eventsite_reliability")
     * @Template("NwpAssessmentBundle:EventScoringItemStatus:reliability.html.twig")
     */
    public function eventReliability() {
        $current_event_id = $this->getCurrentEvent();
        
        if ($current_event_id =="" || !(is_numeric($current_event_id))) {
              return $current_event_id; //redirect to index page
        }
        
        if (!$this->checkAccess("list",null,"EventScoringItem")) {
            throw new AccessDeniedException();
        }
        
        //check access, for now just get role, grade level and table info for the user
        $event_capability_array = $this->getUserEventRoleCapabilities();
        $user_role_id = $event_capability_array[$current_event_id][0]['role_id'];
        $user_grade_level_id = $event_capability_array[$current_event_id][0]['grade_level_id'];
        $user_table_id = $event_capability_array[$current_event_id][0]['table_id'];
        
        //get System Roles
        $system_roles_array = $this->getSystemRoles();
        $role_event_leader_id=$system_roles_array['Event Leader'];
        $role_room_leader_id=$system_roles_array['Room Leader'];
        $role_table_leader_id=$system_roles_array['Table Leader'];
        $role_scorer1_id=$system_roles_array['Scorer 1'];
        $role_scorer2_id = $system_roles_array['Scorer 2']; 
        $role_admin_id = $system_roles_array['Admin']; 
        
        $papers_status_count = "";
        
        $grade_level_array=array();
        $reliability_count_array=array();
        $reliability_count_array_attribute=array();
        $reliability_count_array_total=array();
        
        $em = $this->getDoctrine()->getManager();
        
        $current_event = $em->getRepository('NwpAssessmentBundle:Event')->find($current_event_id);
        $scoring_rubric_id=$current_event->getScoringRubric()->getId();
        $adjudication_trigger=$current_event->getAdjudicationTrigger(); 
        
        
        
        #$attributes = $em->getRepository('NwpAssessmentBundle:ScoringAttribute')->findAll();
        
        $queryBuilder = $em->getRepository('NwpAssessmentBundle:ScoringAttribute')
                            ->createQueryBuilder('sa') 
                            ->select('sa')
                            ->innerJoin('NwpAssessmentBundle:ScoringRubricAttribute', 'sra', 'WITH', 'sra.attribute = sa.id')
                            ->innerJoin('NwpAssessmentBundle:ScoringRubric', 'sr', 'WITH', 'sr.id = sra.rubric')
                            ->where('sr.id='.$scoring_rubric_id);  
        
        $query = $queryBuilder->getQuery();
        $attributes= $query->getResult();
          
        
        $dbh= $this->get('database_connection');
        $dbh->beginTransaction();
       
        $reliability_sql =  "SELECT esi.event_id,si.grade_level_id,g.name grade_level_name,
                            COUNT(esi.id) paper_count,sa.id scoring_rubric_attribute_id, sa.name scoring_rubric_attribute_name,
                            'total' count_type
                            FROM event_scoring_item_status esis1
                            JOIN event_scoring_item_status esis2
                            ON esis1.event_scoring_item_id = esis2.event_scoring_item_id
                            JOIN event_scoring_item esi ON esi.id = esis1.event_scoring_item_id AND esi.id = esis2.event_scoring_item_id
                            JOIN scoring_item_score s1 ON s1.event_scoring_item_status_id = esis1.max_scoring_item_score_status_id
                            JOIN scoring_item_score s2 ON s2.event_scoring_item_status_id = esis2.max_scoring_item_score_status_id 
                            AND s1.scoring_rubric_attribute_id = s2.scoring_rubric_attribute_id
                            JOIN scoring_item si ON si.id = esi.scoring_item_id
                            JOIN scoring_rubric_attribute sra ON sra.id = s1.scoring_rubric_attribute_id AND sra.id=s2.scoring_rubric_attribute_id 
                            JOIN scoring_attribute sa ON sa.id=sra.attribute_id
                            JOIN grade_level g ON g.id = si.grade_level_id
                            WHERE event_id=$current_event_id AND component_id=1 AND esis1.scoring_round_number=1 AND esis1.status_id=11
                            AND esis2.scoring_round_number=2 AND esis2.status_id=11 AND s2.score IS NOT NULL";
        
        if (($user_role_id !=$role_admin_id) && ($user_role_id !=$role_event_leader_id)) {
             $reliability_sql.=" AND si.grade_level_id =".$user_grade_level_id;
        }
        
        $reliability_sql.=" GROUP BY si.grade_level_id,s1.scoring_rubric_attribute_id
                            UNION
                            SELECT esi.event_id,si.grade_level_id,g.name grade_level_name,
                            COUNT(esi.id) paper_count,sa.id scoring_rubric_attribute_id, sa.name scoring_rubric_attribute_name,
                            'adjudicated' count_type
                            FROM event_scoring_item_status esis1
                            JOIN event_scoring_item_status esis2
                            ON esis1.event_scoring_item_id = esis2.event_scoring_item_id
                            JOIN event_scoring_item esi ON esi.id = esis1.event_scoring_item_id AND esi.id = esis2.event_scoring_item_id
                            JOIN scoring_item_score s1 ON s1.event_scoring_item_status_id = esis1.max_scoring_item_score_status_id
                            JOIN scoring_item_score s2 ON s2.event_scoring_item_status_id = esis2.max_scoring_item_score_status_id 
                            AND s1.scoring_rubric_attribute_id = s2.scoring_rubric_attribute_id
                            JOIN scoring_item si ON si.id = esi.scoring_item_id
                            JOIN scoring_rubric_attribute sra ON sra.id = s1.scoring_rubric_attribute_id AND sra.id=s2.scoring_rubric_attribute_id 
                            JOIN scoring_attribute sa ON sa.id=sra.attribute_id
                            JOIN grade_level g ON g.id = si.grade_level_id
                            WHERE event_id=$current_event_id AND component_id=1 AND esis1.scoring_round_number=1 AND esis1.status_id=11
                            AND esis2.scoring_round_number=2 AND esis2.status_id=11 AND ABS(s1.score-s2.score)>=$adjudication_trigger";
        
        if (($user_role_id !=$role_admin_id) && ($user_role_id !=$role_event_leader_id)) {
             $reliability_sql.=" AND si.grade_level_id =".$user_grade_level_id;
        }
        
        $reliability_sql.=" GROUP BY si.grade_level_id,s1.scoring_rubric_attribute_id";
        
        //echo $reliability_sql;
        
        $sth = $dbh->prepare($reliability_sql); 
        $sth->execute();
        $reliability_count = $sth->fetchAll(\PDO::FETCH_ASSOC);
        
        $reliability_count_array_size=sizeof($reliability_count);
 
        $reliability_count_total=0;
        for($r=0;$r<$reliability_count_array_size;$r++){  
            $grade_level= $reliability_count[$r]['grade_level_id'];
            $attribute_id = $reliability_count[$r]['scoring_rubric_attribute_id'];
            $count_type = $reliability_count[$r]['count_type'];
            $grade_level_array[$grade_level]['grade_level_name']=$reliability_count[$r]['grade_level_name'];
            if ($count_type=="total") {
                $reliability_count_array[$grade_level][$attribute_id]['total']= $reliability_count[$r]['paper_count'];
                if (!isset( $reliability_count_array[$grade_level][0]['total'])) {
                    $reliability_count_array[$grade_level][0]['total']=0;
                    $reliability_count_array[$grade_level][0]['paper_count']=$reliability_count[$r]['paper_count'];//total number of double-scored papers
                }
                $reliability_count_array[$grade_level][0]['total'] += $reliability_count_array[$grade_level][$attribute_id]['total'];
                
                if (!isset( $reliability_count_array_attribute[$attribute_id]['total'])) {//keep track of count by attribute id
                    $reliability_count_array_attribute[$attribute_id]['total']=0;
                }
                $reliability_count_array_attribute[$attribute_id]['total'] +=$reliability_count[$r]['paper_count'];
                
                if (!isset($reliability_count_array_total['total'])) {//keep track of count for all attributes(overall) for all grade levels
                    $reliability_count_array_total['total']=0;
                }
                $reliability_count_array_total['total'] +=$reliability_count[$r]['paper_count'];
                  
            } elseif ($count_type="adjudicated") {
                $reliability_count_array[$grade_level][$attribute_id]['adjudicated']= $reliability_count[$r]['paper_count'];
                if (!isset( $reliability_count_array[$grade_level][0]['adjudicated'])) {
                    $reliability_count_array[$grade_level][0]['adjudicated']=0;
                }
                $reliability_count_array[$grade_level][0]['adjudicated'] += $reliability_count_array[$grade_level][$attribute_id]['adjudicated'];
                
                if (!isset( $reliability_count_array_attribute[$attribute_id]['adjudicated'])) { //keep track of count by attribute id
                    $reliability_count_array_attribute[$attribute_id]['adjudicated']=0;
                }
                $reliability_count_array_attribute[$attribute_id]['adjudicated'] +=$reliability_count[$r]['paper_count'];
                
                if (!isset($reliability_count_array_total['adjudicated'])) {//keep track of count for all attributes(overall) for all grade levels
                    $reliability_count_array_total['adjudicated']=0;
                }
                $reliability_count_array_total['adjudicated'] +=$reliability_count[$r]['paper_count'];
            }
            
              
        } 
        
       $reliability_count_array[0]['paper_count']=0;
       foreach ($grade_level_array as $key => $value) {
          $reliability_count_array[0]['paper_count'] +=$reliability_count_array[$key][0]['paper_count'];
       }
       
       //Prompts Report
       
       $grade_level_array_prompt=array();
       $prompt_array=array();
       $reliability_results_by_prompt_count_array=array();
       $reliability_results_by_prompt_array_attribute=array();
       $reliability_results_by_prompt_count_array_attribute=array();
       $reliability_results_by_prompt_count_array_total=array();
       
       $grade_level_array_prompts=array();
       $reliability_sql_by_prompt ="SELECT esi.event_id, si.grade_level_id, g.name grade_level_name,
                                    pr.id prompt_id,pr.name prompt_name, COUNT(esi.id) paper_count,
                                    sa.id scoring_rubric_attribute_id, sa.name scoring_rubric_attribute_name,
                                    'total' count_type
                                    FROM event_scoring_item_status esis1
                                    JOIN event_scoring_item_status esis2 ON esis1.event_scoring_item_id = esis2.event_scoring_item_id
                                    JOIN event_scoring_item esi ON esi.id = esis1.event_scoring_item_id
                                    AND esi.id = esis2.event_scoring_item_id
                                    JOIN scoring_item_score s1 ON s1.event_scoring_item_status_id = esis1.max_scoring_item_score_status_id
                                    JOIN scoring_item_score s2 ON s2.event_scoring_item_status_id = esis2.max_scoring_item_score_status_id
                                    AND s1.scoring_rubric_attribute_id = s2.scoring_rubric_attribute_id
                                    JOIN scoring_item si ON si.id = esi.scoring_item_id
                                    JOIN scoring_rubric_attribute sra ON sra.id = s1.scoring_rubric_attribute_id
                                    AND sra.id = s2.scoring_rubric_attribute_id 
                                    JOIN scoring_attribute sa ON sa.id = sra.attribute_id
                                    JOIN grade_level g ON g.id = si.grade_level_id
                                    JOIN prompt AS pr ON si.prompt_id = pr.id
                                    WHERE event_id = $current_event_id 
                                    AND component_id = 1 AND esis1.scoring_round_number = 1
                                    AND esis1.status_id = 11 AND esis2.scoring_round_number = 2
                                    AND esis2.status_id = 11 AND s2.score IS NOT NULL";
        
        if (($user_role_id !=$role_admin_id) && ($user_role_id !=$role_event_leader_id)) {
             $reliability_sql_by_prompt .=" AND si.grade_level_id =".$user_grade_level_id;
        }
        $reliability_sql_by_prompt .=" GROUP BY si.grade_level_id, si.prompt_id, s1.scoring_rubric_attribute_id
                                    UNION
                                    SELECT esi.event_id, si.grade_level_id, g.name grade_level_name,
                                    pr.id prompt_id, pr.name prompt_name, COUNT(esi.id) paper_count,
                                    sa.id scoring_rubric_attribute_id, sa.name scoring_rubric_attribute_name,
                                    'adjudicated' count_type
                                    FROM event_scoring_item_status esis1
                                    JOIN event_scoring_item_status esis2 ON esis1.event_scoring_item_id = esis2.event_scoring_item_id
                                    JOIN event_scoring_item esi ON esi.id = esis1.event_scoring_item_id
                                    AND esi.id = esis2.event_scoring_item_id
                                    JOIN scoring_item_score s1 ON s1.event_scoring_item_status_id = esis1.max_scoring_item_score_status_id
                                    JOIN scoring_item_score s2 ON s2.event_scoring_item_status_id = esis2.max_scoring_item_score_status_id
                                    AND s1.scoring_rubric_attribute_id = s2.scoring_rubric_attribute_id
                                    JOIN scoring_item si ON si.id = esi.scoring_item_id
                                    JOIN scoring_rubric_attribute sra ON sra.id = s1.scoring_rubric_attribute_id
                                    AND sra.id = s2.scoring_rubric_attribute_id
                                    JOIN scoring_attribute sa ON sa.id = sra.attribute_id
                                    JOIN grade_level g ON g.id = si.grade_level_id
                                    JOIN prompt AS pr ON si.prompt_id = pr.id
                                    WHERE event_id = $current_event_id  
                                    AND component_id = 1 AND esis1.scoring_round_number = 1 AND esis1.status_id = 11
                                    AND esis2.scoring_round_number = 2 AND esis2.status_id = 11 AND ABS(s1.score - s2.score) >= ".$adjudication_trigger;
        if (($user_role_id !=$role_admin_id) && ($user_role_id !=$role_event_leader_id)) {
             $reliability_sql_by_prompt .=" AND si.grade_level_id =".$user_grade_level_id;
        }                               
        $reliability_sql_by_prompt .=" GROUP BY si.grade_level_id, si.prompt_id, s1.scoring_rubric_attribute_id";

        
        //echo $reliability_sql_by_prompt;                          
        $sth = $dbh->prepare($reliability_sql_by_prompt); 
        $sth->execute();
        $reliability_results_by_prompt_count = $sth->fetchAll(\PDO::FETCH_ASSOC);
        
        $reliability_results_by_prompt_array_size=sizeof($reliability_results_by_prompt_count);
        
       // echo "size of array is ".$reliability_results_by_prompt_array_size;
        
        $reliability_results_by_prompt_count_total=0;
        
        for($r=0;$r<$reliability_results_by_prompt_array_size;$r++){  
            $grade_level= $reliability_results_by_prompt_count[$r]['grade_level_id'];
            $prompt_id = $reliability_results_by_prompt_count[$r]['prompt_id'];
            $attribute_id = $reliability_results_by_prompt_count[$r]['scoring_rubric_attribute_id'];
            $count_type = $reliability_results_by_prompt_count[$r]['count_type'];
            $grade_level_array_prompt[$grade_level]['grade_level_name']=$reliability_results_by_prompt_count[$r]['grade_level_name'];
            $prompt_array[$grade_level][$prompt_id]['prompt_name']=$reliability_results_by_prompt_count[$r]['prompt_name'];
            if ($count_type=="total") {
                $reliability_results_by_prompt_count_array[$grade_level][$prompt_id][$attribute_id]['total']= $reliability_results_by_prompt_count[$r]['paper_count'];
                if (!isset( $reliability_results_by_prompt_count_array[$grade_level][$prompt_id][0]['total'])) {
                    $reliability_results_by_prompt_count_array[$grade_level][$prompt_id][0]['total']=0;
                    $reliability_results_by_prompt_count_array[$grade_level][$prompt_id][0]['paper_count']=$reliability_results_by_prompt_count[$r]['paper_count'];//total number of double-scored papers
                }
                $reliability_results_by_prompt_count_array[$grade_level][$prompt_id][0]['total'] += $reliability_results_by_prompt_count_array[$grade_level][$prompt_id][$attribute_id]['total'];
                
                if (!isset( $reliability_results_by_prompt_count_array_attribute[$grade_level][$attribute_id]['total'])) {//keep track of count by attribute id
                    $reliability_results_by_prompt_count_array_attribute[$grade_level][$attribute_id]['total']=0;
                }
                $reliability_results_by_prompt_count_array_attribute[$grade_level][$attribute_id]['total'] +=$reliability_results_by_prompt_count[$r]['paper_count'];
                
                if (!isset($reliability_results_by_prompt_count_array_total[$grade_level]['total'])) {//keep track of count for all attributes(overall) for all grade levels
                    $reliability_results_by_prompt_count_array_total[$grade_level]['total']=0;
                }
                $reliability_results_by_prompt_count_array_total[$grade_level]['total'] +=$reliability_results_by_prompt_count[$r]['paper_count'];
                  
            } elseif ($count_type="adjudicated") {
                $reliability_results_by_prompt_count_array[$grade_level][$prompt_id][$attribute_id]['adjudicated']= $reliability_results_by_prompt_count[$r]['paper_count'];
                if (!isset( $reliability_results_by_prompt_count_array[$grade_level][$prompt_id][0]['adjudicated'])) {
                    $reliability_results_by_prompt_count_array[$grade_level][$prompt_id][0]['adjudicated']=0;
                }
                $reliability_results_by_prompt_count_array[$grade_level][$prompt_id][0]['adjudicated'] += $reliability_results_by_prompt_count_array[$grade_level][$prompt_id][$attribute_id]['adjudicated'];
                
                if (!isset( $reliability_results_by_prompt_count_array_attribute[$grade_level][$attribute_id]['adjudicated'])) { //keep track of count by attribute id
                    $reliability_results_by_prompt_count_array_attribute[$grade_level][$attribute_id]['adjudicated']=0;
                }
                $reliability_results_by_prompt_count_array_attribute[$grade_level][$attribute_id]['adjudicated'] +=$reliability_results_by_prompt_count[$r]['paper_count'];
                
                if (!isset($reliability_results_by_prompt_count_array_total[$grade_level]['adjudicated'])) {//keep track of count for all attributes(overall) for all grade levels
                    $reliability_results_by_prompt_count_array_total[$grade_level]['adjudicated']=0;
                }
                $reliability_results_by_prompt_count_array_total[$grade_level]['adjudicated'] +=$reliability_results_by_prompt_count[$r]['paper_count'];
            }
            
              
        } 
        
       
       foreach ($grade_level_array_prompt as $key => $value) {
          $reliability_results_by_prompt_count_array[$key][0]['paper_count']=0;
          
          foreach ($prompt_array[$key] as $promptkey => $promptvalue) {
           if (isset($reliability_results_by_prompt_count_array[$key][$promptkey][0]['paper_count'])) {
                $reliability_results_by_prompt_count_array[$key][0]['paper_count'] +=$reliability_results_by_prompt_count_array[$key][$promptkey][0]['paper_count'];
           }   
          }
            
       }
       
        return array(
            'grade_level_array' => $grade_level_array,
            'attributes' => $attributes,
            'reliability_count_array' => $reliability_count_array, 
            'reliability_count_array_total' => $reliability_count_array_total, 
            'reliability_count_array_attribute' => $reliability_count_array_attribute, 
            'grade_level_array_prompt' => $grade_level_array_prompt,
            'prompt_array' => $prompt_array,
            'reliability_results_by_prompt_count_array' => $reliability_results_by_prompt_count_array, 
            'reliability_results_by_prompt_count_array_total' => $reliability_results_by_prompt_count_array_total, 
            'reliability_results_by_prompt_count_array_attribute' => $reliability_results_by_prompt_count_array_attribute, 
        );
    }
    
    /**
 
     * @Route("/scorer_reliability", name="eventsite_scorer_reliability")
     * @Template("NwpAssessmentBundle:EventScoringItemStatus:scorer_reliability.html.twig")
     */
    public function eventScorerReliability() {
        $current_event_id = $this->getCurrentEvent();
        
        if ($current_event_id =="" || !(is_numeric($current_event_id))) {
              return $current_event_id; //redirect to index page
        }
        
        if (!$this->checkAccess("list",null,"EventScoringItem")) {
            throw new AccessDeniedException();
        }
        
        //check access, for now just get role, grade level and table info for the user
        $event_capability_array = $this->getUserEventRoleCapabilities();
        $user_role_id = $event_capability_array[$current_event_id][0]['role_id'];
        $user_grade_level_id = $event_capability_array[$current_event_id][0]['grade_level_id'];
        $user_table_id = $event_capability_array[$current_event_id][0]['table_id'];
        
        //get System Roles
        $system_roles_array = $this->getSystemRoles();
        $role_event_leader_id=$system_roles_array['Event Leader'];
        $role_room_leader_id=$system_roles_array['Room Leader'];
        $role_table_leader_id=$system_roles_array['Table Leader'];
        $role_scorer1_id=$system_roles_array['Scorer 1'];
        $role_scorer2_id = $system_roles_array['Scorer 2']; 
        $role_admin_id = $system_roles_array['Admin']; 
        
        $papers_status_count = "";
        
        $grade_level_array=array();
        $reliability_count_array=array();
        $reliability_count_array_attribute=array();
        $reliability_count_array_total=array();
        
        $em = $this->getDoctrine()->getManager();
        
        $current_event = $em->getRepository('NwpAssessmentBundle:Event')->find($current_event_id);
        $scoring_rubric_id=$current_event->getScoringRubric()->getId();
        $adjudication_trigger=$current_event->getAdjudicationTrigger(); 
        
        
        
        #$attributes = $em->getRepository('NwpAssessmentBundle:ScoringAttribute')->findAll();
        
        $queryBuilder = $em->getRepository('NwpAssessmentBundle:ScoringAttribute')
                            ->createQueryBuilder('sa') 
                            ->select('sa')
                            ->innerJoin('NwpAssessmentBundle:ScoringRubricAttribute', 'sra', 'WITH', 'sra.attribute = sa.id')
                            ->innerJoin('NwpAssessmentBundle:ScoringRubric', 'sr', 'WITH', 'sr.id = sra.rubric')
                            ->where('sr.id='.$scoring_rubric_id);  
        
        $query = $queryBuilder->getQuery();
        $attributes= $query->getResult();
          
        
        $dbh= $this->get('database_connection');
        $dbh->beginTransaction();
       
        
        $max_table_sql = "SELECT MAX(table_id) max_table_number,grade_level_id FROM event_user WHERE event_id=".$current_event_id." AND grade_level_id IS NOT NULL GROUP by grade_level_id"; 
        $sth = $dbh->prepare($max_table_sql); 
        $sth->execute();
        $max_table_results_count = $sth->fetchAll(\PDO::FETCH_ASSOC);
        
        $max_table_results_count_array_size=sizeof($max_table_results_count);
        
        
        for($m=0;$m<$max_table_results_count_array_size;$m++){ 
           $grade_level_id = $max_table_results_count[$m]['grade_level_id'];
            $max_table_array[$grade_level_id]['max_table_number']=$max_table_results_count[$m]['max_table_number']; 
        }
        
        $reliability_sql_by_user ="
                                    SELECT
                                    a.event_id,
                                    a.grade_level_id,
                                    eu.table_id,
                                    eu.role_id,
                                    r.name role_name,
                                    gr.name grade_level_name,
                                    u.id user_id,
                                    u.firstname,
                                    u.lastname,
                                    sa.id scoring_rubric_attribute_id,
                                    sa.name scoring_rubric_attribute_name,
                                    SUM(a.total_all_paper_count) total_all_paper_count,
                                    SUM(a.total_double_paper_count) total_double_paper_count,
                                    SUM(a.adjudicated_paper_count) adjudicated_paper_count
                                    FROM
                                    (
                                        SELECT
                                        COUNT(DISTINCT ef.event_scoring_item_id,ef.scoring_round_number) total_all_paper_count,
                                        0 total_double_paper_count,
                                        0 adjudicated_paper_count,
                                        ef.max_score_created_by user_id,
                                        sis.scoring_rubric_attribute_id scoring_rubric_attribute_id,
                                        ef.event_id,
                                        ef.scoring_item_id,
                                        ef.scoring_item_type_id,
                                        ef.grade_level_id,
                                        ef.event_scoring_item_id,
                                        ef.max_score_created_by,
                                        ef.scoring_round_number,
                                        ef.max_scoring_item_score_status_id
                                        FROM
                                        event_scoring_item_status_list_final ef
                                        JOIN scoring_item_score sis ON sis.event_scoring_item_status_id = ef.max_scoring_item_score_status_id
                                        WHERE
                                        ef.event_id = $current_event_id";
                                        if (($user_role_id !=$role_admin_id) && ($user_role_id !=$role_event_leader_id)) {
                                            $reliability_sql_by_user.=" AND ef.grade_level_id =".$user_grade_level_id;
                                        }
                                        $reliability_sql_by_user.=" AND ef.status_id = 11
                                        AND ef.component_id = 1
                                        AND ef.scoring_round_number IN (1,2)
                                        GROUP BY ef.grade_level_id, ef.max_score_created_by, sis.scoring_rubric_attribute_id
                                        UNION
                                        SELECT
                                        0 total_all_paper_count,
                                        COUNT(DISTINCT ef.event_scoring_item_id,ef.scoring_round_number) AS total_double_paper_count,
                                        0 adjudicated_paper_count,
                                        ef.max_score_created_by user_id,
                                        sis.scoring_rubric_attribute_id scoring_rubric_attribute_id,
                                        ef.event_id,
                                        ef.scoring_item_id,
                                        ef.scoring_item_type_id,
                                        ef.grade_level_id,
                                        ef.event_scoring_item_id,
                                        ef.max_score_created_by,
                                        ef.scoring_round_number,
                                        ef.max_scoring_item_score_status_id
                                        FROM
                                        event_scoring_item_status_list_final AS ef
                                        JOIN scoring_item_score AS sis ON sis.event_scoring_item_status_id = ef.max_scoring_item_score_status_id
                                        JOIN event_scoring_item_status AS esis1 ON esis1.event_scoring_item_id = ef.event_scoring_item_id AND esis1.status_id = ef.status_id AND esis1.scoring_round_number = 1
                                        JOIN event_scoring_item_status AS esis2 ON esis2.event_scoring_item_id = ef.event_scoring_item_id AND esis2.status_id = ef.status_id AND esis2.scoring_round_number = 2
                                        JOIN scoring_item_score AS s1 ON s1.event_scoring_item_status_id = esis1.max_scoring_item_score_status_id AND s1.scoring_rubric_attribute_id = sis.scoring_rubric_attribute_id
                                        JOIN scoring_item_score AS s2 ON s2.event_scoring_item_status_id = esis2.max_scoring_item_score_status_id AND s2.scoring_rubric_attribute_id = sis.scoring_rubric_attribute_id
                                        WHERE
                                        ef.event_id = $current_event_id";
                                        if (($user_role_id !=$role_admin_id) && ($user_role_id !=$role_event_leader_id)) {
                                            $reliability_sql_by_user.=" AND ef.grade_level_id =".$user_grade_level_id;
                                        }
                                        $reliability_sql_by_user.=" AND ef.status_id = 11
                                        AND ef.component_id = 1
                                        AND ef.scoring_round_number IN (1,2)
                                        AND ef.scoring_item_type_id = 2
                                        AND s1.score IS NOT NULL AND s2.score IS NOT NULL
                                        GROUP BY ef.grade_level_id, ef.max_score_created_by, sis.scoring_rubric_attribute_id
                                        UNION
                                        SELECT
                                        0 total_all_paper_count,
                                        0 total_double_paper_count,
                                        COUNT(DISTINCT ef.event_scoring_item_id,ef.scoring_round_number) adjudicated_paper_count,	
                                        ef.max_score_created_by user_id,
                                        sis.scoring_rubric_attribute_id scoring_rubric_attribute_id,
                                        ef.event_id,
                                        ef.scoring_item_id,
                                        ef.scoring_item_type_id,
                                        ef.grade_level_id,
                                        ef.event_scoring_item_id,
                                        ef.max_score_created_by,
                                        ef.scoring_round_number,
                                        ef.max_scoring_item_score_status_id
                                        FROM
                                        event_scoring_item_status_list_final AS ef
                                        JOIN scoring_item_score AS sis ON sis.event_scoring_item_status_id = ef.max_scoring_item_score_status_id
                                        JOIN event_scoring_item_status AS esis1 ON esis1.event_scoring_item_id = ef.event_scoring_item_id AND esis1.status_id = ef.status_id AND esis1.scoring_round_number = 1
                                        JOIN event_scoring_item_status AS esis2 ON esis2.event_scoring_item_id = ef.event_scoring_item_id AND esis2.status_id = ef.status_id AND esis2.scoring_round_number = 2
                                        LEFT JOIN event_scoring_item_status AS esis3 ON esis3.event_scoring_item_id = ef.event_scoring_item_id AND esis3.status_id = ef.status_id AND esis3.scoring_round_number = 3	
                                        JOIN scoring_item_score AS s1 ON s1.event_scoring_item_status_id = esis1.max_scoring_item_score_status_id AND s1.scoring_rubric_attribute_id = sis.scoring_rubric_attribute_id
                                        JOIN scoring_item_score AS s2 ON s2.event_scoring_item_status_id = esis2.max_scoring_item_score_status_id AND s2.scoring_rubric_attribute_id = sis.scoring_rubric_attribute_id
                                        LEFT JOIN scoring_item_score AS s3 ON s3.event_scoring_item_status_id = esis3.max_scoring_item_score_status_id AND s3.scoring_rubric_attribute_id = sis.scoring_rubric_attribute_id
                                        WHERE
                                        ef.event_id = $current_event_id";
                                        if (($user_role_id !=$role_admin_id) && ($user_role_id !=$role_event_leader_id)) {
                                            $reliability_sql_by_user.=" AND ef.grade_level_id =".$user_grade_level_id;
                                        }
                                        $reliability_sql_by_user.=" AND ef.status_id = 11
                                        AND ef.component_id = 1
                                        AND ef.scoring_round_number IN (1,2)
                                        AND ef.scoring_item_type_id = 2
                                        AND ABS(s1.score-s2.score)>= $adjudication_trigger
                                        AND (s3.score IS NULL OR ABS(s1.score-s3.score)>= $adjudication_trigger OR ABS(s2.score-s3.score)>= $adjudication_trigger)
                                        GROUP BY ef.grade_level_id, ef.max_score_created_by, sis.scoring_rubric_attribute_id
                                        ) AS a
                                        JOIN fos_user_user u ON a.max_score_created_by = u.id
                                        JOIN event_user eu ON eu.user_id = u.id AND eu.event_id = a.event_id
                                        JOIN grade_level gr ON gr.id = a.grade_level_id
                                        JOIN scoring_rubric_attribute sra ON sra.id = a.scoring_rubric_attribute_id
                                        JOIN scoring_attribute sa ON sa.id=sra.attribute_id
                                        join role r on r.id=eu.role_id
                                        WHERE
                                        eu.role_id IN (8,9,10)
                                        GROUP BY a.grade_level_id, a.user_id, a.scoring_rubric_attribute_id;
                                        ";
        
        
        //echo $reliability_sql_by_user;
        
        $sth = $dbh->prepare($reliability_sql_by_user); 
        $sth->execute();
        $reliability_results_by_user_count = $sth->fetchAll(\PDO::FETCH_ASSOC);
        
        $reliability_results_by_user_array_size=sizeof($reliability_results_by_user_count);
        
        for($ru=0;$ru<$reliability_results_by_user_array_size;$ru++){ 
                $grade_level_id = $reliability_results_by_user_count[$ru]['grade_level_id'];
                $table_id = $reliability_results_by_user_count[$ru]['table_id'];
                $user_id = $reliability_results_by_user_count[$ru]['user_id'];
                $role_name = $reliability_results_by_user_count[$ru]['role_name'];
                $attribute =$reliability_results_by_user_count[$ru]['scoring_rubric_attribute_id'];
                $total_all_paper_count = $reliability_results_by_user_count[$ru]['total_all_paper_count'];
                $total_double_paper_count = $reliability_results_by_user_count[$ru]['total_double_paper_count'];
                $adjudicated_paper_count = $reliability_results_by_user_count[$ru]['adjudicated_paper_count'];
                $firstname = $reliability_results_by_user_count[$ru]['firstname'];
                $lastname = $reliability_results_by_user_count[$ru]['lastname'];
                $grade_level_array[$grade_level_id]['grade_level_name']=$reliability_results_by_user_count[$ru]['grade_level_name'];
                $reliability_results_by_user[$grade_level_id][$table_id][$user_id][0]['fullname']=$firstname." ".$lastname;
                $reliability_results_by_user[$grade_level_id][$table_id][$user_id][0]['role_name']=$role_name;
                $reliability_results_by_user[$grade_level_id][$table_id][$user_id][0]['total_all']=$total_all_paper_count;
                $reliability_results_by_user[$grade_level_id][$table_id][$user_id][0]['total_double']=$total_double_paper_count;
                $reliability_results_by_user[$grade_level_id][$table_id][$user_id][$attribute]['adjudicated']=$adjudicated_paper_count;
        }
       
        //var_dump($reliability_results_by_user);
        return array(
            'grade_level_array' => $grade_level_array,
            'max_table_array' => $max_table_array,
            'attributes' => $attributes,
            'reliability_results_by_user' => $reliability_results_by_user,
        );
    }

    /**
     * Displays a form to create a new EventScoringItemStatus entity.
     *
     * @Route("/eventscoringitemstatus/assigned", name="eventsite_eventscoringitemstatus_assigned")
     * @Template("NwpAssessmentBundle:EventScoringItemStatus:new.html.twig")
     */
    public function assignPaper() {
        //is current event set?
        $current_event_id = $this->getCurrentEvent();
       
        $component_id=1;
        
        if ($current_event_id =="" || !(is_numeric($current_event_id))) {
              return $current_event_id; //redirect to index page
        }
        //before allowing to grab New Paper, requery event role user rights in case their role has changed during the session
        $request = $this->getRequest();
        $session = $request->getSession();
        if ($session->has("EventRoleUserSession")) {
            $session->remove('EventRoleUserSession');
        }
        if ($session->has("StatusPathwaysUserSession")) {
           $session->remove('StatusPathwaysUserSession'); 
        }
        if ($session->has("StatusListQueueUserSession")) {
            $session->remove('StatusListQueueUserSession');
        }
        
        
        //check access, for now just get role, grade level and table info for the user
        $event_capability_array = $this->getUserEventRoleCapabilities();
        $user_role_id = $event_capability_array[$current_event_id][0]['role_id'];
        $user_grade_level_id = $event_capability_array[$current_event_id][0]['grade_level_id'];
        $user_table_id = $event_capability_array[$current_event_id][0]['table_id'];
        $event_type_id = $event_capability_array[$current_event_id][0]['event_type_id'];
        
        $em = $this->getDoctrine()->getManager();
        //get random paper from event_scoring_item table
        $error_msg="";
        $event_scoring_item="";
        $event_scoring_item_id="";
        $user_id=$this->container->get('security.context')->getToken()->getUser()->getId();
        
        $status_ready = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Ready'))->getId();
        $status_assigned = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Assigned'))->getId();
        
       //get System Roles
        $system_roles_array = $this->getSystemRoles();
        $role_event_leader_id=$system_roles_array['Event Leader'];
        $role_room_leader_id=$system_roles_array['Room Leader'];
        $role_table_leader_id=$system_roles_array['Table Leader'];
        $role_scorer1_id=$system_roles_array['Scorer 1'];
        $role_scorer2_id = $system_roles_array['Scorer 2']; 
        $role_admin_id = $system_roles_array['Admin']; 
        
        $event_user_id="";
        
        //Asynchronous Scoring settings
        $block_quota_reached_access=true; 
        $user_block_quota_reached_access=true;
        $error_msg_block_quota="";
        $error_msg_user_block_quota="";
        $paper_count_total=array();
        $block_capability_array=array();
        $user_block_capability_array=array();

        if ($user_role_id !=$role_admin_id) { //everyone but Admin should be in event_user table as an attendee
            $event_user_id=$event_capability_array[$current_event_id][0]['id'];
        }

        
        //Set check_status_assigned to true for checkStatusAccess function to check whether the user already has papers assigned (if so, they cannot get a new paper)
        $status_assigned_access = $this->checkStatusAccess($component_id,$user_role_id,"create", null,"EventScoringItemStatus",$status_ready,$status_assigned,$current_event_id,$check_status_exists=true);
      
        //Asynchronous Scoring Processing
        if ($event_type_id ==2) { //asynchronous event processing
            //get total papers (reads) in event by grade level
            $paper_count_total = $this->getPaperTotalsByGradeLevel($user_role_id,$user_grade_level_id, $current_event_id);
               
            //get total papers already assigned by grade level
            $paper_count_grade_level_assigned_array=$this->getPapersAssignedByGradeLevel($user_role_id,$user_grade_level_id, $current_event_id,$status_assigned);
               
            //build block capability array by grade level
            $block_capability_array = $this->getBlocksCapability($user_role_id,$user_grade_level_id,$user_table_id,$current_event_id, $paper_count_total,$paper_count_grade_level_assigned_array);
                  
             //now check individual stats and build user block capability array by event_user_id                
             if ($event_user_id !="") {
                $eu_record = $em->getRepository('NwpAssessmentBundle:EventUser')->findBy(array('id' => $event_user_id)); 
                    if ($eu_record) {
                        $target_percent_user=$eu_record[0]->getTarget();
                        $max_block_user=$eu_record[0]->getMaxBlock();
                       //get total papers (reads) in event that have already been assigned to scorer
                        $paper_count_total_user_assigned=$this->getPapersAssignedUser($event_user_id,$current_event_id,$status_assigned);
                        //set user block statistics
                        if (((isset($paper_count_total[$user_grade_level_id])) && ($paper_count_total[$user_grade_level_id]>0))
                            && (isset($block_capability_array[$user_grade_level_id])))
                        {
                            $user_block_capability_array[$event_user_id] = $this->getUserBlocksCapability($event_user_id,$user_grade_level_id,$user_role_id,$target_percent_user,$max_block_user,$paper_count_total,$paper_count_total_user_assigned,$block_capability_array);
                        }
                    }
            }
               
            //check Rules 2 and 3 
            $block_quota_reached_access = $this->checkBlockQuotaAccess($event_user_id, $user_role_id,$user_block_capability_array,$role_scorer1_id,$role_scorer2_id);
            $user_block_quota_reached_access = $this->checkUserBlockQuotaAccess($event_user_id, $user_role_id,$user_block_capability_array,$role_scorer1_id,$role_scorer2_id);
        } //end of Asynchronous scoring processing
        
        if (($status_assigned_access==false) || ($block_quota_reached_access==false) || ($user_block_quota_reached_access==false))  { //user does not have the right to create this status, redirect to no access page
           throw new AccessDeniedException(); 
        }
           
        $current_event = $em->getRepository('NwpAssessmentBundle:Event')->find($current_event_id);
        $second_scoring_table_trigger=$current_event->getsecondScoringTableTrigger();  
        
        try {
            $dbh= $this->get('database_connection');
            $dbh->beginTransaction();
            
            //get # of tables existing in user's room
            //used to decide whether second scoring should be routed to different table than user's table
            if ($user_table_id !="") {
                $max_table_sql = "SELECT MAX(table_id) max_table_number FROM event_user WHERE event_id=".$current_event_id." AND grade_level_id =".$user_grade_level_id; 
                $sth = $dbh->prepare($max_table_sql); 
                $sth->execute();
                $max_table_results_count = $sth->fetchColumn();
            }
            $table_leader_event_user_id="";
      
            if (($user_role_id==$role_scorer1_id) || ($user_role_id==$role_scorer2_id)) {
                $table_leader_result = $this->getTableLeader($user_id,$current_event_id,$role_table_leader_id);
                if ($table_leader_result) {
                    $table_leader_event_user_id = $table_leader_result [0]->getId();
                }
            }
            
            //First check if user's grade level has blocks
            $grade_level_block_sql = "SELECT * FROM event_grade_level_block WHERE event_id=$current_event_id AND grade_level_id=$user_grade_level_id";
            $sth = $dbh->prepare($grade_level_block_sql); 
            $sth->execute();
            $grade_level_block_exists = $sth->fetchAll(\PDO::FETCH_ASSOC);
                
            //first get the double-scored papers ready for 2nd read (scoring_round_number).
            //only Scorers and Table Leaders can score 2nd read papers, Room Leaders CANNOT socre 2nd read double-scored papers.
            if (($user_role_id==$role_table_leader_id) || ($user_role_id==$role_scorer1_id) || ($user_role_id==$role_scorer2_id)) {
               
                $select_second_read_sql = " SELECT esi.id AS event_scoring_item_id, si.id AS scoring_item_id,
                                    esi.status_id AS current_status,esi.scoring_round_number,esi.read_number,si.prompt_id
                                    FROM event_scoring_item esi
                                    JOIN scoring_item si ON esi.scoring_item_id=si.id";
                                    if (count($grade_level_block_exists) != 0) {
                                        $select_second_read_sql.=" JOIN event_grade_level_block b ON b.event_id=esi.event_id AND b.grade_level_id=si.grade_level_id
                                        JOIN event_grade_level_block_prompt p ON p.event_grade_level_block_id=b.id
                                        AND p.prompt_id=si.prompt_id";
                                    }
                                     
                                    $select_second_read_sql.=" WHERE 
                                    si.grade_level_id=$user_grade_level_id
                                    AND ((scoring_item_type_id=2 AND esi.scoring_round_number=2) OR (esi.read_number>1))
                                    AND esi.status_id=$status_ready 
                                    AND esi.event_id=$current_event_id AND component_id=1";
                                    if (count($grade_level_block_exists) != 0) {
                                        if (($event_type_id==2) && (isset($user_block_capability_array[$event_user_id])) && ($user_block_capability_array[$event_user_id][0]['user_current_block']!=null)) {
                                            $select_second_read_sql.= " AND b.block_id = ".$user_block_capability_array[$event_user_id][0]['user_current_block'];
                                        } 
                                        $select_second_read_sql.=" AND b.is_active=1";
                                        if ($user_table_id !=""){
                                            $select_second_read_sql.=" AND p.table_id=".$user_table_id;
                                        }
                                    }
                                    
                                 
                $select_second_read_sql.=" AND esi.id NOT IN
                  (
                    SELECT esi2.id
                    FROM event_scoring_item esi2 JOIN scoring_item si2 ON esi2.scoring_item_id=si2.id
                    JOIN event_scoring_item_status ess ON ess.event_scoring_item_id=esi2.id
                    JOIN event_user eu ON eu.event_id=esi2.event_id AND eu.user_id = ess.assigned_to
                    WHERE si2.grade_level_id=$user_grade_level_id AND
                    ((scoring_item_type_id=2 AND esi2.scoring_round_number=2) OR (esi2.read_number>1))
                    AND esi2.status_id=$status_ready 
                    AND esi2.event_id=$current_event_id AND component_id=1 
                    AND ess.status_id=$status_assigned AND (ess.assigned_to=$user_id
                     
                ";
                if (($user_table_id !="") && ($max_table_results_count >=$second_scoring_table_trigger)) {
                    $select_second_read_sql.=" OR eu.table_id =".$user_table_id;
                } 
                $select_second_read_sql.="))"
                    ;
                $select_second_read_sql.=" AND esi.id NOT IN
                         (SELECT esig.event_scoring_item_id FROM event_user_grouping eug 
                          JOIN event_scoring_item_grouping esig ON eug.grouping_id=esig.grouping_id
                          WHERE ";
                 if ($table_leader_event_user_id!="") {
                    $select_second_read_sql.="("; 
                }  
         
                $select_second_read_sql.= "eug.event_user_id=".$event_user_id;
            
                if ($table_leader_event_user_id!="") {
                    $select_second_read_sql.=" OR eug.event_user_id=".$table_leader_event_user_id.")";
                }
                $select_second_read_sql.= ")";
                 
                
                $select_second_read_sql.=" ORDER BY RAND() LIMIT 1 FOR UPDATE";
                //echo "<br>second read SQL:".$select_second_read_sql."<br><br>";
                //die();
               
                $sth = $dbh->prepare($select_second_read_sql); 
                $sth->execute();
                $event_scoring_item = $sth->fetchAll(\PDO::FETCH_ASSOC);
            }
            
             //if $select_second_read_sql comes back as 0, select single-scored and papers double-scored papers ready for 1st read
            if (($event_scoring_item==null) || ($event_scoring_item=="")) { 
               
                $select_sql="SELECT esi.id AS event_scoring_item_id, si.id AS scoring_item_id,esi.scoring_round_number,esi.read_number,si.prompt_id 
                         FROM event_scoring_item esi
                         JOIN scoring_item si ON esi.scoring_item_id=si.id";
                         if (count($grade_level_block_exists) != 0) {
                            $select_sql.=" JOIN event_grade_level_block b ON b.event_id=esi.event_id AND b.grade_level_id=si.grade_level_id
                                          JOIN event_grade_level_block_prompt p ON p.event_grade_level_block_id=b.id
                                          AND p.prompt_id=si.prompt_id ";
                         }   
                         $select_sql.=" WHERE si.grade_level_id=$user_grade_level_id
                                       AND ((scoring_item_type_id=1 OR (scoring_item_type_id=2 AND esi.scoring_round_number=1))
                                       AND (esi.read_number=1))
                                       AND status_id=$status_ready AND
                                       esi.event_id=$current_event_id  AND component_id=1";
                         if (count($grade_level_block_exists) != 0) {
                               if (($event_type_id==2) && (isset($user_block_capability_array[$event_user_id])) && ($user_block_capability_array[$event_user_id][0]['user_current_block']!=null)) {
                                    $select_sql.= " AND b.block_id = ".$user_block_capability_array[$event_user_id][0]['user_current_block'];
                               }     
                               $select_sql.=" AND b.is_active=1"; 
                                if ($user_table_id !="") {
                                    $select_sql.=" AND p.table_id=".$user_table_id;
                                }
                         }
                         
                 
                         $select_sql.=" AND esi.id NOT IN
                            (SELECT esig.event_scoring_item_id FROM event_user_grouping eug 
                            JOIN event_scoring_item_grouping esig ON eug.grouping_id=esig.grouping_id
                            WHERE ";
                        if ($table_leader_event_user_id!="") {
                            $select_sql.="("; 
                        }  
         
                        $select_sql.= "eug.event_user_id=".$event_user_id;
            
                        if ($table_leader_event_user_id!="") {
                            $select_sql.=" OR eug.event_user_id=".$table_leader_event_user_id.")";
                        }
                        $select_sql.= ")";
                 
                        //echo "<br>SQL: first read SQL".$select_sql."<br><br>";
                        $select_sql.=" ORDER BY RAND() LIMIT 1 FOR UPDATE";
                        
                        //die();
                
                        $sth = $dbh->prepare($select_sql); //select
                        $sth->execute();
                        $event_scoring_item = $sth->fetchAll(\PDO::FETCH_ASSOC);
            }
            
           
           
           if (($event_scoring_item !="") && ($event_scoring_item !=null)) {
                $event_scoring_item_id =  $event_scoring_item[0]['event_scoring_item_id'];
                $scoring_item_id=$event_scoring_item[0]['scoring_item_id'];
                $scoring_round_number=$event_scoring_item[0]['scoring_round_number'];
                $read_number=$event_scoring_item[0]['read_number'];
                
                $insert_status_sql="INSERT INTO event_scoring_item_status(event_scoring_item_id,status_id,scoring_round_number, read_number,created_by,assigned_to,time_created)
                            VALUES (".$event_scoring_item_id.",".$status_assigned.",".$scoring_round_number.",".$read_number.",".$user_id.",".$user_id.",now())";
                
                $dbh->exec($insert_status_sql);  //insert status
                
                $previous_scoringitemstatus=$dbh->lastInsertId('id');
                
                $update_sql= "UPDATE event_scoring_item SET status_id = ".$status_assigned.", max_event_scoring_item_status_id = ".$previous_scoringitemstatus." WHERE id=".$event_scoring_item_id;  
                $dbh->exec($update_sql);  //update status in event_scoring_item table
                $dbh->commit();
           } else {
              $dbh->rollback();
              $error_msg = "There are no papers available for scoring at this time.  Please contact the Event Leader.";
           }
        } catch (Exception $e){
            $dbh->rollback();
            $error_msg = "A Database error occurred and a paper could not be assigned to you.  Please contact the Event Leader.";
        }    
        $dbh = NULL;
        
        if ($error_msg !="") {
            $this->get('session')->getFlashBag()->add('error', $error_msg);
            return $this->redirect($this->generateUrl('eventsite_eventscoringitemstatus')); 
        }
        return $this->redirect($this->generateUrl('eventsite_eventscoringitemstatus_new', array('previous_scoringitemstatus' => $previous_scoringitemstatus)));   
          
    }

    /**
    * Create filter form and process filter request.
    *
    */
    protected function filter()
    {
        //is current event set?
        $current_event_id = $this->getCurrentEvent();
       
        if ($current_event_id =="" || !(is_numeric($current_event_id))) {
            return $current_event_id; //redirect to index page
         }
        $event_capability_array = $this->getUserEventRoleCapabilities();
        $user_role_id = $event_capability_array[$current_event_id][0]['role_id'];
        $user_grade_level_id = $event_capability_array[$current_event_id][0]['grade_level_id'];
        $user_table_id = $event_capability_array[$current_event_id][0]['table_id'];
        
        //get System Roles
        $system_roles_array = $this->getSystemRoles();
        $role_event_leader_id=$system_roles_array['Event Leader'];
        $role_room_leader_id=$system_roles_array['Room Leader'];
        $role_table_leader_id=$system_roles_array['Table Leader'];
        $role_scorer1_id=$system_roles_array['Scorer 1'];
        $role_scorer2_id = $system_roles_array['Scorer 2']; 
        $role_admin_id=$system_roles_array['Admin']; 
        
        $status_capability_array = $this->getUserStatusListQueue($user_role_id);
        $url =  $this->getRequest()->getPathInfo();
        
        $component_id=1; //default to 1
        if (strpos($url, "/calibration")!== false) {  //The url of the page designates that it's the Recent Papers reporting page
            $component_id=2;
            $filter_name = "EventScoringItemStatusControllerCalibrationFilter";
        }
        
        $Ids="";
        foreach ($status_capability_array as $sc) {
            foreach ($sc as $i) {
                if (($i['action_name']=="list") && ($i['component_id']==$component_id)) {
                    $Ids.=$i['status_id'].",";
                }
            }             
        } 
       
        $Ids = substr($Ids, 0, -1); //strip last comma
    
        if ($Ids =="") { //user does not have the right to access this status, redirect to no access page
           throw new AccessDeniedException(); 
        }
        
        
        //Set filter session based on whether this is the Queue or Recent Papers Reporting Page
        if ($component_id==1) {
            $reporting=false;
            $filter_name = "EventScoringItemStatusControllerFilter"; //Assume this is the Queue, not Recent Papers reporting page    

            if (strpos($url, "/reporting")!== false) {  //The url of the page designates that it's the Recent Papers reporting page
                $reporting=true;
                $filter_name = "EventScoringItemStatusControllerReportingFilter";
            }
        }
        
         
        $request = $this->getRequest();
        $session = $request->getSession();
        
        $user_id=$this->container->get('security.context')->getToken()->getUser()->getId();
        
        $em = $this->getDoctrine()->getManager();
        
        
         
        $filterForm = $this->createForm(new EventScoringItemStatusFilterType($Ids,$current_event_id,$component_id,$user_role_id,$user_grade_level_id,$user_table_id,$role_scorer1_id,$role_scorer2_id,$role_table_leader_id,$role_room_leader_id,$role_event_leader_id,$role_admin_id));
        
        $queryBuilder=$this->getEventPapers($component_id);
                        
        // Reset filter
        if ($request->getMethod() == 'POST' && $request->get('filter_action') == 'reset') {
            $session->remove($filter_name);
        }
    
        // Filter action
        if ($request->getMethod() == 'POST' && $request->get('filter_action') == 'filter') {
            // Bind values from the request
            $filterForm->bind($request);

            if ($filterForm->isValid()) {
                // Build the query from the given form object
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
                // Save filter to session
                $filterData = $filterForm->getData();
                $session->set($filter_name, $filterData);
            } else {
                //Filter was probably submitted with empty values, remove session info for the filter, so that the results are not filtered.
                $session->remove($filter_name);
            }
        } else {
            // Get filter from session
            if ($session->has($filter_name)) {
                $filterData = $session->get($filter_name);
                //this code fixes "Entities passed to the choice field must be managed" symfony error message  
                foreach ($filterData as $key => $filter) { 
                    if (is_object($filter)) {
                        $filterData[$key] = $em->merge($filter);
                    }
                }
                //
                $filterForm = $this->createForm(new EventScoringItemStatusFilterType($Ids,$current_event_id,$component_id,$user_role_id,$user_grade_level_id,$user_table_id,$role_scorer1_id,$role_scorer2_id,$role_table_leader_id,$role_room_leader_id,$role_event_leader_id,$role_admin_id), $filterData);
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
            }
        }
        
        if (isset($filterData)) {
             $this->get('session')->getFlashBag()->add('info', 'flash.filter.queue.success');
        }
    
        return array($filterForm, $queryBuilder);
    }


    protected function paginatorArray($entities)
    {
        // Paginator
        $request = $this->getRequest();
        $routeName = $request->get('_route');
        
        $adapter = new ArrayAdapter($entities);
        $pagerfanta = new Pagerfanta($adapter);
        $currentPage = $this->getRequest()->get('page', 1);
        $pagerfanta->setCurrentPage($currentPage);
        $entities = $pagerfanta->getCurrentPageResults();
    
        // Paginator - route generator
        $me = $this;
        $routeGenerator = function($page) use ($routeName, $me)
        {
            return $me->generateUrl($routeName, array('page' => $page));
        };
    
        // Paginator - view
        $translator = $this->get('translator');
        $view = new TwitterBootstrapView();
        $pagerHtml = $view->render($pagerfanta, $routeGenerator, array(
            'proximity' => 3,
            'prev_message' => $translator->trans('views.index.pagprev', array(), 'JordiLlonchCrudGeneratorBundle'),
            'next_message' => $translator->trans('views.index.pagnext', array(), 'JordiLlonchCrudGeneratorBundle'),
        ));
    
        return array($entities, $pagerHtml);
    }
    
    /**
    * Create filter form and process filter request.
    *
    */
    protected function getEventPapers($component_id,$isAlert=null) {
        
        //is current event set?
        $current_event_id = $this->getCurrentEvent();
       
        if ($current_event_id =="" || !(is_numeric($current_event_id))) {
            return $current_event_id; //redirect to index page
         }
        $event_capability_array = $this->getUserEventRoleCapabilities();
        $user_role_id = $event_capability_array[$current_event_id][0]['role_id'];
        $user_grade_level_id = $event_capability_array[$current_event_id][0]['grade_level_id'];
        $user_table_id = $event_capability_array[$current_event_id][0]['table_id'];
        
        //get System Roles
        $system_roles_array = $this->getSystemRoles();
        $role_event_leader_id=$system_roles_array['Event Leader'];
        $role_room_leader_id=$system_roles_array['Room Leader'];
        $role_table_leader_id=$system_roles_array['Table Leader'];
        $role_scorer1_id=$system_roles_array['Scorer 1'];
        $role_scorer2_id = $system_roles_array['Scorer 2']; 
        $role_admin_id=$system_roles_array['Admin']; 
        
        
        $event_user_id="";
        $event_scoring_items_excluded="";
        
        if ($user_role_id !=$role_admin_id) { //everyone but Admin should be in event_user table as an attendee
            $event_user_id=$event_capability_array[$current_event_id][0]['id'];
        } 

        if ($event_user_id !="") {
            $event_scoring_items_excluded=$this->getEventScoringItemsExcluded($event_user_id, $user_role_id, $current_event_id);
        }
        
        $url =  $this->getRequest()->getPathInfo();
        
        if ($component_id==1) {
            $reporting=false;
              
            if (strpos($url, "/reporting")!== false) {  //The url of the page designates that it's the Recent Papers reporting page
                $reporting=true;
            }
        }
        
        $em = $this->getDoctrine()->getManager();
        
        if ($component_id==2) {
              $queryBuilder = $em->getRepository('NwpAssessmentBundle:EventScoringItem')
                            ->createQueryBuilder('esi') 
                            ->select('esi.id id, IDENTITY(si.prompt) promptId, p.name as promptName,
                              IDENTITY(esi.scoringItem) scoringItem,
                              IDENTITY(esu.status) status, 
                              (case when s.name is null then \'Ready\' else s.name end) statusName, 
                              IDENTITY(si.gradeLevel) gradeLevelId, g.name as gradeLevelName,sa.name actionName'     
                                    )
                            ->leftJoin('NwpAssessmentBundle:EventScoringItemStatusListByUser','esu',
                                                 \Doctrine\ORM\Query\Expr\Join::WITH,
                                               'esu.eventScoringItem = esi.id and esu.createdBy='.$this->container->get('security.context')->getToken()->getUser()->getId())
                           ->Join('NwpAssessmentBundle:ScoringItem','si',
                                                 \Doctrine\ORM\Query\Expr\Join::WITH,
                                               'esi.scoringItem = si.id')
                           ->Join('NwpAssessmentBundle:GradeLevel','g',
                                                 \Doctrine\ORM\Query\Expr\Join::WITH,
                                               'si.gradeLevel = g.id')
                          ->Join('NwpAssessmentBundle:Prompt','p',
                                                 \Doctrine\ORM\Query\Expr\Join::WITH,
                                               'si.prompt = p.id')
                           ->leftJoin('NwpAssessmentBundle:ScoringItemStatus','s',
                                                 \Doctrine\ORM\Query\Expr\Join::WITH,
                                               'esu.status = s.id')
                           ->leftJoin('NwpAssessmentBundle:ScoringItemStatusRoleCapability', 'rc', 'WITH', '(rc.status =esu.status OR (esu.status IS NULL AND rc.status =1)) AND rc.component=esi.component')
                           ->leftJoin('NwpAssessmentBundle:SystemAction', 'sa', 'WITH', 'sa.id = rc.action')
                         
                           ->where('esi.component=2')
                           ->andWhere('esi.event='.$current_event_id)
                           ->andWhere('rc.role='.$user_role_id)
                           ->AndWhere('sa.name=\'edit\' or sa.name=\'show\'');
                                  
                           if (($user_role_id!=$role_event_leader_id) && ($user_role_id!=$role_admin_id)) {
                                 $queryBuilder->andWhere('si.gradeLevel='.$user_grade_level_id);
                           }
                           if ($event_scoring_items_excluded!='') {
                                $queryBuilder->andWhere('esi.id not in ('.$event_scoring_items_excluded.')');
                           }
                           $queryBuilder->orderBy('esi.id', 'ASC');
               ; 
        
        } else {
            $queryBuilder =$em->getRepository('NwpAssessmentBundle:EventScoringItemStatusList')
                          ->createQueryBuilder('esu')
                          ->select('esu')
                          ->innerJoin('NwpAssessmentBundle:ScoringItemStatusRoleCapability', 'rc', 'WITH', 'rc.status = esu.status AND rc.component=esu.component')
                          ->innerJoin('NwpAssessmentBundle:SystemAction', 'sa', 'WITH', 'sa.id = rc.action');
                         //if this grade level has prompt for the event, uncomment the following lines
                         // $queryBuilder->Join('NwpAssessmentBundle:EventGradeLevelBlock','b',
                         //                        \Doctrine\ORM\Query\Expr\Join::WITH,
                         //                      'b.event=esu.event and b.gradeLevel=esu.gradeLevelId')
                         //               ->Join('NwpAssessmentBundle:EventGradeLevelBlockPrompt','p',
                          //                       \Doctrine\ORM\Query\Expr\Join::WITH,
                          //                     'p.eventGradeLevelBlock=b.id and p.prompt=esu.prompt');
                          if (($user_role_id==$role_scorer1_id) || ($user_role_id==$role_scorer2_id)) {
                              if ($reporting==true) {
                                  $queryBuilder->where('esu.statusAssignedAssignedTo='.$this->container->get('security.context')->getToken()->getUser()->getId()); 
                              } else {
                                  $queryBuilder->where('esu.assignedTo='.$this->container->get('security.context')->getToken()->getUser()->getId()); 
                              }     
                          }   else {  //al other roles besides Scorer 1 and Scorer 2
                                $queryBuilder->where('esu.assignedTo='.$this->container->get('security.context')->getToken()->getUser()->getId().' AND rc.subrole=2 AND rc.structure=5')
                                       ->orWhere('esu.assignedTo!='.$this->container->get('security.context')->getToken()->getUser()->getId().' AND esu.assignedTo is NOT NULL AND rc.subrole=2 AND rc.structure!=5')
                                        ->orWhere('esu.assignedTo IS NULL AND rc.subrole=1'); 
                          }
                          
                          if ($user_role_id==$role_room_leader_id) {
                               $queryBuilder->AndWhere('esu.gradeLevelCreated='.$user_grade_level_id.' OR esu.gradeLevelAssigned='.$user_grade_level_id);    
                          } else if ($user_role_id==$role_table_leader_id) {
                              $queryBuilder->AndWhere('(esu.gradeLevelCreated='.$user_grade_level_id.' AND esu.tableIdCreated='.$user_table_id.') OR
                                              (esu.gradeLevelAssigned='.$user_grade_level_id.' AND esu.tableIdAssigned='.$user_table_id.')'); 
                          }
                     
                           if ($reporting==true) { //Recent Papers
                               $queryBuilder->AndWhere('sa.name=\'show\'');
                               if ($user_role_id==$role_room_leader_id) {
                                    $queryBuilder->andWhere('(esu.scoringItemType=1) OR (esu.scoringItemType=2 AND esu.maxScoringRoundNumber=3 and esu.maxStatus=11) OR (esu.scoringItemType=2 and esu.maxScoringRoundNumber=2 and esu.maxStatus=11)');
                               } 
                           } else {//Queue
                               $queryBuilder->AndWhere('sa.name=\'edit\'');
                           }
                           if ($isAlert != null) {
                                 $queryBuilder->andWhere('esu.isAlert=1');
                           }
                           $queryBuilder->andWhere('esu.event='.$current_event_id);
                           $queryBuilder->andWhere('rc.role='.$user_role_id);
                           $queryBuilder->andWhere('esu.component=1');
                           
                           if ($event_scoring_items_excluded!='') {
                                $queryBuilder->andWhere('esu.eventScoringItem not in ('.$event_scoring_items_excluded.')');
                           }
                           
                           if ($reporting==true) { //Recent Papers
                               $queryBuilder->orderBy('esu.id', 'DESC');
                               #$queryBuilder->orderBy('esu.eventScoringItem,esu.id', 'DESC');
                           } else { //Queue
                               $queryBuilder->add('orderBy','esu.status DESC, esu.id ASC');
                           }
                          
                ;
        
        }
        #echo $queryBuilder->getDQL();
        return $queryBuilder;
    }        
    
    /**
     * Finds and displays a EventScoringItemStatus entity.
     *
     * @Route("/eventscoringitemstatus/{id}/show", name="eventsite_eventscoringitemstatus_show")
     * @Route("/reporting/{id}/show", name="eventsite_eventscoringitemstatus_reporting_show")
     * @Route("/calibration/{id}/show", name="eventsite_calibration_show")
     * @Template()
     */
    public function showAction($id)
    {
        //is current event set?
        $current_event_id = $this->getCurrentEvent();
       
        if ($current_event_id =="" || !(is_numeric($current_event_id))) {
            return $current_event_id; //redirect to index page
        }
        
        //check access, for now just get role, grade level and table info for the user
        $event_capability_array = $this->getUserEventRoleCapabilities();
        $user_role_id = $event_capability_array[$current_event_id][0]['role_id'];
        $user_grade_level_id = $event_capability_array[$current_event_id][0]['grade_level_id'];
        $user_table_id = $event_capability_array[$current_event_id][0]['table_id'];
        $event_type_id = $event_capability_array[$current_event_id][0]['event_type_id'];
        
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        
        //get System Roles
        $system_roles_array = $this->getSystemRoles();
        $role_event_leader_id=$system_roles_array['Event Leader'];
        $role_room_leader_id=$system_roles_array['Room Leader'];
        $role_table_leader_id=$system_roles_array['Table Leader'];
        $role_scorer1_id=$system_roles_array['Scorer 1'];
        $role_scorer2_id = $system_roles_array['Scorer 2']; 
        $role_admin_id = $system_roles_array['Admin']; 
        
        $event_user_id="";
        $alert_entities="";
        $user_info_msg="";
        
        //Asynchronous Scoring settings
        $block_quota_reached_access=true; 
        $user_block_quota_reached_access=true;
        $error_msg_block_quota="";
        $error_msg_user_block_quota="";
        $paper_count_total=array();
        $block_capability_array=array();
        $user_block_capability_array=array();

        if ($user_role_id !=$role_admin_id) { //everyone but Admin should be in event_user table as an attendee
            $event_user_id=$event_capability_array[$current_event_id][0]['id'];
        }

        $status_ready = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Ready'))->getId();
        $status_assigned = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Assigned'))->getId();
         
        //get component id
        if ($request->query->has('component_id') && ($request->query->get('component_id')!="") ) {
           $component_id = $request->query->get('component_id');     
        } else {
            $component_id=1; //default to regular event
        }
        
        $user=$this->container->get('security.context')->getToken()->getUser();

        //check whether the item is in its latest status in EventScoringItemList and whether the user has any acess rights to it
        if ($component_id==2) {
            $entity = $this->checkStatusUserAccess($component_id,"show",$event_user_id,$user_role_id,$user_grade_level_id,$user_table_id,$current_event_id,null,$id,$role_scorer1_id,$role_scorer2_id,$role_table_leader_id,$role_room_leader_id);  
            if ($entity) {
                if (!$entity ['createdBy']==$user->getId())  {
                    throw new AccessDeniedException();
                }
            } 
        }
        
        
        if ($component_id==1) {
            $entity = $this->checkStatusUserAccess($component_id,null,$event_user_id,$user_role_id,$user_grade_level_id,$user_table_id,$current_event_id,null,$id,$role_scorer1_id,$role_scorer2_id,$role_table_leader_id,$role_room_leader_id);  
        
            if ($entity) {
                #if user created this item, no need to check role access, they should have individual show access, as long as it's the latest status in EventScoringItemStatusList for the item 
                if ($entity->getCreatedBy()->getId() != $this->container->get('security.context')->getToken()->getUser()->getId()) { 
                    #if user did not create this item, check whether the user has "show" access based on their role
                    $structure_id =$this->getStatusStructure($user_role_id,$role_event_leader_id,$role_room_leader_id,$role_table_leader_id,$role_admin_id);
                    $combo_id =$this->getStatusCombo($user_role_id,$role_event_leader_id,$role_room_leader_id,$role_table_leader_id,$role_admin_id,$role_scorer1_id,$role_scorer2_id,"show",$structure_id,$entity); 
                    if (!$this->checkStatusAccess($component_id,$user_role_id,"show",$combo_id,"EventScoringItemStatus",null,null,$current_event_id)) {
                        throw new AccessDeniedException();
                    }  
                }        
            }   
        } 
        
        if (!$entity) {
                throw new AccessDeniedException(); 
        }   
        
        if ($component_id==2) {
            
            $max_scoring_item_score_status = $entity ['maxScoringItemScoreStatus'];
            
            ///get correct results - Admin that scored last
            $dbh= $this->get('database_connection');
            $dbh->beginTransaction();
            
            $max_correct_score_sql = "SELECT MAX(id) FROM event_scoring_item_status_byuser_final lf
                                      WHERE event_scoring_item_id=".$entity ['id']." 
                                      AND role_id_created=$role_admin_id"; 
            
            $sth = $dbh->prepare($max_correct_score_sql); 
            $sth->execute();
            $max_correct_score_count = $sth->fetchColumn();
        
            $status_history=false;
            $correct_scores = $em->getRepository('NwpAssessmentBundle:ScoringItemScore')->findBy(array('eventScoringItemStatus'=> $max_correct_score_count), array('id' => 'ASC'));     
        
        } else {
            $max_scoring_item_score_status = $entity->getMaxScoringItemScoreStatus();
            $status_history = $this->getEventScoringItemStatusHistory($entity,$user_role_id,$role_admin_id);
            $correct_scores=false;
            
           
            $reporting=false;
            $url =  $this->getRequest()->getPathInfo(); 
            if (strpos($url, "/reporting")!== false) {  //The url of the page designates that it's the Recent Papers reporting page
                $reporting=true;
            }
            if ($reporting==false ) {
            
                //get Alerts
                $queryBuilder = $this->getEventPapers($component_id,1) ;
                $query = $queryBuilder->getQuery();
                $alert_entities= $query->getResult();
            }
              
        }
        
        //Set check_status_assigned to true for checkStatusAccess function to check whether the user already has papers assigned (if so, they cannot get a new paper)
        $status_assigned_access = $this->checkStatusAccess($component_id,$user_role_id,"create", null,$object="EventScoringItemStatus",$status_ready,$status_assigned,$current_event_id,$check_status_exists=true);
      
        //Asynchronous Scoring Processing
            if ($event_type_id ==2) { //asynchronous event processing
            
                //get total papers (reads) in event by grade level
               $paper_count_total = $this->getPaperTotalsByGradeLevel($user_role_id,$user_grade_level_id, $current_event_id);
               
               //get total papers already assigned by grade level
               $paper_count_grade_level_assigned_array=$this->getPapersAssignedByGradeLevel($user_role_id,$user_grade_level_id, $current_event_id,$status_assigned);
               
               //build block capability array by grade level
               $block_capability_array = $this->getBlocksCapability($user_role_id,$user_grade_level_id,$user_table_id,$current_event_id, $paper_count_total,$paper_count_grade_level_assigned_array);
                  
               //now check individual stats and build user block capability array by event_user_id                
               if ($event_user_id !="") {
                    $eu_record = $em->getRepository('NwpAssessmentBundle:EventUser')->findBy(array('id' => $event_user_id)); 
                    if ($eu_record) {
                        $target_percent_user=$eu_record[0]->getTarget();
                        $max_block_user=$eu_record[0]->getMaxBlock();
                       //get total papers (reads) in event that have already been assigned to scorer
                        $paper_count_total_user_assigned=$this->getPapersAssignedUser($event_user_id,$current_event_id,$status_assigned);
                        //set user block statistics
                        if (((isset($paper_count_total[$user_grade_level_id])) && ($paper_count_total[$user_grade_level_id]>0))
                            && (isset($block_capability_array[$user_grade_level_id])))
                        {
                            $user_block_capability_array[$event_user_id] = $this->getUserBlocksCapability($event_user_id,$user_grade_level_id,$user_role_id,$target_percent_user,$max_block_user,$paper_count_total,$paper_count_total_user_assigned,$block_capability_array);
                        }
                            
                        }
               }
              //check Rules 2 and 3 
              $block_quota_reached_access = $this->checkBlockQuotaAccess($event_user_id, $user_role_id,$user_block_capability_array,$role_scorer1_id,$role_scorer2_id);
              $user_block_quota_reached_access = $this->checkUserBlockQuotaAccess($event_user_id, $user_role_id,$user_block_capability_array,$role_scorer1_id,$role_scorer2_id);
              if ($block_quota_reached_access==false)  {
                  $error_msg_block_quota = "There are no papers to score in this block. Please contact your Table Leader.";
              }
              if ($user_block_quota_reached_access==false) {
                  $error_msg_user_block_quota = "You do not have access to score papers. Please contact your Table Leader.";
              }  
             
            } //end of Asynchronous scoring processing

        $scores = $em->getRepository('NwpAssessmentBundle:ScoringItemScore')->findBy(array('eventScoringItemStatus'=> $max_scoring_item_score_status), array('id' => 'ASC'));     
        
        //set Flash messages for Asynchronous scoring
        if (isset($user_block_capability_array[$event_user_id])) { 
            if ($user_block_capability_array[$event_user_id][0]['user_next_block_ready']==1) {
                $user_next_block_ready_msg = "Congratulations! You have completed Block ".($user_block_capability_array[$event_user_id][0]['user_current_block']-1)
                                            .". Please log out and take a break. Prior to starting your next block, complete the topic immersion process for "
                                            .$block_capability_array[$user_grade_level_id][$user_block_capability_array[$event_user_id][0]['user_current_block']]['block_prompt']
                                            .". When you are done, please contact your Table Leader.";
                $this->get('session')->getFlashBag()->add('success', $user_next_block_ready_msg);
            } else { //display error messages only if congratulations message does not display
                if ($error_msg_block_quota !="") {
                    $this->get('session')->getFlashBag()->add('error', $error_msg_block_quota);
                }
                if ($error_msg_user_block_quota !="") {
                    $this->get('session')->getFlashBag()->add('error', $error_msg_user_block_quota);
                } 
            }
            if (($user_role_id !=$role_admin_id) && ($user_role_id !=$role_event_leader_id) && ($user_role_id !=$role_room_leader_id)) {
                $user_info_msg=" You have scored ".$user_block_capability_array[$event_user_id][0]['user_papers_assigned'].
                     " out of ".$user_block_capability_array[$event_user_id][0]['user_total_target_papers']." papers.  "
                     . "You are on Block ".$user_block_capability_array[$event_user_id][0]['user_current_block'].".";
                //$this->get('session')->getFlashBag()->add('info', $user_info_msg);
            }
             
        }
        
        return array(
            'entity' =>$entity,
            'alert_entities' =>$alert_entities,
            'component_id' =>$component_id,
            'status_history' =>$status_history,
            'status_assigned_access' => $status_assigned_access,
            'correct_scores'      => $correct_scores,
            'scores'      => $scores,
            'user_role_id' => $user_role_id,
            'role_admin_id' => $role_admin_id,
            'role_event_leader_id' => $role_event_leader_id,
            'role_room_leader_id' => $role_room_leader_id,
            'role_table_leader_id' => $role_table_leader_id,
            'role_scorer1_id' => $role_scorer1_id,
            'role_scorer2_id' => $role_scorer2_id,
            'block_quota_reached_access' =>  $block_quota_reached_access,
            'user_block_quota_reached_access' =>  $user_block_quota_reached_access,
            'user_info_msg' => $user_info_msg,
        );
    }
    
    
    /**
     * Displays a form to create a new EventScoringItemStatus entity.
     *
     * @Route("/eventscoringitemstatus/new", name="eventsite_eventscoringitemstatus_new")
     * @Route("/calibration/new", name="eventsite_calibration_new")
     * @Template()
     */
    public function newAction()
    {
        //is current event set?
        $current_event_id = $this->getCurrentEvent();
       
        if ($current_event_id =="" || !(is_numeric($current_event_id))) {
            return $current_event_id; //redirect to index page
        }
        
        //check access, for now just get role, grade level and table info for the user
        $event_capability_array = $this->getUserEventRoleCapabilities();
        $user_role_id = $event_capability_array[$current_event_id][0]['role_id'];
        $user_grade_level_id = $event_capability_array[$current_event_id][0]['grade_level_id'];
        $user_table_id = $event_capability_array[$current_event_id][0]['table_id'];
        
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        
        //get System Roles
        $system_roles_array = $this->getSystemRoles();
        $role_event_leader_id=$system_roles_array['Event Leader'];
        $role_room_leader_id=$system_roles_array['Room Leader'];
        $role_table_leader_id=$system_roles_array['Table Leader'];
        $role_scorer1_id=$system_roles_array['Scorer 1'];
        $role_scorer2_id = $system_roles_array['Scorer 2']; 
        $role_admin_id = $system_roles_array['Admin'];
       
        $event_user_id="";
        
        if ($user_role_id !=$role_admin_id) { //everyone but Admin should be in event_user table as an attendee
            $event_user_id=$event_capability_array[$current_event_id][0]['id'];
        }
        
        //get component id
        if ($request->query->has('component_id') && ($request->query->get('component_id')!="") ) {
           $component_id = $request->query->get('component_id');     
        } else {
            $component_id=1; //default to regular event
        }
        
        //First assign this item if it's component id 2
        if ($component_id==2) {
            if ($request->query->has('previous_scoringitemstatus') && ($request->query->get('previous_scoringitemstatus')!="") ) {
                $previous_scoringitemstatus = $request->query->get('previous_scoringitemstatus'); 
                
                $user=$this->container->get('security.context')->getToken()->getUser();
                $status_assigned = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Assigned'));
                $status_ready = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Ready'));
                $status_accepted = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Accepted'));
                $status_list_by_user =  $em->getRepository('NwpAssessmentBundle:EventScoringItemStatusListByUser')->findOneBy(array('eventScoringItem' => $previous_scoringitemstatus, 'createdBy' => $user->getId()));
                
                if (!$status_list_by_user) {  //status is Ready
                    
                    $status_assigned_access = $this->checkStatusAccess($component_id,$user_role_id,"create", null,$object="EventScoringItemStatus",$status_ready->getId(),$status_assigned->getId(),$current_event_id,$check_status_exists=false);
                    
                    if ($status_assigned_access==true) {
                        //assign this item, previous entity becomes the assigned item
                        $event_scoring_item =  $em->getRepository('NwpAssessmentBundle:EventScoringItem')->find($previous_scoringitemstatus);
                        $new_status_entity =  $this->CreateEventScoringItemStatus(null,$status_assigned,1,1,null,$event_scoring_item,$user);  
                        $previous_entity = $em->getRepository('NwpAssessmentBundle:EventScoringItemStatusListByUser')->find($new_status_entity);     
                    } else {
                       $previous_entity = $status_list_by_user; 
                    }
                } else {
                    $previous_entity = $status_list_by_user;
                }
                    
            }
        }
        
        //check access
        if ($component_id==1) {
            if ($request->query->has('previous_scoringitemstatus') && ($request->query->get('previous_scoringitemstatus')!="") ) {
                $previous_scoringitemstatus = $request->query->get('previous_scoringitemstatus'); 
                $previous_entity = $this->checkStatusUserAccess($component_id,null,$event_user_id,$user_role_id,$user_grade_level_id,$user_table_id,$current_event_id,null,$previous_scoringitemstatus,$role_scorer1_id,$role_scorer2_id,$role_table_leader_id,$role_room_leader_id);
            } 
        }
        
        
        
        if (!$previous_entity) {
           throw new AccessDeniedException(); 
        }
        
        
       
       // if ($component_id==1) { //fix this later, for now don't check fo calibration papers

            $structure_id =$this->getStatusStructure($user_role_id,$role_event_leader_id,$role_room_leader_id,$role_table_leader_id,$role_admin_id);
            $combo_id =$this->getStatusCombo($user_role_id,$role_event_leader_id,$role_room_leader_id,$role_table_leader_id,$role_scorer1_id,$role_scorer2_id,$role_admin_id,"create",$structure_id,$previous_entity);
        
            if (!$this->checkStatusAccess($component_id,$user_role_id,"edit",$combo_id,"EventScoringItemStatus",null,null,$current_event_id)) {
                throw new AccessDeniedException();
            }
      //  }
        
        
        //end of check access
            
        //check whether we should update the status to an under review status:
        $review_entity="";
        $review_status_update=false;
        
        $status_under_review_table_leader = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Under Table Leader Review'));
        $status_under_review_room_leader = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Under Room Leader Review'));
        $status_under_review_event_leader = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Under Event Leader Review'));
        $status_under_adjudication = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Under Adjudication'));
        $status_accepted = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Accepted'));
        $status_adjudicate = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Adjudicate'));
        
        $status_assigned = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Assigned'));
        $status_reassigned = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Returned'));
        $status_returned_table_leader = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Returned to Table Leader'));
         
        
        $assigned_to =  $em->getRepository('NwpAssessmentBundle:EventScoringItemStatus')->findOneBy(array('eventScoringItem' => $previous_entity->getEventScoringItem(), 'scoringRoundNumber' =>$previous_entity->getScoringRoundNumber(),'readNumber' => $previous_entity->getReadNumber(),'status' => $status_assigned),array('id' => 'DESC'),1);
        
        if ($assigned_to) {
            $assigned_to_user_id = $assigned_to->getAssignedTo()->getId(); 
        } else {
           $assigned_to_user_id="";
        }
        
        if (($previous_entity->getStatus()->getId()==$status_under_adjudication->getId())
                || ($previous_entity->getStatus()->getId()==$status_adjudicate->getId()) 
                || ($previous_entity->getStatus()->getId()==$status_assigned->getId())
                || ($previous_entity->getStatus()->getId()==$status_reassigned->getId())
                || (($previous_entity->getStatus()->getId()==$status_returned_table_leader->getId()) && ($assigned_to_user_id == $this->container->get('security.context')->getToken()->getUser()->getId()))
                )  {
            $edit_scores=1;
        } else {
            $edit_scores=0;
        }
        
        if ($component_id==1) {
            $status_pathways = $em->getRepository('NwpAssessmentBundle:ScoringItemStatusPathway')->findBy(array('status' => $previous_entity->getStatus()->getId(),'role' => $user_role_id));

            foreach ($status_pathways as $p) { 
                $review_status_update=false;

                if ($p->getPathway()->getId()==$status_under_review_table_leader->getId()) {
                    $review_status=$status_under_review_table_leader; 
                    $review_status_update=true;
                } elseif ($p->getPathway()->getId()==$status_under_review_room_leader->getId()) {
                     $review_status=$status_under_review_room_leader;
                     $review_status_update=true;
                } elseif ($p->getPathway()->getId()==$status_under_review_event_leader->getId()) {
                     $review_status=$status_under_review_event_leader;
                     $review_status_update=true;
                } elseif ($p->getPathway()->getId()==$status_under_adjudication->getId()) {
                     $review_status=$status_under_adjudication;  
                     $review_status_update=true;
                }
                if ($review_status_update==true) {
                    break; 
                }
            }
        
        
            if ($review_status_update==true) {
                $read_number=$previous_entity->getReadNumber();
                $scoring_round_number=$previous_entity->getScoringRoundNumber();
                $event_scoring_item=$previous_entity->getEventScoringItem();
                $max_scoring_item_score_status=$previous_entity->getMaxScoringItemScoreStatus();
                $user=$this->container->get('security.context')->getToken()->getUser();
                $time_created = new \DateTime('now'); 

                $review_entity  = new EventScoringItemStatus();
                $review_entity->setEventScoringItem($event_scoring_item);
                $review_entity->setStatus($review_status);
                $review_entity->setScoringRoundNumber($scoring_round_number);
                $review_entity->setReadNumber($read_number);  
                $review_entity->setCreatedBy($user);
                $review_entity->setAssignedTo($user);
                $review_entity->setMaxScoringItemScoreStatus($max_scoring_item_score_status);
                $review_entity->setTimeCreated($time_created);
                $em->persist($review_entity);
                $em->flush();

                //update status in event_scoring_item table
                $parent_entity=$em->getRepository('NwpAssessmentBundle:EventScoringItem')->find($event_scoring_item);
                $parent_entity->setStatus($review_status);
                $parent_entity->setMaxEventScoringItemStatus($review_entity);
                $em->persist($parent_entity);
                $em->flush(); 

                return $this->redirect($this->generateUrl('eventsite_eventscoringitemstatus_new', array('previous_scoringitemstatus' =>  $review_entity->getId())));
            }
       
        //end of under review status check
        } //end of component id =1
        
        $entity = new EventScoringItemStatus();
        $current_event = $em->getRepository('NwpAssessmentBundle:Event')->find($current_event_id);
        
        //get the scoring rubric used for this event
        $scoring_rubric_id=$current_event->getScoringRubric()->getId();
        $scoring_rubric = $em->getRepository('NwpAssessmentBundle:ScoringRubric')->find($scoring_rubric_id);
        
        //get scoring scale the rubric uses
        $min_score = $scoring_rubric->getMinScore();
        $max_score = $scoring_rubric->getMaxScore();
        $scoring_scale=array();
        for($c= $min_score;$c<=$max_score;$c++){
            $scoring_scale[$c]=$c;
        }
         
        if (($previous_entity->getStatus()->getId()==$status_adjudicate->getId()) || ($previous_entity->getStatus()->getId()==$status_under_adjudication->getId()))  {
            //get only the attributes that were more than 1 score apart and need to be adjudicated
            $required_attributes = $this->checkAdjudication($current_event_id,$previous_entity->getEventScoringItem()->getId(),$status_accepted->getId());
            $Ids = implode($required_attributes, ",");
            $qb = $em->getRepository('NwpAssessmentBundle:ScoringRubricAttribute')->createQueryBuilder('e')->where('e.id IN ('.$Ids.')');       
            $query = $qb->getQuery();
            $attributes= $query->getResult();     
        } else {       
            $attributes = $em->getRepository('NwpAssessmentBundle:ScoringRubricAttribute')->findBy(array('rubric'=> $scoring_rubric_id), array('id' => 'ASC'));
        }
        
       $formBuilder = $this->get('form.factory')->createNamedBuilder('scoring_form', 'form', null);
       if ($component_id==1) {
            //get pathways for previous status for filtering status field
            $user_status_pathways = $this->getUserStatusPathways($user_role_id);
            if ($review_status_update==true) {
                $originating_status = $review_status->getId();
            } else {
                $originating_status = $previous_entity->getStatus()->getId();
            }
            //var_dump($user_status_pathways);
            $Ids="";
            if (isset ($user_status_pathways[$originating_status]) ){
                foreach($user_status_pathways[$originating_status] as $usp) {
                    if ($usp['component_id']==$component_id) {
                        $Ids .= $usp['pathway_id'].",";
                    }
                }
                $Ids = substr($Ids, 0, -1); //strip last comma
            }
            if ($Ids =="") { //user does not have the right to edit this status, redirect to no access page
               throw new AccessDeniedException(); 
            }
        
            
            $formBuilder->add('status', 'entity', array(
                              'class' => 'NwpAssessmentBundle:ScoringItemStatus',
                              'property' => 'actionName',
                              'label' =>'Select Action:',
                              'required' => true,
                              'query_builder' => function ($er) use ($Ids) 
                                    {
                                        $qb = $er->createQueryBuilder('status');
                                        $qb->where('status.id IN ('.$Ids.')');
                                        $qb->orderBy('status.orderId');

                                        return $qb;
                                    }
                               ));
                               
          $formBuilder->add('comment','textarea', array('required' => false,'max_length' => 500,'label' =>'Comment:')); 
       }//end of component id 1
             
       
       if ($component_id==1) {
            if (($previous_entity->getStatus()->getId()==$status_adjudicate->getId()) || ($previous_entity->getStatus()->getId()==$status_under_adjudication->getId()))  {
                $item_event_scoring_item_id =  $previous_entity->getEventScoringItem()->getId();

                $user_id=$this->container->get('security.context')->getToken()->getUser()->getId();
                $users_scored=$this->getUsersScoredPaper($item_event_scoring_item_id);          
                $users_scored_ids="";
                $users_scored_array_size=sizeof($users_scored);
                for($u=0;$u<$users_scored_array_size;$u++){ 
                    $users_scored_ids .=$users_scored[$u]["user_id"].",";

                }
                $users_scored_ids .=$user_id.",";

                $users_scored_ids = substr($users_scored_ids, 0, -1); //strip last comma
                
                $event_users_excluded=$this->getEventUsersExcluded($item_event_scoring_item_id,$current_event_id);
                //echo "event user excluded is !!!".$event_users_excluded;

                $formBuilder->add('assignedTo', 'filter_entity', array('class' =>'Application\Sonata\UserBundle\Entity\User','label' =>'Assign To:',

                                 'query_builder' => function ($er) use ($current_event_id,$users_scored_ids,$user_role_id,$user_grade_level_id,$user_table_id,$role_room_leader_id,$role_table_leader_id,$event_users_excluded) 
                                   {
                                       $qb = $er->createQueryBuilder('u');
                                       $qb->leftJoin('NwpAssessmentBundle:EventUser','eu',
                                                    \Doctrine\ORM\Query\Expr\Join::WITH,
                                                   'eu.user = u.id');
                                       $qb->where('eu.event='.$current_event_id);
                                       $qb->andWhere('eu.gradeLevel='.$user_grade_level_id); 
                                       if ($user_role_id==$role_room_leader_id)  {
                                           #$qb->andWhere('(eu.role='.$role_table_leader_id.' OR eu.role='.$role_room_leader_id.')')
                                           $qb->andWhere('eu.role='.$role_room_leader_id)
                                              ->andWhere($qb->expr()->notIn('u.id', $users_scored_ids))
                                              ;
                                       } #elseif ($user_role_id==$role_table_leader_id)  {
                                         #  $qb->andWhere('eu.role='.$role_room_leader_id)
                                          #    ->andWhere($qb->expr()->notIn('u.id', $users_scored_ids));
                                       #}
                                       if ($event_users_excluded !="") {
                                            $qb->andWhere('eu.id not in ('.$event_users_excluded.')');
                                       }
                                       
                                       return $qb;
                                   }
                       ));
                       

            }
       }//end of component id 1
       
        foreach ($attributes as $attribute) {
             $i=$attribute->getId();
             $formBuilder->add('score_'.$i, 'choice', array('label'=>$attribute->getAttribute()->getName(),
                                                             'required' => false,
                                                             'choices' => $scoring_scale,
                                                             'expanded' => true, 
                  ));
             if (($component_id==2) && ($user_role_id==$role_admin_id)) {
                $formBuilder->add('commentary_'.$i,'text', array('required' => false,'max_length' => 2000,'label' =>'Commentary:')); 
             }
         }
         
         $scores = $em->getRepository('NwpAssessmentBundle:ScoringItemScore')->findBy(array('eventScoringItemStatus'=> $previous_entity->getMaxScoringItemScoreStatus()), array('id' => 'ASC'));     
        
         $form = $formBuilder->getForm();
         
         $status_history = $this->getEventScoringItemStatusHistory($previous_entity,$user_role_id,$role_admin_id);
        
          
        return array(
            'entity' => $entity,
            'edit_scores' => $edit_scores,
            'status_history' => $status_history,
            'scores' => $scores,
            'previous_entity' =>$previous_entity,
            'review_entity' =>$review_entity,
            'attributes' =>$attributes,
            'user_role_id' => $user_role_id,
            'role_admin_id' => $role_admin_id,
            'role_event_leader_id' => $role_event_leader_id,
            'role_room_leader_id' => $role_room_leader_id,
            'role_table_leader_id' => $role_table_leader_id,
            'role_scorer1_id' => $role_scorer1_id,
            'role_scorer2_id' => $role_scorer2_id,
            'component_id' => $component_id,
            'form'   => $form->createView(),
        );
     
    }

    /**
     * Creates a new EventScoringItemStatus entity.
     *
     * @Route("/eventscoringitemstatus/create", name="eventsite_eventscoringitemstatus_create")
     * @Route("/calibration/create", name="eventsite_calibration_create")
     * @Method("post")
     * @Template("NwpAssessmentBundle:EventScoringItemStatus:new.html.twig")
     */
    public function createAction()
    {
       
        //is current event set?
        $current_event_id = $this->getCurrentEvent();
       
        if ($current_event_id =="" || !(is_numeric($current_event_id))) {
              return $current_event_id; //redirect to index page
        }
        
        //check access, for now just get role, grade level and table info for the user
        $event_capability_array = $this->getUserEventRoleCapabilities();
        $user_role_id = $event_capability_array[$current_event_id][0]['role_id'];
        $user_grade_level_id = $event_capability_array[$current_event_id][0]['grade_level_id'];
        $user_table_id = $event_capability_array[$current_event_id][0]['table_id']; 
        $event_type_id = $event_capability_array[$current_event_id][0]['event_type_id'];
        
        $error_msg="";
        $error_msg_spec="";
        $review_entity="";
        $previous_scoringitemstatus="";
        $review_scoringitemstatus="";
        $overwrite_scores = false;
        
        $em = $this->getDoctrine()->getManager();
        $current_event = $em->getRepository('NwpAssessmentBundle:Event')->find($current_event_id);
        
        $status_under_review_table_leader = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Under Table Leader Review'));
        $status_under_review_room_leader = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Under Room Leader Review'));
        $status_accepted = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Accepted'));
        $status_adjudicate = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Adjudicate'));   
        $status_under_adjudication = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Under Adjudication'));
            
        $request = $this->getRequest();
        
        if ($request->query->has('component_id') && ($request->query->get('component_id')!="") ) {
           $component_id = $request->query->get('component_id');     
        } else {
            $component_id=1; //default to regular event
        }
 
        //get System Roles
        $system_roles_array = $this->getSystemRoles();
        $role_event_leader_id=$system_roles_array['Event Leader'];
        $role_room_leader_id=$system_roles_array['Room Leader'];
        $role_table_leader_id=$system_roles_array['Table Leader'];
        $role_scorer1_id=$system_roles_array['Scorer 1'];
        $role_scorer2_id = $system_roles_array['Scorer 2'];
        $role_admin_id = $system_roles_array['Admin'];
        
        $event_user_id="";
        
        if ($user_role_id !=$role_admin_id) { //everyone but Admin should be in event_user table as an attendee
            $event_user_id=$event_capability_array[$current_event_id][0]['id'];
        }
        
        //get component id
        if ($request->query->has('component_id') && ($request->query->get('component_id')!="") ) {
           $component_id = $request->query->get('component_id');     
        } else {
            $component_id=1; //default to regular event
        }
        
        //get querystring
        if ($request->query->has('previous_scoringitemstatus') && ($request->query->get('previous_scoringitemstatus')!="") ) {
           $previous_scoringitemstatus = $request->query->get('previous_scoringitemstatus');   
        } 
        if ($request->query->has('review_scoringitemstatus') && ($request->query->get('review_scoringitemstatus')!="") ) {
           $review_scoringitemstatus = $request->query->get('review_scoringitemstatus');
           
        } 
        
        if ($component_id==1) {
            $show_path = 'eventsite_eventscoringitemstatus_show';
        } else {
            $show_path='eventsite_calibration_show';
        }
        
        //check access
        
        //If component id 2, item should have already been assigned to be in createAction
        if ($component_id==2) {
            if ($request->query->has('previous_scoringitemstatus') && ($request->query->get('previous_scoringitemstatus')!="") ) {
                $previous_scoringitemstatus = $request->query->get('previous_scoringitemstatus'); 
                
                $user=$this->container->get('security.context')->getToken()->getUser();
                $status_assigned = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Assigned'));
                $status_ready = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Ready'));
                $status_accepted = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Accepted'));
                $previous_entity =  $em->getRepository('NwpAssessmentBundle:EventScoringItemStatusListByUser')->findOneBy(array('id' => $previous_scoringitemstatus, 'createdBy' => $user->getId()));
               
                    
            }
        }
        
        if ($component_id==1) {
            if ($previous_scoringitemstatus!="") {
               if ($review_scoringitemstatus!=""){ 
                    //it's a new under review item, check access for the review item, not previous item
                    $previous_entity = $em->getRepository('NwpAssessmentBundle:EventScoringItemStatus')->find($previous_scoringitemstatus);
                    $review_entity=$this->checkStatusUserAccess($component_id,null,$event_user_id,$user_role_id,$user_grade_level_id,$user_table_id,$current_event_id,null,$review_scoringitemstatus,$role_scorer1_id,$role_scorer2_id,$role_table_leader_id,$role_room_leader_id);  
                    if (!$review_entity) {
                        throw new AccessDeniedException(); 
                    }
               } else {
                    $previous_entity=$this->checkStatusUserAccess($component_id,null,$event_user_id,$user_role_id,$user_grade_level_id,$user_table_id,$current_event_id,null,$previous_scoringitemstatus,$role_scorer1_id,$role_scorer2_id,$role_table_leader_id,$role_room_leader_id); 

                    if (!$previous_entity) {
                        throw new AccessDeniedException(); 
                    }

                    $review_entity = $em->getRepository('NwpAssessmentBundle:EventScoringItemStatus')->find($review_scoringitemstatus);
               }

            }
        }
        
       
        $structure_id =$this->getStatusStructure($user_role_id,$role_event_leader_id,$role_room_leader_id,$role_table_leader_id,$role_admin_id);
        $combo_id =$this->getStatusCombo($user_role_id,$role_event_leader_id,$role_room_leader_id,$role_table_leader_id,$role_admin_id,$role_scorer1_id,$role_scorer2_id,"create",$structure_id,$previous_entity);
        
        if (!$this->checkStatusAccess($component_id,$user_role_id,"edit",$combo_id,"EventScoringItemStatus",null,null,$current_event_id)) {
            throw new AccessDeniedException();
        }
        //end of check access
        
         
        $read_number=$previous_entity->getReadNumber();
        $scoring_round_number=$previous_entity->getScoringRoundNumber();
        $event_scoring_item=$previous_entity->getEventScoringItem();
        
        //get the scoring rubric used for this event
        $scoring_rubric_id=$current_event->getScoringRubric()->getId();
        $scoring_rubric = $em->getRepository('NwpAssessmentBundle:ScoringRubric')->find($scoring_rubric_id);
        
        //get scoring scale the rubric uses
        $min_score = $scoring_rubric->getMinScore();
        $max_score = $scoring_rubric->getMaxScore();
        $scoring_scale=array();
        for($c= $min_score;$c<=$max_score;$c++){
            $scoring_scale[$c]=$c;
        }
        
        $entity  = new EventScoringItemStatus();
        
        if (($previous_entity->getStatus()->getId()==$status_adjudicate->getId()) || ($previous_entity->getStatus()->getId()==$status_under_adjudication->getId()))  {
            //get only the attributes that were more than 1 score apart and need to be adjudicated
            $required_attributes = $this->checkAdjudication($current_event_id,$previous_entity->getEventScoringItem()->getId(),$status_accepted->getId());
            $Ids = implode($required_attributes, ",");
            $qb = $em->getRepository('NwpAssessmentBundle:ScoringRubricAttribute')->createQueryBuilder('e')->where('e.id IN ('.$Ids.')');       
            $query = $qb->getQuery();
            $attributes= $query->getResult();     
        } else {       
            $attributes = $em->getRepository('NwpAssessmentBundle:ScoringRubricAttribute')->findBy(array('rubric'=> $scoring_rubric_id), array('id' => 'ASC'));
        }
        
        $formBuilder = $this->get('form.factory')->createNamedBuilder('scoring_form', 'form', null);
         
        if ($component_id==1) {
            //get pathways for filtering status field
            $user_status_pathways = $this->getUserStatusPathways($user_role_id);
            if ((isset ($review_entity)) && ($review_entity !="")) {
                $originating_status = $review_entity->getStatus()->getId();
            } else {
                $originating_status = $previous_entity->getStatus()->getId();
            }
            $Ids="";
            if (isset ($user_status_pathways[$originating_status])) {
                foreach($user_status_pathways[$originating_status] as $usp) {
                    if ($usp['component_id']==$component_id) {
                        $Ids .= $usp['pathway_id'].",";
                    }
                }
                $Ids = substr($Ids, 0, -1); //strip last comma
            }
            if ($Ids =="") { //user does not have the right to edit this status, redirect to no access page
               throw new AccessDeniedException(); 
            }
        
       
            $formBuilder->add('status', 'entity', array(
                             'class' => 'NwpAssessmentBundle:ScoringItemStatus',
                             'property' => 'actionName',
                             'label' =>'Select Action:',
                             'required' => true,
                             'query_builder' => function ($er) use ($Ids) 
                                   {
                                       $qb = $er->createQueryBuilder('status');
                                       $qb->where('status.id IN ('.$Ids.')');
                                       $qb->orderBy('status.orderId');

                                       return $qb;
                                   }
                              ));
                              
           $formBuilder->add('comment','textarea', array('required' => false,'max_length' => 500,'label' =>'Comment:')); 
           
        }
        
        
        if ($component_id==1) {
            if (($previous_entity->getStatus()->getId()==$status_adjudicate->getId()) || ($previous_entity->getStatus()->getId()==$status_under_adjudication->getId()))  {

                $item_event_scoring_item_id =  $previous_entity->getEventScoringItem()->getId();

                $user_id=$this->container->get('security.context')->getToken()->getUser()->getId();
                $users_scored=$this->getUsersScoredPaper($item_event_scoring_item_id);          
                $users_scored_ids="";
                $users_scored_array_size=sizeof($users_scored);
                for($u=0;$u<$users_scored_array_size;$u++){ 
                    $users_scored_ids .=$users_scored[$u]["user_id"].",";

                }
                $users_scored_ids .=$user_id.",";

                $users_scored_ids = substr($users_scored_ids, 0, -1); //strip last comma
                
                $event_users_excluded=$this->getEventUsersExcluded($item_event_scoring_item_id,$current_event_id);

                $formBuilder->add('assignedTo', 'filter_entity', array('class' =>'Application\Sonata\UserBundle\Entity\User','label' =>'Assign To:',

                                   'query_builder' => function ($er) use ($current_event_id,$users_scored_ids,$user_role_id,$user_grade_level_id,$user_table_id,$role_room_leader_id,$role_table_leader_id, $event_users_excluded) 
                                     {
                                         $qb = $er->createQueryBuilder('u');
                                         $qb->leftJoin('NwpAssessmentBundle:EventUser','eu',
                                                      \Doctrine\ORM\Query\Expr\Join::WITH,
                                                     'eu.user = u.id');
                                         $qb->where('eu.event='.$current_event_id);
                                         $qb->andWhere('eu.gradeLevel='.$user_grade_level_id); 
                                        if ($user_role_id==$role_room_leader_id)  {
                                           #$qb->andWhere('(eu.role='.$role_table_leader_id.' OR eu.role='.$role_room_leader_id.')')
                                           $qb->andWhere('eu.role='.$role_room_leader_id)
                                              ->andWhere($qb->expr()->notIn('u.id', $users_scored_ids))
                                              ;
                                       } #elseif ($user_role_id==$role_table_leader_id)  {
                                         #  $qb->andWhere('eu.role='.$role_room_leader_id)
                                          #    ->andWhere($qb->expr()->notIn('u.id', $users_scored_ids));
                                       #}
                                         if ($event_users_excluded!='') {
                                            $qb->andWhere('eu.id not in ('.$event_users_excluded.')');
                                         }
                                         
                                         return $qb;
                                     }
                         ));
              }
         
        }
         
        foreach ($attributes as $attribute) {
             $i=$attribute->getId();
             $formBuilder->add('score_'.$i, 'choice', array('label'=>$attribute->getAttribute()->getName(),
                                                            'required' => false,
                                                             'choices' => $scoring_scale,
                                                             'expanded' => true, 
                  ));
             
             
             if (($component_id==2) && ($user_role_id==$role_admin_id)) {
                $formBuilder->add('commentary_'.$i,'text', array('required' => false,'max_length' => 2000,'label' =>'Commentary:')); 
             }
                
         }
         
         $scores = $em->getRepository('NwpAssessmentBundle:ScoringItemScore')->findBy(array('eventScoringItemStatus'=> $previous_entity->getMaxScoringItemScoreStatus()), array('id' => 'ASC'));            
         
         $status_history = $this->getEventScoringItemStatusHistory($previous_entity,$user_role_id,$role_admin_id); 
         
         $form = $formBuilder->getForm();
         $form->bind($request);

        
        if ($form->isValid()) {
            
            //error-check the form. The following statuses have to have scores submitted along with the status:
            //"Submitted", "Accepted" if Table Leader/Room Leader's own paper, and "Adjudicated"
            $formData = $form->getData();
            if ($component_id==2) {
                    $status=$status_accepted; //for callibartion papers, once they are assigned and submitted, they're automatically accepted, fix this later to be dynamic
            } else {
                $status = $formData['status'];
            }
            
            if ($component_id==1) {
                $comment = $formData['comment'];
            }
            
            $status_ready = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Ready'));
            $status_submitted = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Submitted'));
            $status_consult_table_leader = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Consult with Table Leader'));
            $status_consult_room_leader = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Consult with Room Leader'));
            $status_nonscorable_table_leader = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Non-Scorable to Table Leader'));
            $status_nonscorable_room_leader = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Non-Scorable to Room Leader'));
            $status_nonscorable = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Non-Scorable'));
            $status_redflag_event_leader = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Red-Flag to Event Leader'));
            $status_redflag = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Red Flag'));
            $status_assigned = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Assigned'));
            $status_reassigned = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Returned'));
            $status_returned_table_leader = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Returned to Table Leader'));
           
            if (($previous_entity->getStatus()==$status_adjudicate) || ($previous_entity->getStatus()==$status_under_adjudication)) {
                $scoring_round_number = 3;
            } else {
                $scoring_round_number=$previous_entity->getScoringRoundNumber();
            }
            
            $assigned_to =  $em->getRepository('NwpAssessmentBundle:EventScoringItemStatus')->findOneBy(array('eventScoringItem' => $previous_entity->getEventScoringItem(), 'scoringRoundNumber' =>$scoring_round_number,'readNumber' => $previous_entity->getReadNumber(),'status' => $status_assigned),array('id' => 'DESC'),1);
            
            if ($assigned_to) {
                $assigned_to_user_id = $assigned_to->getAssignedTo()->getId(); 
            } else {
                $assigned_to_user_id="";
            }
            
            
            if (($previous_entity->getStatus()->getId()==$status_under_adjudication->getId())
                || ($previous_entity->getStatus()->getId()==$status_adjudicate->getId()) 
                || ($previous_entity->getStatus()->getId()==$status_assigned->getId())
                || ($previous_entity->getStatus()->getId()==$status_reassigned->getId())
                || (($previous_entity->getStatus()->getId()==$status_returned_table_leader->getId()) && ($assigned_to_user_id == $this->container->get('security.context')->getToken()->getUser()->getId()))
                )  {
                $edit_scores=1;
            } else {
                $edit_scores=0;
            }
            
            if ($status->getId()==$status_accepted->getId()) {
                $previous_status_id = $previous_entity->getStatus()->getId();
            } else {
                $previous_status_id ="";
            }
           
            //A. all attributes are required if paper is submitted
            //B. if paper is accepted, we look at previous status:
            //1) previous statuses Assigned and Re-Assigned mean I am accepting my own paper, so all attributes are required
            //2) previous statuses Under TL Review, Under RL Review mean I am either accepting previous scores or overwriting old scores
            if (($status->getId()==$status_submitted->getId()) 
               || ($previous_status_id==$status_assigned->getId()) 
               || ($previous_status_id==$status_reassigned->getId())
               || (($previous_status_id==$status_returned_table_leader->getId()) &&  ($assigned_to_user_id == $this->container->get('security.context')->getToken()->getUser()->getId()))    
             )
            {
                //all attributes need to be scored
                foreach ($attributes as $attribute) {
                    $i=$attribute->getId();
                    $score_value = $formData['score_'.$i];
                    if ($score_value=="") {
                        $error_msg_spec .= $attribute->getAttribute()->getName().",";
                    }
                }
            } elseif ((($review_entity !="") 
                    || ($previous_status_id==$status_under_review_table_leader->getId()) 
                    || ($previous_status_id==$status_under_review_room_leader->getId())
                    || (($previous_status_id==$status_returned_table_leader->getId()) &&  ($assigned_to_user_id != $this->container->get('security.context')->getToken()->getUser()->getId()))
                    )
                    && ($previous_status_id !="")) {  //status is Under TL Review or Under RL Review 
                 
               
                foreach ($attributes as $attribute) {
                    $i=$attribute->getId();
                    $score_value = $formData['score_'.$i];
                    if ($score_value !="") {
                        $overwrite_scores=true;
                    }  else {
                        $error_msg_spec .= $attribute->getAttribute()->getName().",";
                    }
                }
               
                
                if ($overwrite_scores==false) {
                   $error_msg_spec="";
                   
                   if (sizeof($scores) >0) {
                       foreach($scores as $score) {
                            if ($score->getScore()=="") {
                                $error_msg_spec .= $score->getScoringRubricAttribute().",";
                            }
                        }
                   } else {
                       foreach ($attributes as $attribute) {
                          $error_msg_spec .= $attribute->getAttribute()->getName().","; 
                       }
                   }
                }
                
            } elseif (($previous_status_id==$status_adjudicate->getId()) || ($previous_status_id==$status_under_adjudication->getId())) {
                //get only the attributes that were more than 1 score apart and need to be adjudicated
                $required_attributes = $this->checkAdjudication($current_event_id,$event_scoring_item->getId(),$status_accepted->getId());
                
                foreach ($attributes as $attribute) {
                    $i=$attribute->getId();
                    $score_value = $formData['score_'.$i];
                    if ($required_attributes !="") {
                        foreach ($required_attributes as $re) {
                            if ($re==$i) {
                                if ($score_value=="") {
                                    $error_msg_spec .= $attribute->getAttribute()->getName().",";
                                } 
                            }
                        }
                    }        
                }
                
            } else {
                $required_attributes="";//end of required attributes check
            }

           if ($status->getId()==$status_adjudicate->getId()) {
               $assigned_to_value=$formData['assignedTo'];
               if ($assigned_to_value=="") {
                    $error_msg .="Error:  The form was not submitted.  You must choose the person in the \"Assign To\" dropdown box when Action \"".$status_adjudicate->getActionName()."\" is selected. ";
               }            
           }
           
           if ($status->getId()==$status_nonscorable->getId()) {
               $comment_value=$formData['comment'];
               if ($comment_value=="") {
                    $error_msg .="Error:  The form was not submitted.  You must write a comment in the \"Comment\" textbox when Action \"".$status_nonscorable->getActionName()."\"  is selected. ";
               }            
           }
         
            
          
           if ($error_msg_spec !="") { //scores were required, but some or all were missing
                $error_msg_spec =  substr($error_msg_spec, 0, -1);
                $error_msg .= "Error:  Your scores were NOT ".$status->getName()."! The following attributes are missing scores: ".$error_msg_spec; //strip last comma
           }
            
            
           if ($error_msg!="") {
               $this->get('session')->getFlashBag()->add('error', $error_msg);
           } else { //scores were submitted or were not required
                
                //insert scores into scoring_item_score only with allowed statuses, otherwise scores are ignored
                $insert_scores=false;
                if (($status->getId()==$status_submitted->getId()) 
                    || ($previous_status_id==$status_assigned->getId()) || ($previous_status_id==$status_reassigned->getId()) 
                    || (($previous_status_id==$status_returned_table_leader->getId()) &&  ($assigned_to_user_id == $this->container->get('security.context')->getToken()->getUser()->getId())) 
                    || ($previous_status_id==$status_adjudicate->getId()) || ($previous_status_id==$status_under_adjudication->getId())   
                    || ($overwrite_scores==true)
                    )  {
                   
                    //insert scores 
                    $insert_scores=true;
                } elseif ($status->getId()==$status_consult_table_leader->getId() || 
                         (($status->getId()==$status_consult_room_leader->getId()) && ($review_entity =="") && ($previous_entity->getStatus()->getId()!=$status_under_review_table_leader->getId()))
                         ) {
                    
                    foreach ($attributes as $attribute) {
                        $i=$attribute->getId();
                        if ($formData['score_'.$i] !="") { //if at least one attribute was graded, enter all the scores
                            $insert_scores=true;
                            break;
                        }
                    }   
                } 
                
                //insert new status first
                $em = $this->getDoctrine()->getManager();
            
                //create an entry in event_scoring_item_status table to update the status
                $user=$this->container->get('security.context')->getToken()->getUser();
                $time_created = new \DateTime('now');  
            
                //first get values from previous status entry
                $entity->setEventScoringItem($event_scoring_item);
                $entity->setScoringRoundNumber($scoring_round_number);
                $entity->setCreatedBy($user);
                $entity->setTimeCreated($time_created);
                
                if ($insert_scores==false) {
                    $max_scoring_item_score_status=$previous_entity->getMaxScoringItemScoreStatus(); 
                    $entity->setMaxScoringItemScoreStatus($max_scoring_item_score_status); //no new scores are entered, set max score id to previous score id
                }
            
                if ($status->getId()==$status_ready->getId()) {
                    $entity->setReadNumber($read_number+1);  
                } else {
                    $entity->setReadNumber($read_number);
                }
                
                if (($status->getId()==$status_reassigned->getId())) {  //Reassign should be assigned to orginal scorer whom the paper was assigned to last
                    $assigned_to =  $em->getRepository('NwpAssessmentBundle:EventScoringItemStatus')->findOneBy(array('eventScoringItem' => $event_scoring_item, 'scoringRoundNumber' =>$scoring_round_number,'readNumber' => $read_number,'status' => $status_assigned),array('id' => 'DESC'),1);
                    $entity->setAssignedTo($assigned_to->getAssignedTo()); 
                } elseif (($status->getId()==$status_returned_table_leader->getId())) {
                    //get Table Leader of the person whom the paper was assigned to by getting the status_under_review_table_leader status of the item
                    //if status_under_review_table_leader is missing, it means either
                    //A. Table Leader was the Scorer, so get assigned status
                    //B. Table Leader never saw this item, so get the Table Leader for this item.  In case of multiple table leaders, get first one in ascending order
         
                    //sort the query by status, and then id in desc order in case there are mutliple entries for this item to get the latest person that had it in either status_under_review_table_leader or status_assigned
                    $queryBuilder =$em->getRepository('NwpAssessmentBundle:EventScoringItemStatus')
                          ->createQueryBuilder('esis')
                          ->select('esis')
                          ->leftJoin('NwpAssessmentBundle:EventUser','eu',
                                                 \Doctrine\ORM\Query\Expr\Join::WITH,
                                                'eu.user = esis.assignedTo')
                          ->Where('esis.eventScoringItem='.$event_scoring_item->getId())
                          ->andWhere('esis.scoringRoundNumber='.$scoring_round_number)  
                          ->andWhere('esis.readNumber='.$read_number)
                          ->andWhere('esis.status IN ('.$status_under_review_table_leader->getId().",".$status_assigned->getId().')')
                          ->andWhere('eu.event='.$current_event_id)
                          ->andWhere('eu.role='.$role_table_leader_id)
                          ->orderBy('esis.status DESC, esis.id', 'DESC');
            
                    $query = $queryBuilder->getQuery();

                    $assignees= $query->getResult();
                    $table_leader_found = false;
                    foreach ($assignees as $a) {
                        if ($a->getAssignedTo() != "") {
                          $entity->setAssignedTo($a->getAssignedTo()); //when assigned to value is available, set it and get out of the loop
                          $table_leader_found=true;
                          break;
                        }
                    }
                    if ($table_leader_found==false) { //Table Leader never looked at this item
                        //get the Table Leader of whom the paper was originally assigned to  
                         $assigned_to =  $em->getRepository('NwpAssessmentBundle:EventScoringItemStatus')->findOneBy(array('eventScoringItem' => $event_scoring_item, 'scoringRoundNumber' =>$scoring_round_number,'readNumber' => $read_number,'status' => $status_assigned),array('id' => 'DESC'),1);
                         $assigned_to_table_leader = $this->getTableLeader($assigned_to->getAssignedTo()->getId(),$current_event_id,$role_table_leader_id);
                         if ($assigned_to_table_leader) {
                             $entity->setAssignedTo($assigned_to_table_leader[0]->getUser()); 
                         }
                    }
                } elseif (($status->getId()==$status_adjudicate->getId())) { //adjudication is assigned to someone else
                    $entity->setAssignedTo($assigned_to_value); //get the values from the assignedTo dropdown box
                }
                 
                 //now get user-entered values from the form 
               
                $entity->setStatus($status);
                if ($component_id==1) {
                    $entity->setComment($comment);
                }
                $em->persist($entity);
                $em->flush();
  
                if ($insert_scores==true) {
                    foreach ($attributes as $attribute) {
                        $i=$attribute->getId();
                        $score = $formData['score_'.$i]; 
                        $sub_entity  = new ScoringItemScore();
                        $sub_entity->setEventScoringItemStatus($entity); 
                        $sub_entity->setScoringRubricAttribute($attribute);
                        $sub_entity->setScore($score);
                        if (($component_id==2) && ($user_role_id==$role_admin_id)) {
                            $commentary = $formData['commentary_'.$i];
                            $sub_entity->setComment($commentary);
                        }
                        $em->persist($sub_entity);
                        $em->flush();
                    } 
                    $entity->setMaxScoringItemScoreStatus($entity->getId()); //set max score id to newly inserted scores
                    $em->persist($entity);
                    $em->flush();
                } 
            
                //post-processing for event_item_scoring table, set max status, etc.
                //the below line replaces the code that used to be here and was put into a function EventScoringItemStatusPostProcessing instead
                $post_processing=$this->EventScoringItemStatusPostProcessing($current_event_id,$entity,$status);     
            
                //only show this message if non-asyncrhonous (manual) event
                if ($event_type_id==1) {
                    $this->get('session')->getFlashBag()->add('success', 'flash.createeventscoringitem.success');
                }
                
                return $this->redirect($this->generateUrl($show_path, array('id' =>  $entity->getId(),'component_id' =>  $component_id)));        
            }  
        } else { //form not valid
            $this->get('session')->getFlashBag()->add('error', 'flash.createeventscoringitem.error');
        }
        
        return array(
            'entity' => $entity,
            'scores' => $scores,
            'status_history' => $status_history,
            'previous_entity' =>$previous_entity,
            'edit_scores' => $edit_scores,
            'review_entity' =>$review_entity,
            'attributes' =>$attributes,
            'user_role_id' => $user_role_id,
            'role_admin_id' => $role_admin_id,
            'role_event_leader_id' => $role_event_leader_id,
            'role_room_leader_id' => $role_room_leader_id,
            'role_table_leader_id' => $role_table_leader_id,
            'role_scorer1_id' => $role_scorer1_id,
            'role_scorer2_id' => $role_scorer2_id,
            'form'   => $form->createView(),
        );
    }
    
    public function checkAdjudication($current_event_id,$event_scoring_item_id,$status_id) {
        
        $adjudicate_attributes="";
        $adjudication_array_1=array();
        $adjudication_array_2=array();
        
        #number of score points apart that trigger adjudication
        $em = $this->getDoctrine()->getManager();
        
        $current_event = $em->getRepository('NwpAssessmentBundle:Event')->find($current_event_id);
        $adjudication_trigger=$current_event->getAdjudicationTrigger();      
        
        $sql = "SELECT event_scoring_item_id,scoring_round_number,read_number,sis.id,scoring_rubric_attribute_id,score 
                FROM event_scoring_item_status ess
                JOIN scoring_item_score sis
                ON ess.max_scoring_item_score_status_id = sis.event_scoring_item_status_id
                WHERE event_scoring_item_id=$event_scoring_item_id AND status_id=$status_id";
       
        $dbh= $this->get('database_connection');
        $stmt = $dbh->query($sql); 
        $adjudication_array = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        $adjudication_array_size=sizeof($adjudication_array);
        for($c=0;$c<$adjudication_array_size;$c++){  
            if ($adjudication_array[$c]["scoring_round_number"]==1) {
                $adjudication_array_1[$adjudication_array[$c]["scoring_rubric_attribute_id"]]=$adjudication_array[$c]['score'];
            } elseif ($adjudication_array[$c]["scoring_round_number"]==2) {
                 $adjudication_array_2[$adjudication_array[$c]["scoring_rubric_attribute_id"]]=$adjudication_array[$c]['score'];
            }
        }
        
        foreach($adjudication_array_1 as $key=> $a1){
            $diff = abs($a1 - $adjudication_array_2[$key]);
            if ($diff >= $adjudication_trigger) {  
                $adjudicate_attributes[] = $key;
            }   
        }
       
        return $adjudicate_attributes;
    }
    
    public function checkStatusUserAccess($component_id,$action=null,$event_user_id,$user_role_id,$user_grade_level_id,$user_table_id,$current_event_id,$event_scoring_item_id=null,$previous_scoringitemstatus,$role_scorer1_id,$role_scorer2_id,$role_table_leader_id,$role_room_leader_id) {
         
         $event_scoring_items_excluded="";
         if ($event_user_id !="") {
            $event_scoring_items_excluded=$this->getEventScoringItemsExcluded($event_user_id,$user_role_id, $current_event_id);
         }
         
         //check whether the user or the user's group, if leader, has access to this particular item
         $em = $this->getDoctrine()->getManager();
          
         if ($component_id==2) {//calibration component
           
             $queryBuilder = $em->getRepository('NwpAssessmentBundle:EventScoringItem')
                            ->createQueryBuilder('esi') 
                            ->select('esi.id id, IDENTITY(esi.scoringItem) scoringItem,
                              IDENTITY(esu.status) status, IDENTITY(esu.createdBy) createdBy, esu.maxScoringItemScoreStatus,
                              (case when s.name is null then \'Ready\' else s.name end) statusName, 
                              IDENTITY(si.gradeLevel) gradeLevelId, g.name as gradeLevelName,sa.name actionName'     
                                    )
                            ->leftJoin('NwpAssessmentBundle:EventScoringItemStatusListByUser','esu',
                                                 \Doctrine\ORM\Query\Expr\Join::WITH,
                                               'esu.eventScoringItem = esi.id and esu.createdBy='.$this->container->get('security.context')->getToken()->getUser()->getId())
                           ->Join('NwpAssessmentBundle:ScoringItem','si',
                                                 \Doctrine\ORM\Query\Expr\Join::WITH,
                                               'esi.scoringItem = si.id')
                           ->Join('NwpAssessmentBundle:GradeLevel','g',
                                                 \Doctrine\ORM\Query\Expr\Join::WITH,
                                               'si.gradeLevel = g.id')
                           ->leftJoin('NwpAssessmentBundle:ScoringItemStatus','s',
                                                 \Doctrine\ORM\Query\Expr\Join::WITH,
                                               'esu.status = s.id')
                           ->leftJoin('NwpAssessmentBundle:ScoringItemStatusRoleCapability', 'rc', 'WITH', '(rc.status =esu.status OR (esu.status IS NULL AND rc.status =1)) AND rc.component=esi.component')
                           ->leftJoin('NwpAssessmentBundle:SystemAction', 'sa', 'WITH', 'sa.id = rc.action')
                         
                           ->where('esi.component=2')
                           ->andWhere('esi.event='.$current_event_id)
                           ->andWhere('rc.role='.$user_role_id);
                           if ($action!=null) { //
                                $queryBuilder->AndWhere('sa.name=\''.$action.'\'');
                           } 
                           
                           if ($event_scoring_item_id != null) {
                                $queryBuilder->AndWhere('esi.id='.$event_scoring_item_id);
                           } else {
                                $queryBuilder->AndWhere('esu.id='.$previous_scoringitemstatus);  
                           }
                           if (($user_role_id==$role_scorer1_id) || ($user_role_id==$role_scorer2_id) || ($user_role_id==$role_table_leader_id) || ($user_role_id==$role_room_leader_id)) {
                                 $queryBuilder->andWhere('si.gradeLevel='.$user_grade_level_id);
                           }
                           if ($event_scoring_items_excluded !='') {
                                 $queryBuilder->andWhere('esi.id not in ('.$event_scoring_items_excluded.')');
                           }
               ; 
             
         } else { //regular event queue
            $queryBuilder = $em->getRepository('NwpAssessmentBundle:EventScoringItemStatusList')
                            ->createQueryBuilder('esu')
                             ->select('esu');
                         if (($user_role_id==$role_scorer1_id)|| ($user_role_id==$role_scorer2_id)){
                              $queryBuilder->where('esu.assignedTo='.$this->container->get('security.context')->getToken()->getUser()->getId())   
                                             ->orWhere('esu.createdBy='.$this->container->get('security.context')->getToken()->getUser()->getId())
                                             ->orWhere('esu.statusAssignedAssignedTo='.$this->container->get('security.context')->getToken()->getUser()->getId());; 
                              
                         } else if ($user_role_id==$role_room_leader_id) {
                                $queryBuilder->Where('esu.gradeLevelCreated='.$user_grade_level_id)
                                             ->orWhere('esu.gradeLevelAssigned='.$user_grade_level_id); 
                         } else if ($user_role_id==$role_table_leader_id) {
                                $queryBuilder->Where('esu.gradeLevelCreated='.$user_grade_level_id.' AND esu.tableIdCreated='.$user_table_id)
                                              ->orWhere('esu.gradeLevelAssigned='.$user_grade_level_id.' AND esu.tableIdAssigned='.$user_table_id) 
                                              ;
                          }  
                           $queryBuilder->andWhere('esu.event='.$current_event_id)
                           ->andWhere('esu.id='.$previous_scoringitemstatus);
                           
                           if ($event_scoring_items_excluded !='') {
                                 $queryBuilder->andWhere('esu.eventScoringItem not in ('.$event_scoring_items_excluded.')');
                           }
                 
         }
         
         //echo $queryBuilder->getDql();
            
         $query = $queryBuilder->getQuery();        
             
         try {         
            $previous_entity=$query->getSingleResult();
         } catch (\Doctrine\ORM\NoResultException $e){
            $previous_entity=false;
         }
            
        return $previous_entity;
        
    }
    
     //old function when scorers were updated with one button, instead of yes/no data-toggle checkboxes
    
     public function batchActionEventUserUpdateRoles($scorers,$current_event_id,$role_scorer2_id){
         $error=false;
         
         foreach ($scorers as $scorer) {
             $event_user_id= $scorer->getId();
             $update_role =  $this->get('request')->get("event_user_".$event_user_id);
             
             $sql = "UPDATE Nwp\AssessmentBundle\Entity\EventUser eu set eu.role = ".$update_role." WHERE eu.id = ".$event_user_id; 
                
                try {
                    $em = $this->getDoctrine()->getManager();
                    $q = $em->createQuery($sql);
                    $numUpdated = $q->execute();                  
                    
                    //update any papers with status "Submitted" to status "Accepted" if scorer 1 became scorer 2
                    if ($update_role==$role_scorer2_id) {
                        $status_submitted = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Submitted'));
                        
                        $queryBuilder = $em->getRepository('NwpAssessmentBundle:EventScoringItemStatusList')
                           ->createQueryBuilder('esu')
                           ->select('esu')
                           ->where('esu.createdBy='.$scorer->getUser()->getId())   
                           ->AndWhere('esu.status='.$status_submitted->getId())
                           ->andWhere('esu.event='.$current_event_id)
                           ->andWhere('esu.gradeLevelId='.$scorer->getGradeLevel()->getId());
                       
                        $query = $queryBuilder->getQuery();
                        $submitted_papers= $query->getResult();
                        
                       
                        if (count($submitted_papers)>0) {
                            $status_accepted = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Accepted'));
                            
                            foreach ($submitted_papers as $sp) {   
                                $entity=$this->CreateEventScoringItemStatus($sp,$status_accepted);                         
                                $post_processing=$this->EventScoringItemStatusPostProcessing($current_event_id,$entity,$status_accepted);   
                            }
                        }
                            
                    }//finish updating papers from status "Submitted" to "Accepted"
                } catch(\Doctrine\DBAL\DBALException $e) {
                    $error = true;
                }
         }
         if ($error ==true) {
            $this->get('session')->getFlashBag()->add('error', 'One or more roles could not be updated.');
         } else {
            $this->get('session')->getFlashBag()->add('info', 'Status has been successfully updated.');
         }
         return $error;
     }
    
    /**
    * @Route("/eventscoringitemstatus/updateEventUserRole", name="_updateEventUserRole")
    */ 
    public function UpdateEventUserRole($scorer,$update_role,$current_event_id,$role_scorer2_id){
        
            $error=false;
             
            $event_user_id= $scorer->getId();
             
             
             $sql = "UPDATE Nwp\AssessmentBundle\Entity\EventUser eu set eu.role = ".$update_role." WHERE eu.id = ".$event_user_id; 
             
             echo $sql;
             
                try {
                    $em = $this->getDoctrine()->getManager();
                    $q = $em->createQuery($sql);
                    $numUpdated = $q->execute();                  
                    
                    //update any papers with status "Submitted" to status "Accepted" if scorer 1 became scorer 2
                    if ($update_role==$role_scorer2_id) {
                        $status_submitted = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Submitted'));
                        
                        $queryBuilder = $em->getRepository('NwpAssessmentBundle:EventScoringItemStatusList')
                           ->createQueryBuilder('esu')
                           ->select('esu')
                           ->where('esu.createdBy='.$scorer->getUser()->getId())   
                           ->AndWhere('esu.status='.$status_submitted->getId())
                           ->andWhere('esu.event='.$current_event_id)
                           ->andWhere('esu.gradeLevelId='.$scorer->getGradeLevel()->getId());
                       
                        $query = $queryBuilder->getQuery();
                        $submitted_papers= $query->getResult();
                        
                       
                        if (count($submitted_papers)>0) {
                            $status_accepted = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Accepted'));
                            
                            foreach ($submitted_papers as $sp) {   
                                $entity=$this->CreateEventScoringItemStatus($sp,$status_accepted);                         
                                $post_processing=$this->EventScoringItemStatusPostProcessing($current_event_id,$entity,$status_accepted);   
                            }
                        }
                            
                    }//finish updating papers from status "Submitted" to "Accepted"
                } catch(\Doctrine\DBAL\DBALException $e) {
                    $error = true;
                }
         
         if ($error ==true) {
            $this->get('session')->getFlashBag()->add('error', 'One or more roles could not be updated.');
         } else {
            $this->get('session')->getFlashBag()->add('info', 'Status has been successfully updated.');
         }
         return $error;
     }
     
     public function batchActionEventUserUpdateBlocks($event_user_id, $block){
        
        $error=false; 
       
        $sql = "UPDATE Nwp\AssessmentBundle\Entity\EventUser eu set eu.maxBlock=".$block." WHERE eu.id = ".$event_user_id; 
                
        try {
            $em = $this->getDoctrine()->getManager();
            $q = $em->createQuery($sql);
            $entity= $q->execute();
        
        } catch(\Doctrine\DBAL\DBALException $e) {
                $error = true;
        }
         
        if ($error ==true) {
            $this->get('session')->getFlashBag()->add('error', 'Block could not be activated.');
        } else {
            $this->get('session')->getFlashBag()->add('info', 'Block activated successfully.');
        }
        
        return $error;
     }
     
     public function CreateEventScoringItemStatus($previous_entity=null,$status,$scoring_round_number=null,$read_number=null,$max_scoring_item_score_status=null,$event_scoring_item=null,$assigned_to=null) {
         $em = $this->getDoctrine()->getManager();
         
         $user=$this->container->get('security.context')->getToken()->getUser();
         $time_created = new \DateTime('now'); 
         
         
         if ($scoring_round_number==null) {
             if ($previous_entity) {
                $scoring_round_number = $previous_entity->getScoringRoundNumber();
             }  else {
                 $scoring_round_number=1;
             }
         }
         if ($read_number==null) {
             if ($previous_entity) {
                $read_number = $previous_entity->getReadNumber();
             } else {
                 $read_number=1;
             }
         }
         if ($max_scoring_item_score_status==null) {
            if ($previous_entity) {
                $max_scoring_item_score_status = $previous_entity->getMaxScoringItemScoreStatus();
            }
         }
         
         if ($event_scoring_item==null) {
            if ($previous_entity) {
                $event_scoring_item = $previous_entity->getEventScoringItem();
            }
         }
           
         $entity  = new EventScoringItemStatus();
         $entity->setEventScoringItem($event_scoring_item); 
         $entity->setStatus($status);       
         $entity->setScoringRoundNumber($scoring_round_number);
         $entity->setReadNumber($read_number);
         if ($max_scoring_item_score_status!='reset') {//reset to null for new scoring round, so no need to update this field
            $entity->setMaxScoringItemScoreStatus($max_scoring_item_score_status);  
         } 
         $entity->setCreatedBy($user);
         if ($assigned_to !=null) {
           $entity->setAssignedTo($assigned_to);  
         }
         $entity->setTimeCreated($time_created);
         
         $em->persist($entity);
         $em->flush();
         
         return $entity;
     }
     
      public function UpdateEventScoringItem($entity,$status,$scoring_round_number=null,$read_number=null) {
         $em = $this->getDoctrine()->getManager();
          
         $user=$this->container->get('security.context')->getToken()->getUser();
         $date_updated = new \DateTime('now');
         
          
         $parent_entity=$em->getRepository('NwpAssessmentBundle:EventScoringItem')->find($entity->getEventScoringItem());
         
         $parent_entity->setStatus($status);
         
         if ($scoring_round_number!=null) {
            $parent_entity->setScoringRoundNumber($scoring_round_number);
         }
         if ($read_number!=null) {
            $parent_entity->setReadNumber($read_number);
         }   
         
         $parent_entity->setmaxEventScoringItemStatus($entity);        
         $parent_entity->setDateUpdated($date_updated);
         
         $em->persist($parent_entity);
         $em->flush(); 
         return $parent_entity;
      }
     
     public function EventScoringItemStatusPostProcessing($current_event_id,$entity,$status) {
         //Create EventScoringItemStatus entity and update EventScoringItem (we never update EventScoringItemStatus since we keep track of all status history)
          $em = $this->getDoctrine()->getManager();
         
         $status_ready = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Ready')); 
         $status_accepted = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Accepted'));
         $status_adjudicate = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Adjudicate'));
         
         //If double-scored paper is accepted and it is not an adjudication (round number 3) 
         if (($status->getId()==$status_accepted->getId()) && ($entity->getEventScoringItem()->getScoringItemType()->getId()==2) && ($entity->getScoringRoundNumber() !=3)) {
            if ($entity->getScoringRoundNumber()==1) {
                //insert Ready status into event_scoring_item_status table
                $scoring_round_number=$entity->getScoringRoundNumber()+1;
                $read_number=1;
                $max_scoring_item_score_status='reset';
                $entity2 = $this->CreateEventScoringItemStatus($entity,$status_ready,$scoring_round_number,$read_number,$max_scoring_item_score_status,null,null);
                $parent_entity_updated=$this->UpdateEventScoringItem($entity2,$status_ready,$scoring_round_number,$read_number);              
            } elseif ($entity->getScoringRoundNumber()==2) {
                //figure out if it has to be Adjudicated 
                $adjudicate_attributes = $this->checkAdjudication($current_event_id,$entity->getEventScoringItem()->getId(),$status_accepted->getId());
                       
                if ($adjudicate_attributes !="") {
                    //insert Adjudicated status into event_scoring_item_status table
                    $scoring_round_number=$entity->getScoringRoundNumber()+1;
                    $read_number=1;
                    $max_scoring_item_score_status='reset';
                    $entity2 = $this->CreateEventScoringItemStatus($entity,$status_adjudicate,$scoring_round_number,$read_number,$max_scoring_item_score_status,null,null);        
                    //update current status in event_scoring_item table for adjudication
                    $parent_entity_updated=$this->UpdateEventScoringItem($entity2,$status_adjudicate,$scoring_round_number,$read_number); 
                    
               }  else { //no adjudication needed, update event_scoring_item table with status accepted
                    $parent_entity_updated=$this->UpdateEventScoringItem($entity,$status);
               }
            } 
         } else { //Single-scored paper, update status in event_scoring_item table
            $parent_entity_updated=$this->UpdateEventScoringItem($entity,$status,null,$entity->getReadNumber());                 
         }
            
         return true;
     }
     
     public function getEventScoringItemStatusHistory($entity,$user_role_id,$role_admin_id) {   
        //get Status History
        //added logic on 05-06-14 to not include "under review" statuses
        $em = $this->getDoctrine()->getManager();

        $queryBuilder = $em->getRepository('NwpAssessmentBundle:EventScoringItemStatus')
                           ->createQueryBuilder('esu')
                           ->select('esu')
                           ->leftJoin('esu.status', 's')
                           ->Where('esu.eventScoringItem='.$entity->getEventScoringItem()->getId());
        $queryBuilder->AndWhere('esu.scoringRoundNumber='.$entity->getScoringRoundNumber())
                     ->AndWhere('esu.readNumber='.$entity->getReadNumber())
                     ->AndWhere('s.isReview !=1');
                    if ($user_role_id!=$role_admin_id) {
                        $status_ready = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Ready'))->getId();
                        $queryBuilder->AndWhere('s.id !='.$status_ready);
                    }
        $queryBuilder->orderBy('esu.id', 'DESC');

        $query = $queryBuilder->getQuery();
        $status_history= $query->getResult();
         
        return $status_history;
    }
    
    public function getUsersScoredPaper($event_scoring_item_id) {
         
          //05-08-14 logic changed so that any Table Leader can be assigned to adjudicate, except if he already scored or had access to the paper in a previous scoring round
          $em = $this->getDoctrine()->getManager();
          
          $status_under_adjudication = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Under Adjudication'));
          $status_adjudicate = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Adjudicate'));
        
          
          $qb=$em->createQueryBuilder();
          $sql="SELECT DISTINCT created_by AS user_id
                FROM event_scoring_item_status WHERE event_scoring_item_id=".$event_scoring_item_id." 
                AND created_by IS NOT NULL AND status_id not in (".$status_adjudicate->getId().",".$status_under_adjudication->getId().")
                UNION
                SELECT DISTINCT assigned_to AS user_id
                FROM event_scoring_item_status WHERE event_scoring_item_id=".$event_scoring_item_id." 
                AND assigned_to IS NOT NULL AND status_id not in (".$status_adjudicate->getId().",".$status_under_adjudication->getId().")";
             
          $dbh= $this->get('database_connection');
          $stmt = $dbh->query($sql); 
          $users_scored = $stmt->fetchAll(\PDO::FETCH_ASSOC);
             
          return $users_scored;
    }
    
    public function getEventScoringItemsExcluded($event_user_id,$user_role_id, $current_event_id) {
        
        //get System Roles
        $system_roles_array = $this->getSystemRoles();
        $role_event_leader_id=$system_roles_array['Event Leader'];
        $role_room_leader_id=$system_roles_array['Room Leader'];
        $role_table_leader_id=$system_roles_array['Table Leader'];
        $role_scorer1_id=$system_roles_array['Scorer 1'];
        $role_scorer2_id = $system_roles_array['Scorer 2']; 
        $role_admin_id=$system_roles_array['Admin'];
        
        $table_leader_event_user_id="";
      
        if (($user_role_id==$role_scorer1_id) || ($user_role_id==$role_scorer2_id)) {
            $user_id=$this->container->get('security.context')->getToken()->getUser()->getId();
            $table_leader_result = $this->getTableLeader($user_id,$current_event_id,$role_table_leader_id);
            if ($table_leader_result) {
                $table_leader_event_user_id = $table_leader_result [0]->getId();
            }
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $qb=$em->createQueryBuilder();
        
        //get event_scoring_item_ids that should be excluded, in comma separated string format
        $sql="SELECT GROUP_CONCAT(esig.event_scoring_item_id) FROM event_user_grouping eug 
              JOIN event_scoring_item_grouping esig ON eug.grouping_id=esig.grouping_id
              WHERE ";
        if ($table_leader_event_user_id!="") {
           $sql.="("; 
        }  
         
        $sql.= "eug.event_user_id=".$event_user_id;
            
        if ($table_leader_event_user_id!="") {
            $sql.=" OR eug.event_user_id=".$table_leader_event_user_id.")";
        }
        //echo "<br>Exclusion SQL:".$sql."<br><br>";
             
        $dbh= $this->get('database_connection');
        $stmt = $dbh->query($sql); 
        $event_scoring_items_excluded = $stmt->fetchColumn();   
        
        return $event_scoring_items_excluded;
        
    }
    
    public function getEventUsersExcluded($event_scoring_item_id,$current_event_id) {
        
        //get System Roles
        $system_roles_array = $this->getSystemRoles();
        $role_event_leader_id=$system_roles_array['Event Leader'];
        $role_room_leader_id=$system_roles_array['Room Leader'];
        $role_table_leader_id=$system_roles_array['Table Leader'];
        $role_scorer1_id=$system_roles_array['Scorer 1'];
        $role_scorer2_id = $system_roles_array['Scorer 2']; 
        $role_admin_id=$system_roles_array['Admin'];
        
        $em = $this->getDoctrine()->getManager();
        
        $qb=$em->createQueryBuilder();
        
        //get event_scoring_item_ids that should be excluded, in comma separated string format
        $sql="SELECT GROUP_CONCAT(DISTINCT(eug.event_user_id)) FROM event_scoring_item_grouping esig
              JOIN event_user_grouping eug ON eug.grouping_id =esig.grouping_id
              WHERE esig.event_scoring_item_id=".$event_scoring_item_id;
            
        
        //echo "<br>Exclusion SQL:".$sql."<br><br>";
             
        $dbh= $this->get('database_connection');
        $stmt = $dbh->query($sql); 
        $event_users_excluded = $stmt->fetchColumn();   
        
        return $event_users_excluded;
        
    }
    
    public function getPaperTotalsByGradeLevel($user_role_id,$user_grade_level_id, $current_event_id) {
        
        $paper_count_total=array();
        
        //get System Roles
        $system_roles_array = $this->getSystemRoles();
        $role_event_leader_id=$system_roles_array['Event Leader'];
        $role_room_leader_id=$system_roles_array['Room Leader'];
        $role_table_leader_id=$system_roles_array['Table Leader'];
        $role_scorer1_id=$system_roles_array['Scorer 1'];
        $role_scorer2_id = $system_roles_array['Scorer 2']; 
        $role_admin_id=$system_roles_array['Admin'];
        
        //get total papers (reads) in event's grade level (single-scored reads + double-scored both reads)
        $dbh= $this->get('database_connection');
       
        $sql="SELECT si.grade_level_id, g.name grade_level_name,
              scoring_item_type_id, COUNT(*) number_of_papers FROM event_scoring_item esi
              JOIN scoring_item si on si.id=esi.scoring_item_id
              JOIN grade_level g ON g.id = si.grade_level_id
              WHERE esi.event_id=".$current_event_id." AND esi.component_id = 1 ";
        if (($user_role_id !=$role_admin_id) && ($user_role_id !=$role_event_leader_id)) {
            $sql.=" AND si.grade_level_id =".$user_grade_level_id;
        }
        
        $sql.=" GROUP BY si.grade_level_id, esi.scoring_item_type_id";
        $sth = $dbh->prepare($sql); 
        $sth->execute();
        $paper_count = $sth->fetchAll(\PDO::FETCH_ASSOC);

        $paper_count_array_size=sizeof($paper_count);
        
        $grade_level_array=array();
        
        for($p=0;$p<$paper_count_array_size;$p++){  
            $grade_level = $paper_count[$p]['grade_level_id'];
            $grade_level_array[$grade_level]['grade_level_name']=$paper_count[$p]['grade_level_name'];
            if (!isset ($paper_count_total[$grade_level])) {
                $paper_count_total[$grade_level]=0;
            }
            if ($paper_count[$p]['scoring_item_type_id']==1) {
                $paper_count_total[$grade_level] +=$paper_count[$p]['number_of_papers'];
            } elseif ($paper_count[$p]['scoring_item_type_id']==2) {
                $paper_count_total[$grade_level] +=($paper_count[$p]['number_of_papers']*2);
            }
	} 
         return $paper_count_total;
    }
    
    public function getPapersAssignedUser($event_user_id,$current_event_id,$status_assigned) {
        $paper_count_total_user_assigned="";
        if ($event_user_id !="") {
            $dbh= $this->get('database_connection');
           
            $sql=   "SELECT COUNT(*) total_all_paper_count FROM event_scoring_item esi
                    JOIN event_scoring_item_status esis ON esi.id=esis.event_scoring_item_id
                    JOIN event_user eu on eu.event_id=esi.event_id and eu.user_id=esis.assigned_to
                    WHERE esi.event_id = ".$current_event_id." AND esi.component_id = 1 ".
                    " AND eu.id=".$event_user_id." AND esis.status_id=".$status_assigned;

            $sth = $dbh->prepare($sql); 
            $sth->execute();
            $paper_count_total_user_assigned = $sth->fetchColumn();
        }
        return $paper_count_total_user_assigned;
    }
    
    public function getPapersAssignedByGradeLevel($user_role_id,$user_grade_level_id, $current_event_id, $status_assigned) {
         
        $paper_count_grade_level_assigned_array=array();
        $dbh= $this->get('database_connection');
        
        //get System Roles
        $system_roles_array = $this->getSystemRoles();
        $role_event_leader_id=$system_roles_array['Event Leader'];
        $role_room_leader_id=$system_roles_array['Room Leader'];
        $role_table_leader_id=$system_roles_array['Table Leader'];
        $role_scorer1_id=$system_roles_array['Scorer 1'];
        $role_scorer2_id = $system_roles_array['Scorer 2']; 
        $role_admin_id=$system_roles_array['Admin'];
        
        //get total papers (read) in event that have already been assigned to everyone per grade level
        $sql=   "SELECT si.grade_level_id, g.name grade_level_name,
                COUNT(*) total_all_papers_assigned FROM event_scoring_item esi
                JOIN event_scoring_item_status esis ON esi.id=esis.event_scoring_item_id
                JOIN scoring_item si on si.id=esi.scoring_item_id
                JOIN grade_level g ON g.id = si.grade_level_id
                WHERE esi.event_id = ".$current_event_id." AND esi.component_id = 1";
         if (($user_role_id !=$role_admin_id) && ($user_role_id !=$role_event_leader_id)) {
                $sql.=" AND si.grade_level_id = ".$user_grade_level_id;
         }
                $sql.=" AND esis.status_id=".$status_assigned.
                        " GROUP BY si.grade_level_id ";
                
        $sth = $dbh->prepare($sql); 
        $sth->execute();
        $paper_count_grade_level_assigned = $sth->fetchAll(\PDO::FETCH_ASSOC);
         
        $paper_count_grade_level_assigned_size=sizeof($paper_count_grade_level_assigned);
        
        $grade_level_array=array();
        
        $em = $this->getDoctrine()->getManager();
        $qb=$em->createQueryBuilder();
        
        for($p=0;$p<$paper_count_grade_level_assigned_size;$p++){  
            $grade_level = $paper_count_grade_level_assigned[$p]['grade_level_id'];
            $paper_count_grade_level_assigned_array[$grade_level]['grade_level_name']=$paper_count_grade_level_assigned[$p]['grade_level_name'];
            $paper_count_grade_level_assigned_array[$grade_level]['total_all_papers_assigned']=$paper_count_grade_level_assigned[$p]['total_all_papers_assigned'];
	}
        
        return $paper_count_grade_level_assigned_array;
    }
    
    public function getBlocksCapability($user_role_id,$user_grade_level_id, $user_table_id, $current_event_id,$paper_count_total,$paper_count_grade_level_assigned_array) {
        
        //get System Roles
        $system_roles_array = $this->getSystemRoles();
        $role_event_leader_id=$system_roles_array['Event Leader'];
        $role_room_leader_id=$system_roles_array['Room Leader'];
        $role_table_leader_id=$system_roles_array['Table Leader'];
        $role_scorer1_id=$system_roles_array['Scorer 1'];
        $role_scorer2_id = $system_roles_array['Scorer 2']; 
        $role_admin_id=$system_roles_array['Admin'];
        
        $block_capability_array=array();
        $block_min=0;
        
        
        $em = $this->getDoctrine()->getManager();
        
        $queryBuilder = $em->getRepository('NwpAssessmentBundle:EventGradeLevelBlock')->createQueryBuilder('b')
                            ->Where('b.event='.$current_event_id);
                            if (($user_role_id!=$role_event_leader_id) && ($user_role_id!=$role_admin_id)) {
                                $queryBuilder->andWhere('b.gradeLevel='.$user_grade_level_id);
                            }
                            $queryBuilder->orderBy('b.gradeLevel,b.blockId', 'ASC');
                            
        $query = $queryBuilder->getQuery();
        $blocks=$query->getResult();
        
        $block_count=count($blocks);
        
        if ($block_count >0) {
            
            foreach ($blocks as $block) {  
                $block_prompt_names="";
                $block_prompts_count=0;
                
                $grade_level=$block->getGradeLevel()->getId();
                $blockNumber=$block->getBlockId();

                if ($block->getTarget() !=null) {
                    $target_block=$block->getTarget();
                } else {
                    $target_block=0; 
                }
                
                //$block_prompts=$block->getPrompt();
                
                $queryBuilder2 = $em->getRepository('NwpAssessmentBundle:EventGradeLevelBlockPrompt')->createQueryBuilder('p')
                            ->Where('p.eventGradeLevelBlock='.$block->getId());
                            if (($user_role_id!=$role_event_leader_id) && ($user_role_id!=$role_admin_id) && ($user_role_id!=$role_room_leader_id)) {
                                $queryBuilder2->andWhere('p.tableId='.$user_table_id);
                            }
                           
                            
                $query2 = $queryBuilder2->getQuery();
                $block_prompts=$query2->getResult();
               
                foreach($block_prompts as $bp) {
                    //formatting for writing out prompt names
                    //if there's more than 1 prompt and we're writing out the last prompt, add an "and"
                    if (($block_prompts_count==count($block_prompts)-1) && (count($block_prompts)>1)) {
                        $block_prompt_names .=  " and ";
                    }
                    $block_prompt_names .=  $bp->getPrompt();
                    //formatting for writing out prompt names
                    //if there's more than 2 prompts and we're not on the last prompt, add a comma with space
                    if (($block_prompts_count < count($block_prompts)-1) && (count($block_prompts)>2)) {
                        $block_prompt_names .=  ", ";
                    }
                    
                    $block_prompts_count++;
                }
                //$block_prompt_names = substr($block_prompt_names, 0, -1); //strip last comma
               
                if (isset($paper_count_total[$grade_level])) {
                    $target_block_total=ceil(($paper_count_total[$grade_level] * $target_block)/100); //round up to nearest integer
                    $block_max=$target_block_total;
                    $block_capability_array[$grade_level][$blockNumber]['block_target_percent']=$target_block;
                    $block_capability_array[$grade_level][$blockNumber]['block_prompt']=$block_prompt_names;
                    $block_capability_array[$grade_level][$blockNumber]['min']=$block_min;
                    if ($blockNumber < $block_count) {
                        $block_capability_array[$grade_level][$blockNumber]['max']=$block_min+$block_max;
                    } else {
                        //if last block, don't limit their max, set it to maximum number of papers uploaded for this grade level
                        $block_capability_array[$grade_level][$blockNumber]['max']=$paper_count_total[$grade_level];
                    }
                    
                    $block_min=$block_capability_array[$grade_level][$blockNumber]['max']+1;
                }
              
            } //end of for loop

            //set block statistics in 0 based array
            foreach ($block_capability_array as $key => $value) {
                 if (isset ($paper_count_grade_level_assigned_array[$key])) {
                     
                     foreach ($block_capability_array[$key] as $block_key => $value) {
                        $block_capability_array[$key][0]['block_count']=$block_count;
                        if (($paper_count_grade_level_assigned_array[$key]['total_all_papers_assigned']+1 >=$block_capability_array[$key][$block_key]['min']) 
                           && ($paper_count_grade_level_assigned_array[$key]['total_all_papers_assigned']+1 <=$block_capability_array[$key][$block_key]['max']) ) 
                        {
                            $block_capability_array[$key][0]['current_block']=$block_key;
                            $block_capability_array[$key][0]['total_all_papers_assigned']=$paper_count_grade_level_assigned_array[$key]['total_all_papers_assigned'];
                        }                    
                     }
                 } else { //0 were assigned, so set everything to 0
                     $block_capability_array[$key][0]['block_count']=$block_count;
                     $block_capability_array[$key][0]['current_block']=1;
                     $block_capability_array[$key][0]['total_all_papers_assigned']=0;
                     
                 }
            }
        } //end of if statement processing for blocks > 0 
        
        return $block_capability_array;
    }
    
    public function getUserBlocksCapability($event_user_id,$user_grade_level_id,$user_role_id,$target_percent_user,$max_block_user,$paper_count_total,$paper_count_total_user_assigned,$block_capability_array) {
        
        //get System Roles
        $system_roles_array = $this->getSystemRoles();
        $role_event_leader_id=$system_roles_array['Event Leader'];
        $role_room_leader_id=$system_roles_array['Room Leader'];
        $role_table_leader_id=$system_roles_array['Table Leader'];
        $role_scorer1_id=$system_roles_array['Scorer 1'];
        $role_scorer2_id = $system_roles_array['Scorer 2']; 
        $role_admin_id=$system_roles_array['Admin']; 
        
        $user_block_min=0;
        $user_block_capability_array=array();
 
        if (((isset($paper_count_total[$user_grade_level_id])) && ($paper_count_total[$user_grade_level_id]>0))
           && (isset($block_capability_array[$user_grade_level_id])))
        {
            if (($target_percent_user !=null) && ($target_percent_user !=0)) {
                $target_user=ceil(($paper_count_total[$user_grade_level_id] * $target_percent_user)/100); //round up to nearest integer
            } else {
                $target_user=0;
            }    
            //initiate statistics in array base 0 for each user to help keep track
            $user_block_capability_array[0]['user_target_percent']=$target_percent_user;
            $user_block_capability_array[0]['user_max_block']=$max_block_user;
            $user_block_capability_array[0]['user_total_target_papers']=$target_user;
            $user_block_capability_array[0]['user_papers_assigned']=$paper_count_total_user_assigned;
            //defaults, these will be reset later in the function if stats are available
            $user_block_capability_array[0]['user_current_block']=null;
            $user_block_capability_array[0]['block_quota_reached']=null;
            $user_block_capability_array[0]['user_block_quota_reached']=null;
            
            $block_count=sizeof($block_capability_array[$user_grade_level_id])-1;
            #foreach ($block_capability_array as $key => $value) {
                
                foreach ($block_capability_array[$user_grade_level_id] as $blockNumber=> $value2) {
                    if ($blockNumber !=0) {
                        $block_target_percent=$block_capability_array[$user_grade_level_id][$blockNumber]['block_target_percent'];
                        if (($block_target_percent > 0) && ($target_user>0)) {
                            $target_block_user=ceil(($block_target_percent * $target_user)/100); //round up to nearest integer
                            $user_block_max=$target_block_user;
                            $user_block_capability_array[$blockNumber]['min']=$user_block_min;
                            if ($blockNumber < $block_count) {
                                $user_block_capability_array[$blockNumber]['max']=$user_block_min+$user_block_max;
                            } else { //if last block, don't limit their max, set it to maximum number of papers uploaded for this grade level
                                $user_block_capability_array[$blockNumber]['max']=$paper_count_total[$user_grade_level_id];
                            }
                            $user_block_min=$user_block_capability_array[$blockNumber]['max']+1;
                        } else { //defaults
                            $target_block_user=0;
                            $user_block_capability_array[$blockNumber]['min']=0;
                            $user_block_capability_array[$blockNumber]['max']=0;
                        }
                        //check whether this block is user's current block and set this statistic (before going on to next block)
                        if (($paper_count_total_user_assigned+1 >=$user_block_capability_array[$blockNumber]['min']) 
                            && ($paper_count_total_user_assigned+1 <=$user_block_capability_array[$blockNumber]['max']) ) 
                        {
                            $user_block_capability_array[0]['user_current_block']=$blockNumber;
                            
                            //if overall current block is greater than user's current block 
                            if ($block_capability_array[$user_grade_level_id][0]['current_block']>$user_block_capability_array[0]['user_current_block'])
                               //if any other role than Scorer1 and Scorer2, set user's current block to overall current block 
                            {
                                 if (($user_role_id!=$role_scorer1_id) || ($user_role_id!=$role_scorer2_id)) {
                                        $user_block_capability_array[0]['user_current_block']=$block_capability_array[$user_grade_level_id][0]['current_block'];
                                 } else { //for Scorer1 and Scorer2 roles, check if this max block is greater than overall current block
                                     if ($block_capability_array[$user_grade_level_id][0]['current_block'] <=$user_block_capability_array[0]['user_max_block']){
                                          $user_block_capability_array[0]['user_current_block']=$block_capability_array[$user_grade_level_id][0]['current_block'];
                                     }
                                 }
                            }
                           
                            //Only scorer1 and scorer2 have Rules 2 and 3 that need to be checked
                            //Other roles do not need these rules
                            if (($user_role_id==$role_scorer1_id) || ($user_role_id==$role_scorer2_id)) {
                                //Rule 2 - can pull papers based on overall block totals?
                                if ($block_capability_array[$user_grade_level_id][0]['current_block']>$user_block_capability_array[0]['user_current_block']) {
                                    $user_block_capability_array[0]['block_quota_reached']=1;
                                } else {
                                    $user_block_capability_array[0]['block_quota_reached']=0;
                                }
                                 //Rule 3 - can pull papers based on individual block totals?
                                if ($user_block_capability_array[0]['user_current_block']>$user_block_capability_array[0]['user_max_block']) 
                                {
                                    $user_block_capability_array[0]['user_block_quota_reached']=1;
                                    //echo "<br>user's count falls within current block";
                                } else {
                                    $user_block_capability_array[0]['user_block_quota_reached']=0;
                                    //echo "<br>user's count DOES NOT fall within current block";
                                } 
                            } //end of socrer1 and scorer2 rules processing
                        } //end of current block processing
                    } //end of $blockNumber does not equal zero if statement  
                } //end of inner for loop
            #} //end of outer for
             
            //set final statistics after all blocks are processed
            $user_current_block = $user_block_capability_array[0]['user_current_block'];
            
            //check if they've finished previous block and are ready to go on to the next block (for contratulations msg.)
            if (($user_current_block > 1) && ($paper_count_total_user_assigned==$user_block_capability_array[$user_current_block-1]['max'])) {
                 $user_block_capability_array[0]['user_next_block_ready']=1;
            } else {
                 $user_block_capability_array[0]['user_next_block_ready']=0;
            }
             
            //set numerator for current block
            if ($paper_count_total_user_assigned>$user_block_capability_array[$user_current_block]['min']) {
                $user_block_capability_array[0]['user_current_block_numerator']=$paper_count_total_user_assigned-$user_block_capability_array[$user_current_block]['min'];
            } else {
                $user_block_capability_array[0]['user_current_block_numerator']=0; 
            }     
                            
            //set denominator for current block
            if ($block_count>$user_current_block){
                $user_block_capability_array[0]['user_current_block_denominator']=$user_block_capability_array[$user_current_block]['max']-$user_block_capability_array[$user_current_block]['min'];
            } else {
                $user_block_capability_array[0]['user_current_block_denominator']= $target_user-$user_block_capability_array[$user_current_block]['min'];
            }
            
            //set block status for current block
            if ($user_block_capability_array[0]['user_max_block'] < $user_block_capability_array[0]['user_current_block']) {
               $user_block_capability_array[0]['user_current_block_status'] ="Needs Activation";
            } else { //user max block >= user_current_block
                if ($user_block_capability_array[0]['user_current_block_numerator']==0) {
                    $user_block_capability_array[0]['user_current_block_status']="Not Started";
                }
                if ($user_block_capability_array[0]['user_current_block_numerator']>0) {
                    $user_block_capability_array[0]['user_current_block_status']="In Progress";
                }
                if ($user_block_capability_array[0]['user_current_block_numerator']==$user_block_capability_array[0]['user_current_block_denominator']) {
                    $user_block_capability_array[0]['user_current_block_status']="Complete";
                }
                
            }
                               
        }   //end of if statement 
        
        return $user_block_capability_array;
    }
    
     
}
