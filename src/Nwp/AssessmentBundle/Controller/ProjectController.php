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

use Nwp\AssessmentBundle\Entity\Project;
use Nwp\AssessmentBundle\Form\ProjectType;
use Nwp\AssessmentBundle\Form\ProjectFilterType;

/**
 * Project controller.
 *
 * @Route("/projectsite/project")
 */
class ProjectController extends BaseController
{
    /**
     * Lists all Project entities.
     *
     * @Route("/", name="projectsite_project")
     * @Template()
     */
    public function indexAction()
    {
        if (!$this->checkAccess("list",null,"Project")) {
            throw new AccessDeniedException();
        }
        $project_capability_array = $this->getUserProjectRoleCapabilities();
        
        //var_dump($project_capability_array);
        
        if ($this->get('request')->get('btn_batch_action')) {
            return $this->render('NwpAssessmentBundle:Default:batch_confirmation.html.twig', array('entity_path' =>'projectsite_project', 'request_data' => $this->get('request')->request->all()));   
        }
        
        list($filterForm, $queryBuilder) = $this->filter();

        list($entities, $pagerHtml) = $this->paginator($queryBuilder);

    
        return array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'project_capability_array' => $project_capability_array,
        );
    }
    
    /**
     * Batch Export for ScoringItem entities.
     *
     * @Route("/batch/action/export", name="projectsite_project_batch_action_export")
     * @Method("post")
     * @Template("NwpAssessmentBundle:Default:batch_confirmation.html.twig")
     */
    public function batchActionExport(){
        
        $items_array = $this->batchApplicationAction();
        
        if ($items_array != null) {
            if ($items_array['entities'] !="") { //we already have the data from filter query on list page
                $Ids="";
                $data=$items_array['entities'];
            } else {
                $Ids = implode(",", $items_array['ids']); //a subset of the filter query on list page was selected, so we need to requery
                $data= $this->getApplicationValues("Nwp\AssessmentBundle\Entity\Project", "p", null, null, null, null,null, "p.id in (".$Ids.")",'p.id');
            }
            //check that user has access to export for all papers selected for export (based on project access)
            if ($Ids=="") {
                foreach ($data as $d) {
                    $Ids .= $d->getId().",";
                }
                $Ids = substr($Ids, 0, -1); //strip last comma
            }
            if (!$this->checkAccess("edit",$Ids,"Project")) {
                throw new AccessDeniedException();
            }
            //end of access check 
            $fields=array('id','name','startDate','endDate');
            $export= $this->batchApplicationActionExport("Project",$fields,$data);
         }
         
        return $export;     
    }

    /**
    * Create filter form and process filter request.
    *
    */
    protected function filter()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        
        $projects = $this->getUserProjects(null, "Project", "list");
        
        $Ids="";
        foreach($projects as $project) {
            $Ids .= $project->getId().",";
        }
        $Ids = substr($Ids, 0, -1); //strip last comma
        
        $filterForm = $this->createForm(new ProjectFilterType());
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('NwpAssessmentBundle:Project')->createQueryBuilder('e')->where('e.id IN ('.$Ids.')');
        
        
    
        // Reset filter
        if ($request->getMethod() == 'POST' && $request->get('filter_action') == 'reset') {
            $session->remove('ProjectControllerFilter');
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
                $session->set('ProjectControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('ProjectControllerFilter')) {
                $filterData = $session->get('ProjectControllerFilter');
                //this code fixes "Entities passed to the choice field must be managed" symfony error message  
                foreach ($filterData as $key => $filter) { 
                    if (is_object($filter)) {
                        $filterData[$key] = $em->merge($filter);
                    }
                }
                $filterForm = $this->createForm(new ProjectFilterType(), $filterData);
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
            }
        }
        
        if (isset($filterData)) {
             $this->get('session')->getFlashBag()->add('info', 'flash.filter.success');
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
            return $me->generateUrl('projectsite_project', array('page' => $page));
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
     * Finds and displays a Project entity.
     *
     * @Route("/{id}/show", name="projectsite_project_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NwpAssessmentBundle:Project')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        if (!$this->checkAccess("show", $id, "Project")) {
            throw new AccessDeniedException();
        }

        //$deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            //'delete_form' => $deleteForm->createView(),
        );
    }

    
    /**
     * Displays a form to edit an existing Project entity.
     *
     * @Route("/{id}/edit", name="projectsite_project_edit")
     * @Template()
     */
    public function editAction($id)
    {
   
        $em = $this->getDoctrine()->getManager();
    
        $entity = $em->getRepository('NwpAssessmentBundle:Project')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        if (!$this->checkAccess("edit", $id, "Project")) {
            throw new AccessDeniedException();
        }

        $editForm = $this->createForm(new ProjectType(), $entity);
        //$deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Project entity.
     *
     * @Route("/{id}/update", name="projectsite_project_update")
     * @Method("post")
     * @Template("NwpAssessmentBundle:Project:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NwpAssessmentBundle:Project')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }

        $editForm   = $this->createForm(new ProjectType(), $entity);
      //  $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.update.success');

            return $this->redirect($this->generateUrl('projectsite_project_edit', array('id' => $id)));
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.update.error');
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
           // 'delete_form' => $deleteForm->createView(),
        );
    }
   
}
