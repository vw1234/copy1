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

 
class EventRoomAdmin extends Admin
{
    protected $baseRouteName = 'nwp.assessment.admin.eventroom';
    
    protected $baseRoutePattern = 'eventuser-room';
    
    
// setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'event,gradeLevel,tableId,role,user'
    );
    
 
    
    public function getFormTheme()
    {
        return array_merge(
        parent::getFormTheme(),
        array('NwpAssessmentBundle:EventUser:admin.tableleader.html.twig')
        );
    }
    
    
    
    public function getTemplate($name)
    {
      switch ($name) {
        case 'edit':
            return 'NwpAssessmentBundle:EventUser:admin.room_edit.html.twig';
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
        $this->classnameLabel = 'Room';
    }

    public function getNewInstance()
    {
        $instance = parent::getNewInstance();
    
        $em = $this->modelManager->getEntityManager('Nwp\AssessmentBundle\Entity\Role');
        $role = $em->getRepository('NwpAssessmentBundle:Role')->findOneBy(array('name' => 'Room Leader'));
        
        $instance->setRole($role);
        $instance->setTableId(null);
        return $instance;
    }
 
    protected function configureFormFields(FormMapper $formMapper)
    {
        $subject = $this->getSubject();
        $id = $subject->getId();
      
       if ($id) {
           //in Edit mode, Room can be updated with a new Room Leader and Tables can be created/updated
           
           $formMapper
         
          // ->add('event', 'entity', array('class' => 'NwpAssessmentBundle:Event','required' => true,'disabled' => true))
          // ->add('gradeLevel', 'entity', array('class' => 'NwpAssessmentBundle:GradeLevel','required' => true,'label' => 'Room','disabled' => true))
          //->with("Room ".$subject->getGradeLevel()." - ".$subject->getEvent())
           ->with($subject->getEvent()." - Room ".$subject->getGradeLevel())
           ->add('user', 'entity', array('class' => 'Application\Sonata\UserBundle\Entity\User','label' => 'Edit Room Leader'))
          ->end()
          # ->with('Tables')
          #   ->add('eu','sonata_type_collection', array('by_reference' => true,'label' =>'Add/Delete Tables','required' =>false), 
          #         array('edit' => 'inline','inline' => 'table','targetEntity'=>'Nwp\AssessmentBundle\Entity\EventUser','admin_code' => 'nwp.assessment.admin.eventtable'))
          # ->end();
        ;
           
       } else {
           // in Create mode, a Room can be created, but tables cannot be added yet
           $formMapper
         
           ->add('event', 'entity', array('class' => 'NwpAssessmentBundle:Event','required' => true))
           ->add('gradeLevel', 'entity', array('class' => 'NwpAssessmentBundle:GradeLevel','required' => true,'label' => 'Room'))
           ->add('user', 'entity', array('class' => 'Application\Sonata\UserBundle\Entity\User','label' => 'Room Leader'))
           
           
        ;
       }
        
    }
    
    
    public function createQuery($context = 'list')
    {
        $em = $this->modelManager->getEntityManager('Nwp\AssessmentBundle\Entity\EventUser');
        
         $sql_where ="";
        
        //$request = $this->getRequest();
        //$event_id=$request->get('event');
        
        //if ($event_id) {
        //    $sql_where = " AND p.event =".$event_id;
        //}
         $role = $em->getRepository('NwpAssessmentBundle:Role')->findOneBy(array('name' => 'Room Leader')); 
        $role_id=$role->getId();
        $sql_where .= " AND p.role =".$role_id;

        $queryBuilder = $em
            ->createQueryBuilder('p')
            ->select('p')
            ->from('NwpAssessmentBundle:EventUser', 'p')
            ->where('p.tableId is null '.$sql_where)
            ->orderBy('p.event DESC,p.gradeLevel','ASC');

        $query = new ProxyQuery($queryBuilder);
        return $query;
    }
    
 
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('event')
            ->add('gradeLevel')
        ;
    }
 
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
         //->add('Room', 'string', array('template' => 'NwpAssessmentBundle:Event:admin.table_list.html.twig'))
            ->addIdentifier('gradeLevel')
            ->add('event')
            ->add('user', null, array('label' => 'Room Leader'))
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
    
  

/**
 * @param mixed $object
 * @return mixed|void
 */

  
  public function preUpdate($object) {
      $event = $object->getEvent();
      $gradeLevel = $object->getGradeLevel();
      $em = $this->modelManager->getEntityManager('Nwp\AssessmentBundle\Entity\Role');
      $role = $em->getRepository('NwpAssessmentBundle:Role')->findOneBy(array('name' => 'Table Leader')); 
    
      foreach($object->getEu() as $child) {
        //set default values that the user does not choose on the Table Leader form
        $child->setEvent($event);
        $child->setGradeLevel($gradeLevel);
        $child->setRole($role);
      }
      
       // $form = $this->getForm('eu');
      //$children = $form->getChildren();

      //foreach ($children as $childForm) {
        //$data = $childForm->getData();
        //if ($data instanceof Collection) {
           // $proxies = $childForm->getChildren();
           // foreach ($proxies as $proxy) {
                
             //   $entity = $proxy->getData();
                
                //if ($data) {
                //if (!$data->contains($entity)) {
                //    $this->getModelManager()->delete($entity);
                 //  echo "entity is ".$entity->getId();
                  // print_r($entity);
                  // die;
                //} else {
                //    echo "nothing was checked";
                //}
                    
                //}
           // }
        //echo $data;
        
       //}
       
   
      $em->flush();
    
    }
  
  
}
