<?php

namespace Nwp\AssessmentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Menu\ItemInterface as MenuItemInterface;

use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

use Nwp\AssessmentBundle\Entity\ScoringRubric;

 
class EventAdmin extends Admin
{

#protected $baseRouteName = 'nwp.assessment.admin.event';
#protected $baseRoutePattern = 'event';

 // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'startDate'
    );
    
    public function getFormTheme()
    {
        return array_merge(
        parent::getFormTheme(),
        array('NwpAssessmentBundle:EventUser:admin.room.html.twig')
        );
    }
    
    
    public function getTemplate($name)
    {
      switch ($name) {
        case 'edit':
            return 'NwpAssessmentBundle:Event:admin.event_edit.html.twig';
            break;
        default:
            return parent::getTemplate($name);
            break;
        }
    }
 
    protected function configureFormFields(FormMapper $formMapper)
    {
       $subject = $this->getSubject();
       $id = $subject->getId();
       
       for ($a=1; $a<5; $a++) { 
         $adjudicationTrigger[$a] =$a;
       }
      
       if ($id) {
           //in Edit mode, can edit Event Rooms
           
        
        $formMapper
           ->add('name', null, array('required' => true))
           ->add('startDate', 'datetime', array('required' => true))
           ->add('endDate', 'datetime', array('required' => true))
           ->add('location', 'textarea', array('required' => false, 'max_length'=>"250"))
           ->add('description', 'textarea', array('required' => false, 'max_length'=>"250"))
           ->add('announcements', 'textarea', array('required' => false, 'max_length'=>"500"))
           ->add('scoringRubric', 'entity', array('class' => 'NwpAssessmentBundle:ScoringRubric'))
           ->add('eventType', 'entity', array('class' => 'NwpAssessmentBundle:EventType'))
           ->add('adjudicationTrigger', 'choice', array('choices' => $adjudicationTrigger,'required' => true, 'label' => 'Adjudication Trigger'))
           ->add('secondScoringTableTrigger', null, array('label' =>'Second Scoring Table Trigger'))     
          # ->with('Rooms')
          # ->add('eu','sonata_type_collection', array('by_reference' => 
         #           false,'label' =>'Add/Delete Rooms','required' =>false), array('edit' => 'inline','inline' => 
         #           'table','targetEntity'=>'Nwp\AssessmentBundle\Entity\EventUser2'))
         #  #->end()
;
        ;
       } else {
           //in Create mode, cannot edit Event Rooms until Event is created
           $formMapper
           ->add('name', null, array('required' => true))
           ->add('startDate', 'datetime', array('required' => true))
           ->add('endDate', 'datetime', array('required' => true))
           ->add('location', 'textarea', array('required' => false, 'max_length'=>"250"))
           ->add('description', 'textarea', array('required' => false, 'max_length'=>"250"))
           ->add('announcements', 'textarea', array('required' => false, 'max_length'=>"500"))
           ->add('eventType', 'entity', array('class' => 'NwpAssessmentBundle:EventType'))
           ->add('scoringRubric', 'entity', array('class' => 'NwpAssessmentBundle:ScoringRubric'))
           ->add('adjudicationTrigger', 'choice', array('choices' => $adjudicationTrigger,'required' => true, 'label' => 'Adjudication Trigger'))
           ->add('secondScoringTableTrigger', null, array('label' =>'Second Scoring Table Trigger'))  
            ;
       }
    }
    
    public function createQuery($context = 'list')
    {
        $em = $this->modelManager->getEntityManager('Nwp\AssessmentBundle\Entity\Event');

        $sql_where ="";
        
        $request = $this->getRequest();
        $event_id=$request->get('event');
        
        if ($event_id) {
            $queryBuilder = $em
                ->createQueryBuilder('p')
                ->select('p')
                ->from('NwpAssessmentBundle:Event', 'p')
                ->where(" p.id =".$event_id)
                ->orderBy('p.id')
            ;         
        } else {
            $queryBuilder = $em
                ->createQueryBuilder('p')
                ->select('p')
                ->from('NwpAssessmentBundle:Event', 'p')
                ->orderBy('p.id')
            ; 
        }
        
        
        
        

        $query = new ProxyQuery($queryBuilder);
        return $query;
    }
 
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('startDate')
            ->add('endDate')
            ->add('description')
        ;
    }
 
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
           
            //->add('custom', 'string', array('template' => 'NwpAssessmentBundle:Event:admin.room_list.html.twig'))
            ->addIdentifier('event')
            ->add('startDate')
            ->add('endDate')
            ->add('description')
            ->add('_action', 'actions', array(
                'actions' => array(
                    //'view' => array(),
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
            ->add('startDate')
            ->add('endDate')
            ->add('description')
            ->add('eventType')
            ->add('scoringRubric')
            ->add('adjudicationTrigger')
            ->add('secondScoringTableTrigger')
        
        ;
    }
    
#    public function preUpdate($object) {
#      $event = $object->getEvent();
#     
#      $em = $this->modelManager->getEntityManager('Nwp\AssessmentBundle\Entity\Role');
#      $role = $em->getRepository('NwpAssessmentBundle:Role')->findOneBy(array('name' => 'Room Leader')); 
#    
#      foreach($object->getEu() as $child) {
#        //set default values that the user does not choose on the Room form
#        $child->setEvent($event);
#        $child->setTableId(null);
#        $child->setRole($role);
#      }
#      
#      $em->flush();
#    
#    }
}
