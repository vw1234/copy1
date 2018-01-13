<?php

namespace Nwp\AssessmentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrapView;

use Nwp\AssessmentBundle\Entity\ScoringItemScore;
use Nwp\AssessmentBundle\Form\ScoringItemScoreType;
use Nwp\AssessmentBundle\Form\ScoringItemScoreFilterType;

/**
 * ScoringItemScore controller.
 *
 * @Route("/eventsite/scoringitemscore")
 */
class ScoringItemScoreController extends BaseController
{
    /**
     * Lists all ScoringItemScore entities.
     *
     * @Route("/", name="eventsite_scoringitemscore")
     * @Template()
     */
    public function indexAction()
    {
        list($filterForm, $queryBuilder) = $this->filter();

        list($entities, $pagerHtml) = $this->paginator($queryBuilder);

    
        return array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
        );
    }

    /**
    * Create filter form and process filter request.
    *
    */
    protected function filter()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        $filterForm = $this->createForm(new ScoringItemScoreFilterType());
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('NwpAssessmentBundle:ScoringItemScore')->createQueryBuilder('e');
    
        // Reset filter
        if ($request->getMethod() == 'POST' && $request->get('filter_action') == 'reset') {
            $session->remove('ScoringItemScoreControllerFilter');
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
                $session->set('ScoringItemScoreControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('ScoringItemScoreControllerFilter')) {
                $filterData = $session->get('ScoringItemScoreControllerFilter');
                $filterForm = $this->createForm(new ScoringItemScoreFilterType(), $filterData);
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
            return $me->generateUrl('eventsite_scoringitemscore', array('page' => $page));
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
     * Finds and displays a ScoringItemScore entity.
     *
     * @Route("/{id}/show", name="eventsite_scoringitemscore_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NwpAssessmentBundle:ScoringItemScore')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ScoringItemScore entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new ScoringItemScore entity.
     *
     * @Route("/new", name="eventsite_scoringitemscore_new")
     * @Template()
     */
    public function newAction()
    {
        $current_event_id = $this->getCurrentEvent();
       
        if ($current_event_id =="" || !(is_numeric($current_event_id))) {
              return $current_event_id; //redirect to index page
        }
        
         $em = $this->getDoctrine()->getManager();
         
        $request = $this->getRequest();
        if ($request->query->has('event_scoring_item_id') ) {
            $event_scoring_item_id = $request->query->get('event_scoring_item_id');
            $status_history = $em->getRepository('NwpAssessmentBundle:EventScoringItemUser')->findBy(array('eventScoringItem'=> $event_scoring_item_id), array('timeAssigned' => 'ASC'));
            //echo "paper id is ".$entity->getEventScoringItem()->getScoringItem()."<br>";
            //echo "current status is ".$entity->getStatus();
            
        }
        
       
        $current_event = $em->getRepository('NwpAssessmentBundle:Event')->find($current_event_id);
        
        //get the scoring rubric used for this event
        $scoring_rubric_id=$current_event->getScoringRubric()->getId();
        $scoring_rubric = $em->getRepository('NwpAssessmentBundle:ScoringRubric')->find($scoring_rubric_id);
        
        //get scoring scale the rubric uses
        $min_score = $scoring_rubric->getMinScore();
        $max_score = $scoring_rubric->getMaxScore();
        $scoring_scale=array();
        for($c= $min_score;$c<=$max_score;$c++){
            $scoring_scale[]=$c;
        }
         
        $entity = new ScoringItemScore();
        $form   = $this->createForm(new ScoringItemScoreType($scoring_scale), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a new ScoringItemScore entity.
     *
     * @Route("/create", name="eventsite_scoringitemscore_create")
     * @Method("post")
     * @Template("NwpAssessmentBundle:ScoringItemScore:new.html.twig")
     */
    public function createAction()
    {
        $current_event_id = $this->getCurrentEvent();
       
        if ($current_event_id =="" || !(is_numeric($current_event_id))) {
              return $current_event_id; //redirect to index page
        }
        
         $em = $this->getDoctrine()->getManager();
         
        $request = $this->getRequest();
        if ($request->query->has('event_scoring_item_id') ) {
            $event_scoring_item_id = $request->query->get('event_scoring_item_id');
            $status_history = $em->getRepository('NwpAssessmentBundle:EventScoringItemUser')->findBy(array('eventScoringItem'=> $event_scoring_item_id), array('timeAssigned' => 'ASC'));
            //echo "paper id is ".$entity->getEventScoringItem()->getScoringItem()."<br>";
            //echo "current status is ".$entity->getStatus();
            
        }
        
       
        $current_event = $em->getRepository('NwpAssessmentBundle:Event')->find($current_event_id);
        
        //get the scoring rubric used for this event
        $scoring_rubric_id=$current_event->getScoringRubric()->getId();
        $scoring_rubric = $em->getRepository('NwpAssessmentBundle:ScoringRubric')->find($scoring_rubric_id);
        
        //get scoring scale the rubric uses
        $min_score = $scoring_rubric->getMinScore();
        $max_score = $scoring_rubric->getMaxScore();
        $scoring_scale=array();
        for($c= $min_score;$c<=$max_score;$c++){
            $scoring_scale[]=$c;
        }
        $entity  = new ScoringItemScore();
        $request = $this->getRequest();
        $form    = $this->createForm(new ScoringItemScoreType($scoring_scale), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.create.success');

            return $this->redirect($this->generateUrl('eventsite_scoringitemscore_show', array('id' => $entity->getId())));        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.create.error');
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    /**
     * Displays a form to edit an existing ScoringItemScore entity.
     *
     * @Route("/{id}/edit", name="eventsite_scoringitemscore_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NwpAssessmentBundle:ScoringItemScore')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ScoringItemScore entity.');
        }

        $editForm = $this->createForm(new ScoringItemScoreType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing ScoringItemScore entity.
     *
     * @Route("/{id}/update", name="eventsite_scoringitemscore_update")
     * @Method("post")
     * @Template("NwpAssessmentBundle:ScoringItemScore:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NwpAssessmentBundle:ScoringItemScore')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ScoringItemScore entity.');
        }

        $editForm   = $this->createForm(new ScoringItemScoreType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.update.success');

            return $this->redirect($this->generateUrl('eventsite_scoringitemscore_edit', array('id' => $id)));
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.update.error');
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a ScoringItemScore entity.
     *
     * @Route("/{id}/delete", name="eventsite_scoringitemscore_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NwpAssessmentBundle:ScoringItemScore')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ScoringItemScore entity.');
            }

            $em->remove($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.delete.success');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.delete.error');
        }

        return $this->redirect($this->generateUrl('eventsite_scoringitemscore'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
