<?php

namespace Nwp\AssessmentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrapView;

use Nwp\AssessmentBundle\Entity\ProjectScoringItem;
use Nwp\AssessmentBundle\Form\ProjectScoringItemType;
use Nwp\AssessmentBundle\Form\ProjectScoringItemFilterType;

/**
 * ProjectScoringItem controller.
 *
 * @Route("/projectsite/projectscoringitem")
 */
class ProjectScoringItemController extends Controller
{
    /**
     * Lists all ProjectScoringItem entities.
     *
     * @Route("/", name="projectsite_projectscoringitem")
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
        $filterForm = $this->createForm(new ProjectScoringItemFilterType());
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('NwpAssessmentBundle:ProjectScoringItem')->createQueryBuilder('e');
    
        // Reset filter
        if ($request->getMethod() == 'POST' && $request->get('filter_action') == 'reset') {
            $session->remove('ProjectScoringItemControllerFilter');
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
                $session->set('ProjectScoringItemControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('ProjectScoringItemControllerFilter')) {
                $filterData = $session->get('ProjectScoringItemControllerFilter');
                $filterForm = $this->createForm(new ProjectScoringItemFilterType(), $filterData);
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
            return $me->generateUrl('projectsite_projectscoringitem', array('page' => $page));
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
     * Finds and displays a ProjectScoringItem entity.
     *
     * @Route("/{id}/show", name="projectsite_projectscoringitem_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NwpAssessmentBundle:ProjectScoringItem')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProjectScoringItem entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new ProjectScoringItem entity.
     *
     * @Route("/new", name="projectsite_projectscoringitem_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new ProjectScoringItem();
        $form   = $this->createForm(new ProjectScoringItemType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a new ProjectScoringItem entity.
     *
     * @Route("/create", name="projectsite_projectscoringitem_create")
     * @Method("post")
     * @Template("NwpAssessmentBundle:ProjectScoringItem:new.html.twig")
     */
    public function createAction()
    {
        $entity  = new ProjectScoringItem();
        $request = $this->getRequest();
        $form    = $this->createForm(new ProjectScoringItemType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.create.success');

            return $this->redirect($this->generateUrl('projectsite_projectscoringitem_show', array('id' => $entity->getId())));        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.create.error');
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    /**
     * Displays a form to edit an existing ProjectScoringItem entity.
     *
     * @Route("/{id}/edit", name="projectsite_projectscoringitem_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NwpAssessmentBundle:ProjectScoringItem')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProjectScoringItem entity.');
        }

        $editForm = $this->createForm(new ProjectScoringItemType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing ProjectScoringItem entity.
     *
     * @Route("/{id}/update", name="projectsite_projectscoringitem_update")
     * @Method("post")
     * @Template("NwpAssessmentBundle:ProjectScoringItem:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NwpAssessmentBundle:ProjectScoringItem')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProjectScoringItem entity.');
        }

        $editForm   = $this->createForm(new ProjectScoringItemType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.update.success');

            return $this->redirect($this->generateUrl('projectsite_projectscoringitem_edit', array('id' => $id)));
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
     * Deletes a ProjectScoringItem entity.
     *
     * @Route("/{id}/delete", name="projectsite_projectscoringitem_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NwpAssessmentBundle:ProjectScoringItem')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ProjectScoringItem entity.');
            }

            $em->remove($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.delete.success');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.delete.error');
        }

        return $this->redirect($this->generateUrl('projectsite_projectscoringitem'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
