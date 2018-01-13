<?php

namespace Nwp\AssessmentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Menu\ItemInterface as MenuItemInterface;
 
class EventGradeLevelBlockPromptAdmin extends Admin
{
 // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'ASC',
        '_sort_by' => 'name'
        
    );
    
 
    protected function configureFormFields(FormMapper $formMapper)
    {
        
       
           //in Edit mode, can edit Event Rooms
           $formMapper
           ->add('prompt', null, array('required' => true))
           ->add('tableId', null, array('required' => true))
           ;

    }
 
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
           ->add('eventGradeLevelBlock')
            ->add('prompt')
            ->add('tableId')
        ;
    }
 
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
           
            ->addIdentifier('eventGradeLevelBlock')
            ->add('prompt')
            ->add('tableId')
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
            ->add('eventGradeLevelBlock')
            ->add('prompt')
            ->add('tableId')
        ->end();
        ;
    }
    
    
}
