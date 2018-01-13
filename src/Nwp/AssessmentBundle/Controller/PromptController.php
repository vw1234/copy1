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

use Nwp\AssessmentBundle\Entity\Prompt;
use Nwp\AssessmentBundle\Form\PromptType;
use Nwp\AssessmentBundle\Form\PromptFilterType;

/**
 * Prompt controller.
 *
 * @Route("/projectsite/prompt")
 */
class PromptController extends BaseController
{
    /**
     * Lists all Prompt entities.
     *
     * @Route("/", name="projectsite_prompt")
     * @Template()
     */
    public function indexAction()
    {
        if (!$this->checkAccess("list",null,"Prompt")) {
            throw new AccessDeniedException();
        }
        
        $project_capability_array = $this->getUserProjectRoleCapabilities();
        
        if ($this->get('request')->get('btn_batch_action')) {
            return $this->render('NwpAssessmentBundle:Default:batch_confirmation.html.twig', array('entity_path' =>'projectsite_prompt', 'request_data' => $this->get('request')->request->all()));   
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
     * Batch Detete for Prompt entities.
     *
     * @Route("/batch/action/delete", name="projectsite_prompt_batch_action_delete")
     * @Method("post")
     * @Template("NwpAssessmentBundle:Default:batch_confirmation.html.twig")
     */
    
    public function batchActionDelete(){
        
        $items_array = $this->batchApplicationAction("Prompt");
        
        if ($items_array != null) {
            //first check access, optimize later so the query doesn't have to be selected again, project ids will be passed through original query
            $Ids = implode(",", $items_array['ids']);
            $data= $this->getApplicationValues("Nwp\AssessmentBundle\Entity\Prompt", "p", null, null, null, null,null, "p.id in (".$Ids.")",'p.id');
            
            //check that user has access to delete all prompts selected for batch delete(based on project access)
            $project_ids="";
            foreach ($data as $d) {
                $project_ids .= $d->getProject()->getId().",";
            }
            $project_ids = substr($project_ids, 0, -1); //strip last comma
            
            if (!$this->checkAccess("delete",$project_ids,"Prompt")) {
                throw new AccessDeniedException();
            }
            //end of access check
            
             $entities_array=array();
                      
            $entities_array[0]['classname']="Nwp\AssessmentBundle\Entity\Prompt";
            $entities_array[0]['alias']="p";
            $entities_array[0]['fieldname']="id";
            $entities_array[0]['ids']=$items_array['ids'];
                      
            $this->batchApplicationActionDelete($entities_array,'Prompts','Papers');
         }
         
        return $this->redirect($this->generateUrl('projectsite_prompt'));     
    }
    
    /**
     * Batch Export for Prompt entities.
     *
     * @Route("/batch/action/export", name="projectsite_prompt_batch_action_export")
     * @Method("post")
     * @Template("NwpAssessmentBundle:Default:batch_confirmation.html.twig")
     */
    public function batchActionExport(){
        
        $items_array = $this->batchApplicationAction("Prompt");
        
        if ($items_array != null) {
            if ($items_array['entities'] !="") { //we already have the data from filter query on list page
                $data=$items_array['entities'];
            } else {
                $Ids = implode(",", $items_array['ids']); //a subset of the filter query on list page was selected, so we need to requery
                $data= $this->getApplicationValues("Nwp\AssessmentBundle\Entity\Prompt", "p", null, null, null, null,null, "p.id in (".$Ids.")",'p.id');
            }
            //check that user has access to export for all papers selected for export (based on project access)
            $project_ids="";
            foreach ($data as $d) {
                $project_ids .= $d->getProject()->getId().",";
            }
            $project_ids = substr($project_ids, 0, -1); //strip last comma
            if (!$this->checkAccess("edit",$project_ids,"Prompt")) {
                throw new AccessDeniedException();
            }
            //end of access check 
            $fields=array('id','name','project');
            $export= $this->batchApplicationActionExport("Prompt",$fields,$data);
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
        
        $projects = $this->getUserProjects(null, "Prompt", "list");
        
        
        
        $Ids="";
        foreach($projects as $project) {
            $Ids .= $project->getId().",";
        }
        $Ids = substr($Ids, 0, -1); //strip last comma
        
     
        
        $filterForm = $this->createForm(new PromptFilterType($projects));
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('NwpAssessmentBundle:Prompt')->createQueryBuilder('e')->where('e.project IN ('.$Ids.')');
    
        // Reset filter
        if ($request->getMethod() == 'POST' && $request->get('filter_action') == 'reset') {
            $session->remove('PromptControllerFilter');
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
                $session->set('PromptControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('PromptControllerFilter')) {
                $filterData = $session->get('PromptControllerFilter');
                //this code fixes "Entities passed to the choice field must be managed" symfony error message  
                foreach ($filterData as $key => $filter) { 
                    if (is_object($filter)) {
                        $filterData[$key] = $em->merge($filter);
                    }
                }
                $filterForm = $this->createForm(new PromptFilterType($projects), $filterData);
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
            return $me->generateUrl('projectsite_prompt', array('page' => $page));
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
     * Finds and displays a Prompt entity.
     *
     * @Route("/{id}/show", name="projectsite_prompt_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NwpAssessmentBundle:Prompt')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Prompt entity.');
        }
        
        if (!$this->checkAccess("show",$entity->getProject()->getId(),"Prompt")) {
            throw new AccessDeniedException();
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new Prompt entity.
     *
     * @Route("/new", name="projectsite_prompt_new")
     * @Template()
     */
    public function newAction()
    {
        if (!$this->checkAccess("create",null,"Prompt")) {
            throw new AccessDeniedException();
        }
        $entity = new Prompt();
       
        $projects = $this->getUserProjects(null, "Prompt", "create");
        $form   = $this->createForm(new PromptType($projects), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a new Prompt entity.
     *
     * @Route("/create", name="projectsite_prompt_create")
     * @Method("post")
     * @Template("NwpAssessmentBundle:Prompt:new.html.twig")
     */
    public function createAction()
    {
        $entity  = new Prompt();
        $request = $this->getRequest();
         
        $projects = $this->getUserProjects(null, "Prompt", "create");
        $form    = $this->createForm(new PromptType($projects), $entity);
        
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.create.success');

            return $this->redirect($this->generateUrl('projectsite_prompt_edit', array('id' => $entity->getId())));        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.create.error');
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    /**
     * Displays a form to edit an existing Prompt entity.
     *
     * @Route("/{id}/edit", name="projectsite_prompt_edit")
     * @Template()
     */
    public function editAction($id)            
            
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NwpAssessmentBundle:Prompt')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Prompt entity.');
        }
       
        if (!$this->checkAccess("edit",$entity->getProject()->getId(),"Prompt")) {
            throw new AccessDeniedException();
        }

        $projects = $this->getUserProjects(null, "Prompt", "edit");
        
        $editForm = $this->createForm(new PromptType($projects), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Prompt entity.
     *
     * @Route("/{id}/update", name="projectsite_prompt_update")
     * @Method("post")
     * @Template("NwpAssessmentBundle:Prompt:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NwpAssessmentBundle:Prompt')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Prompt entity.');
        }

        $projects = $this->getUserProjects(null, "Prompt", "edit");
         
        $editForm   = $this->createForm(new PromptType($projects), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.update.success');

            return $this->redirect($this->generateUrl('projectsite_prompt_edit', array('id' => $id)));
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
     * Deletes a Prompt entity.
     *
     * @Route("/{id}/delete", name="projectsite_prompt_delete")
     */
    public function deleteAction($id)
    {
        if ($this->get('request')->getMethod()=="POST") {
            $form = $this->createDeleteForm($id);
            $request = $this->getRequest();
            $form->bind($request);
        } else { //get method
            $id = $this->get('request')->get('id');
        }

        
         if ((($this->get('request')->getMethod()=="POST") && ($form->isValid())) || (($this->get('request')->getMethod()=="GET") && ($id !=''))) {
            
             try {
                $em = $this->getDoctrine()->getManager();
                 
                $entity = $em->getRepository('NwpAssessmentBundle:Prompt')->find($id);
               
                if (!$entity) {
                    throw $this->createNotFoundException('Unable to find Prompt entity.');
                }
                
               if (!$this->checkAccess("delete",$entity->getProject()->getId(),"Prompt")) {
                throw new AccessDeniedException();
            }
                
                $em->remove($entity);
                 
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'flash.delete.success');
               
            } catch(\Doctrine\DBAL\DBALException $e) {
                    $this->get('session')->getFlashBag()->add('error', 'The Prompt could not be deleted due to dependencies.  To delete this Prompt, you must first delete the Papers that are using this Prompt.');
            }
         
           
        } else {
           
            $this->get('session')->getFlashBag()->add('error', 'flash.delete.error');
        }

        return $this->redirect($this->generateUrl('projectsite_prompt'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
