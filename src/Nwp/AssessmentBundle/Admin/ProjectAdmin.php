<?php

namespace Nwp\AssessmentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Menu\ItemInterface as MenuItemInterface;
 
class ProjectAdmin extends Admin
{
 // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'ASC',
        '_sort_by' => 'name'
        
    );
    
    public function getFormTheme()
    {
        return array_merge(
        parent::getFormTheme(),
        array('NwpAssessmentBundle:ProjectUser:admin.project.html.twig')
        );
    }
 
    protected function configureFormFields(FormMapper $formMapper)
    {
        $subject = $this->getSubject();
        $id = $subject->getId();
      
       if ($id) {
           //in Edit mode, Project Roles can be added
           $formMapper
           ->with('General')
           ->add('name', null, array('required' => true))
           ->add('startDate', 'date', array('required' => false))
           ->add('endDate', 'date', array('required' => false))
            ->end()           
            ->with('Project Roles')
            ->add('pu','sonata_type_collection', array('by_reference' => 
                    false,'label' =>'Project User Roles','required' =>false), array('edit' => 'inline','inline' => 
                    'table','targetEntity'=>'Nwp\AssessmentBundle\Entity\ProjectUser'))
            ->end();
       } else {
           // in Create mode, Project Roles cannot be added
           $formMapper
           ->with('General')
           ->add('name', null, array('required' => true))
           ->add('startDate', 'date', array('required' => false))
           ->add('endDate', 'date', array('required' => false))
           ;
       }
        
       
         #$formMapper->add('project', 'sonata_type_model', array('type' => new ProjectUserAdmin()));
        #->with('Project Users')
       #    ->add('user', 'sonata_type_model', array(
       #     'required' => false,
        #    'label'    => $this->trans('Username'),
        #    'expanded' => true,
        #     'multiple' => true,
             
            #))
            ;
        ;
    }
 
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('startDate')
            ->add('endDate')
        ;
    }
 
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
         ->addIdentifier('name')
            ->add('startDate')
            ->add('endDate')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'view' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }
    
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
        ->with('General')
            ->add('name')
            ->add('startDate')
            ->add('endDate')
        ->end() 
        ->with('Project Roles')
                ->add('pu')
               #  ->add('projectuser','string', array('template' => 'NwpAssessmentBundle:ProjectUserAdmin:list.html.twig'))
       ->end()    
        ;
    }
    
      
  public function preUpdate($object) {
      $project = $object->getProject();
      
      foreach($object->getPu() as $child) {
        //set default values that the user does not choose on the Project User form
        $child->setProject($project);
      }
      
    
    }
}
