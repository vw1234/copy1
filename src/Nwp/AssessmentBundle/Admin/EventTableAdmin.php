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

 
class EventTableAdmin extends Admin
{
    protected $baseRouteName = 'nwp.assessment.admin.eventtable';
    
    protected $baseRoutePattern = 'eventuser-table'; 
    
    //Setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'event,gradeLevel,tableId,role,user'
    );
    
    public function getFormTheme()
    {
        return array_merge(
        parent::getFormTheme(),
        array('NwpAssessmentBundle:EventUser:admin.tablescorer.html.twig')
        );
    }
    
    public function getTemplate($name)
    {
      switch ($name) {
        case 'edit':
            return 'NwpAssessmentBundle:EventUser:admin.table_edit.html.twig';
            break;
        case 'list':
            return 'NwpAssessmentBundle:EventUser:admin.standard_list.html.twig';
            break;
        default:
            return parent::getTemplate($name);
            break;
        }
    }
 
    
    public function __construct($code, $class, $baseControllerName){ 
        parent::__construct($code, $class, $baseControllerName); 
        $this->classnameLabel = 'Table';
    }

    public function getNewInstance()
    {
        $instance = parent::getNewInstance();
    
        $em = $this->modelManager->getEntityManager('Nwp\AssessmentBundle\Entity\Role');
        $role = $em->getRepository('NwpAssessmentBundle:Role')->findOneBy(array('name' => 'Table Leader'));
        
        $instance->setRole($role);
        
        return $instance;
    }
 
    protected function configureFormFields(FormMapper $formMapper)
    {
       $subject = $this->getSubject();
       $id = $subject->getId();
      
      
       if ($id) {  //in Edit mode, Table can be updated with a new Table Leader and Scorers can be added/updated  
           $formMapper
           ->with($subject->getEvent()." - Room ".$subject->getGradeLevel()." - Table ".$subject->getTableId())
           ->add('user', 'entity', array('class' => 'Application\Sonata\UserBundle\Entity\User','label' => 'Edit Table Leader'))
          ->end()
          # ->with('Scorers (8 maximum per Table)')
          #   ->add('eu','sonata_type_collection', array( 'by_reference' => true,'label' =>'Add/Delete Scorers','required' =>false), 
          #         array('edit' => 'inline','inline' => 'table','targetEntity'=>'Nwp\AssessmentBundle\Entity\EventUser'))
          # ->end();
        ;         
       } else {  // in Create mode, a Table can be created, but scorers cannot be added yet
           
           //Allow to add up to 25 tables per Room
           $tables=array();
           for ($t=1; $t<=25; $t++) { 
                $tables[$t] ='Table '.$t;
           }
           
           $formMapper
           ->add('event', 'entity', array('class' => 'NwpAssessmentBundle:Event','required' => true))
           ->add('gradeLevel', 'entity', array('class' => 'NwpAssessmentBundle:GradeLevel','required' => true,'label' => 'Room'))
           ->add('tableId', 'choice', array('choices' => $tables,'required' => true, 'label' => 'Table'))
           ->add('user', 'entity', array('class' => 'Application\Sonata\UserBundle\Entity\User','label' => 'Table Leader'))    
        ;
       }  
    }
    
    
    public function createQuery($context = 'list')
    {
        $em = $this->modelManager->getEntityManager('Nwp\AssessmentBundle\Entity\EventUser');
        
        $sql_where ="";
        
        $role = $em->getRepository('NwpAssessmentBundle:Role')->findOneBy(array('name' => 'Table Leader')); 
        $role_id=$role->getId();
        $sql_where .= " AND p.role =".$role_id;
        
        $queryBuilder = $em
            ->createQueryBuilder('p')
            ->select('p')
            ->from('NwpAssessmentBundle:EventUser', 'p')
            ->where('p.tableId is not null '.$sql_where)
            ->orderBy('p.event DESC,p.gradeLevel, p.tableId');

        $query = new ProxyQuery($queryBuilder);
        return $query;
    }
    
 
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('event')
            ->add('gradeLevel')
            ->add('tableId')
        ;
    }
 
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
         ->addIdentifier('tableId')
            ->add('gradeLevel')
            ->add('event')
            ->add('user', null, array('label' => 'Table Leader'))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }
    
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('event')
            ->add('gradeLevel', null, array('label' => 'Room'))
        ;
    }
    
    
  public function preUpdate($object) {
      $event = $object->getEvent();
      $gradeLevel = $object->getGradeLevel();
      $tableId = $object->getTableId();
      
      foreach($object->getEu() as $child) {
        //set default values that the user does not choose on the Table Leader form
        $child->setEvent($event);
        $child->setGradeLevel($gradeLevel);
        $child->setTableId($tableId);
      }
      
    }
  
}
