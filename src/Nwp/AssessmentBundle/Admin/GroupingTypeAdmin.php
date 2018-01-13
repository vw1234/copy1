<?php

namespace Nwp\AssessmentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Menu\ItemInterface as MenuItemInterface;
 
class GroupingTypeAdmin extends Admin
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
            ;       

    }
 
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
           
        ;
    }
 
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
           
            ->addIdentifier('name')
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
        ->end();
          
          
        ;
    }
    
    
}
