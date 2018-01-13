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


use Nwp\AssessmentBundle\Form\EventGradeLevelBlockPromptFilterType;



/**
 * EventGradeLevelBlock controller.
 *
 * @Route("/eventsite")
 */
class EventGradeLevelBlockPromptController extends BaseController
{
    /**
     * Lists all EventScoringItemStatus entities.
     *
    
     * @Route("/eventprompt", name="eventsite_prompt")
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
        
        if (!$this->checkAccess("list",null,"EventGradeLevelBlockPrompt")) {
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
        
        $filter_name = "EventGradeLevelBlockPromptControllerFilter"; //Assume this is the Queue, not Recent Papers reporting page    

      
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
         
        $filterForm = $this->createForm(new EventGradeLevelBlockPromptFilterType($current_event_id,$user_role_id,$user_grade_level_id,$user_table_id,$role_scorer1_id,$role_scorer2_id,$role_table_leader_id,$role_room_leader_id,$role_event_leader_id,$role_admin_id));
       
        
        $queryBuilder = $em->getRepository('NwpAssessmentBundle:EventGradeLevelBlockPrompt')->createQueryBuilder('e')
                            ->leftJoin('e.eventGradeLevelBlock', 'b')
                            ->Where('b.event='.$current_event_id);
                            if (($user_role_id!=$role_event_leader_id) && ($user_role_id!=$role_admin_id)) {
                                $queryBuilder->andWhere('b.gradeLevel='.$user_grade_level_id);
                            }
                            if (($user_role_id!=$role_event_leader_id) && ($user_role_id!=$role_admin_id) && ($user_role_id!=$role_room_leader_id)) {
                                $queryBuilder->andWhere('e.tableId='.$user_table_id);
                            }
                            $queryBuilder->orderBy('b.gradeLevel,b.blockId,e.tableId', 'ASC');
                
                        
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
                $filterForm = $this->createForm(new EventGradeLevelBlockPromptFilterType($current_event_id,$user_role_id,$user_grade_level_id,$user_table_id,$role_scorer1_id,$role_scorer2_id,$role_table_leader_id,$role_room_leader_id,$role_event_leader_id,$role_admin_id), $filterData);
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
    
       
     
    
}