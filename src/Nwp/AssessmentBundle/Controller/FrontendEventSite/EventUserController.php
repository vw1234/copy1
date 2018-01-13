<?php

namespace Nwp\AssessmentBundle\Controller\FrontendEventSite;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrapView;

use Nwp\AssessmentBundle\Entity\EventUser;
use Nwp\AssessmentBundle\Form\EventUserFilterType;

use Nwp\AssessmentBundle\Controller\BaseController;

/**
 * EventUser controller.
 *
 * @Route("/eventsite/eventuser")
 */
class EventUserController extends BaseController
{
    /**
     * Lists all EventUser entities.
     *
     * @Route("/", name="eventsite_eventuser")
     * @Template()
     */
    public function indexAction()
    {
        $current_event_id = $this->getCurrentEvent();
       
        if ($current_event_id =="" || !(is_numeric($current_event_id))) {
            return $current_event_id; //redirect to index page
         }
         
        $em = $this->getDoctrine()->getManager();
        
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
         
        $queryBuilder =$em->getRepository('NwpAssessmentBundle:EventUser')
                          ->createQueryBuilder('eu')
                          ->select('eu');
                          $queryBuilder->andWhere('eu.event='.$current_event_id)
                         // ->groupBy('eu.tableId, eu.id')
                          ->orderBy('eu.gradeLevel,eu.tableId, eu.role', 'ASC')
                   ;      
        $query = $queryBuilder->getQuery();
        $entities= $query->getResult();
        
        $entities_array_size=sizeof($entities);
        $entities_array=array();
        $grade_level_array=array();
        $target_count_array=array();
        
        $warning_msg=false;
        
           for($e=0;$e<$entities_array_size;$e++){  
               if ($entities[$e]->getGradeLevel() !=null ) {
                   $grade_level=$entities[$e]->getGradeLevel()->getId();
                   $grade_level_name=$entities[$e]->getGradeLevel()->getName();

               } else {
                   $grade_level=0; 
                   $grade_level_name="";
               }
               if ($entities[$e]->getTableId() !=null) {
                  
                   $table_id=$entities[$e]->getTableId();
               } else {
                 
                   $table_id=0; 
               }
               if ($entities[$e]->getTarget() !=null) {
                   $target=$entities[$e]->getTarget();
               } else {
                   $target=0; 
               }
              
               $grade_level_array[$grade_level]=$grade_level_name;
               $entities_array[$grade_level][$table_id]['username'][]= $entities[$e]->getUser()->getUsername();
               $entities_array[$grade_level][$table_id]['firstname'][]= $entities[$e]->getUser()->getFirstName();
               $entities_array[$grade_level][$table_id]['lastname'][]= $entities[$e]->getUser()->getLastName();
               $entities_array[$grade_level][$table_id]['role'][]= $entities[$e]->getRole()->getName();
               $entities_array[$grade_level][$table_id]['role_display'][]= $entities[$e]->getRole()->getDisplayName();
               $entities_array[$grade_level][$table_id]['grade_level_name'][]= $grade_level_name;
               $entities_array[$grade_level][$table_id]['target'][]=  $target;
               
               if (!isset($target_count_array[$grade_level]['target_total'])) {//keep track of count by attribute id
                    $target_count_array[$grade_level]['target_total']=0;
               }
               if ($target !=0) {
                    $target_count_array[$grade_level]['target_total'] +=$target;
               }   
               }
              
   
        //var_dump($entities_array);
        foreach ($grade_level_array as $key => $value) {
            if ($target_count_array[$grade_level]['target_total']< 100) { //at least one grade level has less than 100% target total
                $warning_msg=true;
            }
            
        }
        return array(
            'entities' => $entities,
            'grade_level_array' => $grade_level_array,
            'entities_array' => $entities_array,
            'target_count_array' => $target_count_array,
            'warning_msg' => $warning_msg,
            'user_role_id' => $user_role_id,
            'role_admin_id' => $role_admin_id,
            'role_event_leader_id' => $role_event_leader_id,
            'role_room_leader_id' => $role_room_leader_id,
            'role_table_leader_id' => $role_table_leader_id,
            'role_scorer1_id' => $role_scorer1_id,
            'role_scorer2_id' => $role_scorer2_id   
        );
    }

   
    
    /**
     * Finds and displays a EventUser entity.
     *
     * @Route("/{id}/show", name="eventsite_eventuser_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NwpAssessmentBundle:EventUser')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find EventUser entity.');
        }

        return array(
            'entity'      => $entity,
        );
    }

}
