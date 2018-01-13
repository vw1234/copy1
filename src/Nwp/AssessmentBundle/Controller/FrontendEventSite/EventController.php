<?php

namespace Nwp\AssessmentBundle\Controller\FrontendEventSite;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrapView;

use Nwp\AssessmentBundle\Entity\Event;
use Nwp\AssessmentBundle\Form\EventType;
use Nwp\AssessmentBundle\Form\EventFilterType;

use Nwp\AssessmentBundle\Controller\BaseController;

use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\Query\Expr\Orx;
/**
 * Event controller.
 *
 * @Route("/eventsite/event")
 */
class EventController extends BaseController
{
    
    /**
     * Lists all Event entities.
     *
     * @Route("/", name="eventsite_event")
     * @Template()
     */
    public function indexAction()
    { 
        if (!$this->checkAccess("list",null,"Event")) {
            throw new AccessDeniedException();
        }
        
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        
        $current_event = $this->getCurrentEvent();
        $event_capability_array = $this->getUserEventRoleCapabilities();
        
        if ($session->has("CurrentEventUserSession") && ($session->get("CurrentEventUserSession") !="")) {//redirect to show page
            $entities = $em->getRepository('NwpAssessmentBundle:Event')->find($session->get("CurrentEventUserSession"));
            return $this->redirect($this->generateUrl('eventsite_event_show', array('id' =>$session->get("CurrentEventUserSession"))));
        } else { //have them choose the event out of several current or upcoming events they have access to
            
           $events_with_access_array=$session->get("EventsWithAccessUserSession");
           $events_with_access="";
           foreach($events_with_access_array as $ea) {
                $events_with_access .= $ea.",";
           }
           $events_with_access = substr($events_with_access, 0, -1); //strip last comma
        
           $qb =$em->getRepository('NwpAssessmentBundle:Event')
                          ->createQueryBuilder('e')
                          ->select('e')
                          ->where('e.id IN ('.$events_with_access.")")
                          ->orderBy('e.startDate', 'DESC')
                            ;
            $query = $qb->getQuery();
            $entities= $query->getResult();
            
            return array(
                'entities' => $entities,
                'event_capability_array' => $event_capability_array,
            );
        }
   
    }

    /**
    * Create filter form and process filter request.
    *
    */
    protected function filter()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        $filterForm = $this->createForm(new EventFilterType());
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('NwpAssessmentBundle:Event')->createQueryBuilder('e');
    
        // Reset filter
        if ($request->getMethod() == 'POST' && $request->get('filter_action') == 'reset') {
            $session->remove('EventControllerFilter');
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
                $session->set('EventControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('EventControllerFilter')) {
                $filterData = $session->get('EventControllerFilter');
                $filterForm = $this->createForm(new EventFilterType(), $filterData);
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
            }
        }
    
        return array($filterForm, $queryBuilder);
    }

    /**
    * Get results from paginator and get paginator view.
    *
    */
    protected function paginator($queryBuilder)
    {
        // Paginator
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $currentPage = $this->getRequest()->get('page', 1);
        $pagerfanta->setCurrentPage($currentPage);
        $entities = $pagerfanta->getCurrentPageResults();
    
        // Paginator - route generator
        $me = $this;
        $routeGenerator = function($page) use ($me)
        {
            return $me->generateUrl('event', array('page' => $page));
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
     * Finds and displays a Event entity.
     *
     * @Route("/{id}/show", name="eventsite_event_show")
     * @Template()
     */
    public function showAction($id)
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NwpAssessmentBundle:Event')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Event entity.');
        }

        if (!$this->checkAccess("show", $id, "Event")) {
            throw new AccessDeniedException();
        }
        $score =  $request->get("score");
        if ($score==1) {  //score button was clicked, this is the event that will be set as current event
            if (!$this->checkAccessTimeframe("eventsite", null, null, "show", null,"Event", $id,null,null)) {
                throw new AccessDeniedException();
            } else {
                 $session->set('CurrentEventUserSession',$id );
            }
        }
        return array(
            'entity'      => $entity,
           
        );
    }

   
}
