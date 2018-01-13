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


use Nwp\AssessmentBundle\Form\EventGradeLevelBlockFilterType;



/**
 * EventGradeLevelBlock controller.
 *
 * @Route("/eventsite")
 */
class EventGradeLevelBlockController extends BaseController
{
    /**
     * Lists all EventScoringItemStatus entities.
     *
    
     * @Route("/eventgradelevelblock", name="eventsite_block")
     * @Template()
     */
    public function indexAction()
    {
        $current_event_id = $this->getCurrentEvent();
        
       
        
        if ($current_event_id =="" || !(is_numeric($current_event_id))) {
              return $current_event_id; //redirect to index page
        }
        
        $event_capability_array = $this->getUserEventRoleCapabilities();
        $user_role_id = $event_capability_array[$current_event_id][0]['role_id'];
        $user_grade_level_id = $event_capability_array[$current_event_id][0]['grade_level_id'];
        $user_table_id = $event_capability_array[$current_event_id][0]['table_id'];
        $event_type_id = $event_capability_array[$current_event_id][0]['event_type_id'];
         
        if (!$this->checkAccess("list",null,"EventGradeLevelBlock")) {
            throw new AccessDeniedException();
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
        
        if ($user_role_id !=$role_admin_id) { //everyone but Admin should be in event_user table as an attendee
            $event_user_id=$event_capability_array[$current_event_id][0]['id'];
        }
        
        
        if ($this->get('request')->get('btn_set_block')) {
            $error=$this->setEventGradeBlock($this->get('request')->get('btn_set_block'),$this->get('request')->get('is_active'),$current_event_id); 
            if ($error==false) { //refresh the index page to show new roles
                return $this->redirect($this->generateUrl('eventsite_block'));   
            }
        }
        
  
        list($filterForm, $queryBuilder) = $this->filter();
        
        $query = $queryBuilder->getQuery();
        $entities= $query->getResult();

       
        list($entities, $pagerHtml) = $this->paginatorArray($entities);
        
        //Keep track of whether a block in a room is already active.  Only 1 block per room can be active at a time
        $event_grade_level_block_active=array();
        $grade_level_array=array();
        $target_count_array=array();
        $warning_msg=false;
        
        foreach ($entities as $entity) {
            if ($entity->getGradeLevel() !=null ) {
                   $grade_level=$entity->getGradeLevel()->getId();
                   $grade_level_name=$entity->getGradeLevel()->getName();
            }
            if ($entity->getTarget() !=null) {
                   $target=$entity->getTarget();
               } else {
                   $target=0; 
               }
            if ($entity->getIsActive()==1) {
                $event_grade_level_block_active[$entity->getGradeLevel()->getId()]=$entity->getBlockId();;
            }
            
            $grade_level_array[$grade_level]=$grade_level_name;
            if (!isset($target_count_array[$grade_level]['target_total'])) {//keep track of count by attribute id
                    $target_count_array[$grade_level]['target_total']=0;
            }
            if ($target !=0) {
                $target_count_array[$grade_level]['target_total'] +=$target;
            } 
        }
        
        foreach ($grade_level_array as $key => $value) {
            if ($target_count_array[$grade_level]['target_total']< 100) { //at least one grade level has less than 100% target total
                $warning_msg=true;
            }
            
        }
        
        return array(
            'entities' => $entities,
            'event_type_id' => $event_type_id,
            'event_grade_level_block_active' => $event_grade_level_block_active,
            'grade_level_array' => $grade_level_array,
            'target_count_array' => $target_count_array,
            'warning_msg' => $warning_msg,
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
        
        $status_capability_array = $this->getUserStatusListQueue($user_role_id);
        $url =  $this->getRequest()->getPathInfo();
        
        $filter_name = "EventGradeLevelBlockControllerFilter"; //Assume this is the Queue, not Recent Papers reporting page    

      
        $request = $this->getRequest();
        $session = $request->getSession();
        
        $user_id=$this->container->get('security.context')->getToken()->getUser()->getId();
        
        $em = $this->getDoctrine()->getManager();
        
        //get System Roles
        $system_roles_array = $this->getSystemRoles();
        $role_event_leader_id=$system_roles_array['Event Leader'];
        $role_room_leader_id=$system_roles_array['Room Leader'];
        $role_table_leader_id=$system_roles_array['Table Leader'];
        $role_scorer1_id=$system_roles_array['Scorer 1'];
        $role_scorer2_id = $system_roles_array['Scorer 2']; 
        $role_admin_id=$system_roles_array['Admin']; 
        
        $event_user_id="";
        
        if ($user_role_id !=$role_admin_id) { //everyone but Admin should be in event_user table as an attendee
            $event_user_id=$event_capability_array[$current_event_id][0]['id'];
        }
         
        $filterForm = $this->createForm(new EventGradeLevelBlockFilterType($current_event_id,$user_role_id,$user_grade_level_id,$user_table_id,$role_scorer1_id,$role_scorer2_id,$role_table_leader_id,$role_room_leader_id,$role_event_leader_id,$role_admin_id));
       
        
        $queryBuilder = $em->getRepository('NwpAssessmentBundle:EventGradeLevelBlock')->createQueryBuilder('b')
                            ->Where('b.event='.$current_event_id);
                            if (($user_role_id!=$role_event_leader_id) && ($user_role_id!=$role_admin_id)) {
                                $queryBuilder->andWhere('b.gradeLevel='.$user_grade_level_id);
                            }
                            
                            $queryBuilder->orderBy('b.gradeLevel,b.blockId', 'ASC');
                
                        
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
                $filterForm = $this->createForm(new EventGradeLevelBlockFilterType($current_event_id,$user_role_id,$user_grade_level_id,$user_table_id,$role_scorer1_id,$role_scorer2_id,$role_table_leader_id,$role_room_leader_id,$role_event_leader_id,$role_admin_id), $filterData);
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
    
       
     public function setEventGradeBlock($id, $is_active, $current_event_id){
         
         $error=false;
         
         if ($is_active==0) {
             //activate
              $start_date  = date('Y-m-d H:i:s');
              $end_date='NULL';
              
              $sql = "UPDATE Nwp\AssessmentBundle\Entity\EventGradeLevelBlock b set b.isActive = 1, b.startDate='$start_date',b.endDate=".$end_date;
              if ($id != -1)  {
                $sql.= " WHERE b.id=".$id; 
              } else {
                 $sql.= " WHERE b.event=".$current_event_id; 
              }
                     
         } else {
             //disactivate
             $end_date  = date('Y-m-d H:i:s');
             $sql = "UPDATE Nwp\AssessmentBundle\Entity\EventGradeLevelBlock b set b.isActive = 0, b.endDate='$end_date'"; 
             if ($id != -1)  {
                $sql.= " WHERE b.id=".$id; 
             } else {
                $sql.= " WHERE b.event=".$current_event_id; 
             }
             
         }
         
         try {
            $em = $this->getDoctrine()->getManager();
            $q = $em->createQuery($sql);
            $numUpdated = $q->execute();                  
                    
         } catch(\Doctrine\DBAL\DBALException $e) {
            $error = true;
         }
         
         if ($id==-1) {
             $block_msg1="Blocks";
             $block_msg2="Blocks have";
         } else {
             $block_msg1 = "Block";
             $block_msg2 = "Block has";
         }
         
         if ($error ==true) {
            $this->get('session')->getFlashBag()->add('error', $block_msg1.' could not be updated.');
         } else {
            $this->get('session')->getFlashBag()->add('info', $block_msg2.' been successfully updated.');
         }
         return $error;
     }
    
    /**
     * Finds and displays a EventGradeLevelBlockPrompt entity.
     *
     * @Route("/eventgradelevelblock/{id}/show", name="eventsite_block_show")
     
     * @Template()
     */
    public function showAction($id)
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
        
        if (!$this->checkAccess("list",null,"EventGradeLevelBlock")) {
            throw new AccessDeniedException();
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
        $prompts_new=array();
       
        $prompts = $em->getRepository('NwpAssessmentBundle:EventGradeLevelBlockPrompt')->findBy(array('eventGradeLevelBlock'=> $id), array('tableId' => 'ASC'));     
        
        foreach($prompts as $p) {
            $table_id=$p->getTableId();
            $prompts_new[$table_id][]=$p->getPrompt()->getName();
            
        }
        
        return array(
            'prompts'      => $prompts,
            'prompts_new'      => $prompts_new,
            'user_role_id' => $user_role_id,
            'role_admin_id' => $role_admin_id,
            'role_event_leader_id' => $role_event_leader_id,
            'role_room_leader_id' => $role_room_leader_id,
            'role_table_leader_id' => $role_table_leader_id,
            'role_scorer1_id' => $role_scorer1_id,
            'role_scorer2_id' => $role_scorer2_id,
        );
    }
    
}