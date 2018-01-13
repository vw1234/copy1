<?php

namespace Nwp\AssessmentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// Import new namespaces
use Nwp\AssessmentBundle\Entity\Enquiry;
use Nwp\AssessmentBundle\Form\EnquiryType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DefaultController extends BaseController
{
    public function topnavAction() {
        $current_url = $_SERVER['REQUEST_URI'];
        $project_capability_array="";
        $event_capability_array="";
      
        if ($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) { 
            $project_capability_array = $this->getUserProjectRoleCapabilities();
            $event_capability_array = $this->getUserEventRoleCapabilities();
        }
        return $this->render(
            'NwpAssessmentBundle:Default:topnav.html.twig',
            array('projects' => $project_capability_array,'events' => $event_capability_array, 'current_url' => $current_url)
        );
    }
    
    public function displayRolesAction() {
        $site="";
        $current_event_entity="";
        
        $request = $this->getRequest();
        $session = $request->getSession();
        $current_url = $_SERVER['REQUEST_URI'];
        $project_capability_array = $this->getUserProjectRoleCapabilities();
        $event_capability_array = $this->getUserEventRoleCapabilities();
        
        //get System Roles
        $system_roles_array = $this->getSystemRoles();
        $role_event_leader_id=$system_roles_array['Event Leader'];
        $role_room_leader_id=$system_roles_array['Room Leader'];
        $role_table_leader_id=$system_roles_array['Table Leader'];
        $role_scorer1_id=$system_roles_array['Scorer 1'];
        $role_scorer2_id = $system_roles_array['Scorer 2']; 
        $role_admin_id = $system_roles_array['Admin']; 
        
      if (strpos($current_url, "/projectsite")!== false)  {  //The url of the page designates that it's the Project Site
           $site="projectsite";
       } else if (strpos($current_url, "/eventsite")!== false) {  //The url of the page designates that it's the Event Site
           $site="eventsite";
           $current_event = $this->getCurrentEvent();
           if ($session->has("CurrentEventUserSession") && ($session->get("CurrentEventUserSession") !="")) {
                $event_id =  $session->get("CurrentEventUserSession");
                $em = $this->getDoctrine()->getManager();
                
                $current_event_entity = $em->getRepository('NwpAssessmentBundle:Event')->find($event_id);
               
                
           }
       }
        return $this->render(
            'NwpAssessmentBundle:Default:roles.html.twig',
            array('projects' => $project_capability_array,
                'events' => $event_capability_array, 
                'current_url' => $current_url, 
                'site' => $site, 
                'current_event_entity' =>$current_event_entity,
                'role_admin_id' => $role_admin_id,
                'role_event_leader_id' => $role_event_leader_id,
                'role_room_leader_id' => $role_room_leader_id,
                'role_table_leader_id' => $role_table_leader_id,
                'role_scorer1_id' => $role_scorer1_id,
                'role_scorer2_id' => $role_scorer2_id   
                )
        );
    }
   
    public function navAction() {
       $site="";
       
       $request = $this->getRequest();
       $session = $request->getSession();
       $project_capability_array = $this->getUserProjectRoleCapabilities();
       $event_capability_array = $this->getUserEventRoleCapabilities();
       
       $nav_level_1 = array();  //build level 1 nav
       $nav_level_2 = array();  //build level 2 nav
       $nav_level_1_new = array(); //rebuild level 1 nav based on number of items
               
       $current_url = $_SERVER['REQUEST_URI'];
       
       if (strpos($current_url, "/projectsite")!== false)  {  //The url of the page designates that it's the Project Site
           $site="projectsite";
       } else if (strpos($current_url, "/eventsite")!== false) {  //The url of the page designates that it's the Event Site
           $site="eventsite";
           $current_event = $this->getCurrentEvent();
           if ($session->has("CurrentEventUserSession") && ($session->get("CurrentEventUserSession") !="")) {
                $event_id =  $session->get("CurrentEventUserSession");
                //echo "current event id is ".$event_id;
                $access=false;
               // var_dump($event_capability_array[$event_id]);
                
                foreach ($event_capability_array[$event_id] as $e) { //check whether user has access to current event and event roles
                       if ($e['structure_id']== 2) {
                           $access=true;
                           break;
                       }
                 
                }
                if ($access==true) {//display the event nav if user has at least one event role for this event
                   $nav_array = $this->getApplicationValues("NwpAssessmentBundle:Nav", "n", "n.id,n.name,n.path,n.levelId,n.parentId,n.orderId,IDENTITY(n.entity) object_id,IDENTITY(n.action) action_id",null, "NavEventArrayUserSession",null,null,'n.structure=2 and n.isActive=1','n.orderId');
                    $nav_array_size = sizeof($nav_array); 
                    
                    for($n=0;$n<$nav_array_size;$n++){
                        if ($nav_array[$n]["levelId"]==1) {
                            $nav_level_1[]=$nav_array[$n]; 
                        } else if (($nav_array[$n]["levelId"]==2)) {
                                foreach ($event_capability_array[$event_id] as $e) { //check whether user has access
                                   $access=true;
                                   if ((isset($nav_array[$n]["action_id"])) && (isset($nav_array[$n]["object_id"]))){ 
                                        $access=false;
                                        if (($e['action_id']==$nav_array[$n]["action_id"]) && ($e['object_id']==$nav_array[$n]["object_id"])) {
                                            $access=true;
                                            break; 
                                        }
                                
                                    if ($access==true) {//has access at least to one project, no need to loop through others
                                        break;
                                    }
                                  }
                                } 
                            if ($access==true) {
                                $nav_level_2[]=$nav_array[$n];
                            }
                        }
                    }
                    //var_dump($nav_level_2);
                    //die();
                }
           } 
       } 
       
       if (($site=="projectsite") && ($project_capability_array)) {
            $nav_array = $this->getApplicationValues("NwpAssessmentBundle:Nav", "n", "n.id,n.name,n.path,n.levelId,n.parentId,n.orderId,IDENTITY(n.entity) object_id,IDENTITY(n.action) action_id",null, "NavProjectArrayUserSession",null,null,'n.structure=1 and n.isActive=1','n.orderId');
           
            $nav_array_size = sizeof($nav_array);

            for($n=0;$n<$nav_array_size;$n++){
                if ($nav_array[$n]["levelId"]==1) {
                    $nav_level_1[]=$nav_array[$n]; 
                } else if (($nav_array[$n]["levelId"]==2)) {
                    $access=false;
                    foreach ($project_capability_array as $p) { //check whether user has access
                        foreach ($p as $i) {
                            if (($i['action_id']==$nav_array[$n]["action_id"]) && ($i['object_id']==$nav_array[$n]["object_id"])) {
                                $access=true;
                                break; 
                            }
                        }
                        if ($access==true) {//has access at least to one project, no need to loop through others
                            break;
                        }
                    } 
                    if ($access==true) {
                        $nav_level_2[]=$nav_array[$n];
                    }
                }
            }
            
            
       }
       
       //If there are no items under parent nav, do not display the parent nav
       foreach ($nav_level_1 as $n1) {
           foreach ($nav_level_2 as $n2) {
                if  ($n2['parentId']== $n1['id']) {
                    //at least one nav has this parent id, so keep the parent nav
                    $nav_level_1_new[]=$n1;
                    break;
                }
           }
       }
       
       //var_dump($nav_level_1_new);
       //var_dump($nav_level_2);
       
        return $this->render(
            'NwpAssessmentBundle:Default:nav.html.twig',
            array('nav_level_1' => $nav_level_1_new,'nav_level_2' => $nav_level_2, 'current_url' => $current_url,'site' => $site)
        );
       
    }
    
     /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        if ($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) { 
            $project_capability_array = $this->getUserProjectRoleCapabilities();
            $event_capability_array = $this->getUserEventRoleCapabilities();
        }
        if ((sizeof($event_capability_array)>0) && ((sizeof($project_capability_array)==0))) {
             return $this->redirect($this->generateUrl('eventsite_event'));   
        } else {
            return $this->render('NwpAssessmentBundle:Default:index.html.twig');
        }
    }
    
    public function aboutAction()
    {
        return $this->render('NwpAssessmentBundle:Default:about.html.twig');
    }
    
    public function contactAction()
    {
        $enquiry = new Enquiry();
        $form = $this->createForm(new EnquiryType(), $enquiry);

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            
            if ($form->isValid()) {
                
                
                // Perform some action, such as sending an email
                $message = \Swift_Message::newInstance()
                    ->setSubject('Contact enquiry from Assessment System')
                    ->setFrom($this->container->getParameter('nwp_assessment.contact_email'))
                    ->setTo($this->container->getParameter('nwp_assessment.contact_email'))
                    ->setBody($this->renderView('NwpAssessmentBundle:Default:contactEmail.txt.twig', array('enquiry' => $enquiry)));
                $this->get('mailer')->send($message);

                 $this->addFlash('blogger-notice', 'Your contact enquiry was successfully sent. Thank you!');


                // Redirect - This is important to prevent users re-posting
                // the form if they refresh the page
                return $this->redirect($this->generateUrl('NwpAssessmentBundle_pages_contact'));
            }
        }

        return $this->render('NwpAssessmentBundle:Default:contact.html.twig', array(
            'form' => $form->createView()
        ));
    }   
}
