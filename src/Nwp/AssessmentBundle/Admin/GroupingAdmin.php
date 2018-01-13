<?php

namespace Nwp\AssessmentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Menu\ItemInterface as MenuItemInterface;

use Nwp\AssessmentBundle\Entity\GroupingType;
 
class GroupingAdmin extends Admin
{
 // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'ASC',
        '_sort_by' => 'name'
        
    );
    
    
    protected function configureFormFields(FormMapper $formMapper)
    {
        
        $formMapper
           ->add('name', null, array('required' => true))      
            ->add('groupingType', 'entity', array('class' => 'NwpAssessmentBundle:GroupingType'))    
          ;
  
           
         
    }
 
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('groupingType')
           
        ;
    }
 
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
           
            ->addIdentifier('name')
            ->add('groupingType')
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
            ->add('name')
            ->add('groupingType')
        ;
    }
    
      
  public function preUpdate($object) {
      
      
    
    }
}
