<?php

namespace Nwp\AssessmentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Menu\ItemInterface as MenuItemInterface;
 
class EventGradeLevelBlockAdmin extends Admin
{
 // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'ASC',
        '_sort_by' => 'name'
        
    );
    
 
    protected function configureFormFields(FormMapper $formMapper)
    {
        
       $subject = $this->getSubject();
       $id = $subject->getId();
       
       if ($id) {
           //in Edit mode, can edit Event Rooms
           $formMapper
           ->add('event', null, array('required' => true))
           ->add('gradeLevel', null, array('required' => true))
           ->add('blockId', null, array('label' =>'Block','required' => true))
           ->add('target', null, array('label' =>'Target'))
           ->end()
           ->with('Prompts/Tables')
           ->add('pu','sonata_type_collection', array('by_reference' => 
                    false,'label' =>'Prompts/Tables','required' =>true), array('edit' => 'inline','inline' => 
                    'table','targetEntity'=>'Nwp\AssessmentBundle\Entity\EventGradeLevelBlockPrompt'))
           ->end();
            ;   
       } else {
           $formMapper
           ->add('event', null, array('required' => true))
           ->add('gradeLevel', null, array('required' => true))
           ->add('blockId', null, array('label' =>'Block','required' => true))
           ->add('target', null, array('label' =>'Target'))
            ;   
       }

    }
 
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('event')
            ->add('gradeLevel')
            ->add('blockId')
            ->add('target')
        ;
    }
 
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
           
            ->addIdentifier('event')
            ->addIdentifier('gradeLevel')
            ->addIdentifier('blockId')
            ->addIdentifier('target')
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
            ->add('event')
            ->add('gradeLevel')
            ->add('blockId')
            ->add('target')
         ->end()
        ;
    }
    
    

    public function preUpdate($object)
    {
        foreach($object->getPu() as $child) {
            //set default values that the user does not choose on the form
            $child->setEventGradeLevelBlock($object);
            
      }
    }
    
    
}
