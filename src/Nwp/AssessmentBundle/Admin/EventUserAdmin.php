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
 
class EventUserAdmin extends Admin
{
    
    protected $baseRouteName = 'nwp.assessment.admin.eventuser';
    
    protected $baseRoutePattern = 'eventuser-user';
    
 // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'ASC',
        '_sort_by' => 'user'
    );
 
    protected function configureFormFields(FormMapper $formMapper)
    {
        $subject = $this->getSubject();
        
        $em = $this->modelManager->getEntityManager('Nwp\AssessmentBundle\Entity\EventUser');
        
        $query  = $em->createQueryBuilder();
        $query->select('u')
            ->from('Application\Sonata\UserBundle\Entity\User', 'u');
         
        //Only show the roles Scorer 1 and Scorer 2 in the Role dropdown at Table Level (when adding/deleting Scorers)
        $edit_table=false;
        $url= $_SERVER["REQUEST_URI"];
       
        if ((strpos($url, "/eventuser-table/")!== false) || ($this->getRequest()->get('code')=="nwp.assessment.admin.eventtable")) { 
             $edit_table=true;
        }
        
        //Allow to add up to 25 tables per Room
        $tables=array();
        for ($t=1; $t<=25; $t++) { 
            $tables[$t] ='Table '.$t;
        }
        
       
        $formMapper
           ->add('event', 'entity', array('class' => 'NwpAssessmentBundle:Event','required' => true))
           ->add('gradeLevel', 'entity', array('class' => 'NwpAssessmentBundle:GradeLevel','required' => false,'label' => 'Room'))
           ->add('tableId', 'choice', array('choices' => $tables,'required' => false, 'label' => 'Table'))
           ->add('user', 'sonata_type_model', array('query' => $query))
           ->add('role', 'entity', array('class' => 'NwpAssessmentBundle:Role',
             'query_builder' => function($er) use ($edit_table) {
                                                $qb = $er->createQueryBuilder('r')
                                                ->select('r')
                                                ->where('r.structure =2');
                                                if ($edit_table==true) {
                                                    $qb->andWhere("r.name='Scorer 1'")
                                                       ->orWhere("r.name='Scorer 2'");
                                                }
                                                return $qb;
                                            }))
           ->add('target', null, array('label' =>'Target'))
           ->add('groupings', 'entity', array('class' => 'Nwp\AssessmentBundle\Entity\Grouping','multiple'  => true, 'expanded' => true))
                                            
        
        ;
      
    }
    
    public function createQuery($context = 'list')
    {
        $em = $this->modelManager->getEntityManager('Nwp\AssessmentBundle\Entity\EventUser');

        $sql_where ="";
        
        $request = $this->getRequest();
        $event_id=$request->get('event');
        $room_id=$request->get('gradeLevel');
        $table_id=$request->get('tableId');
        
        if ($event_id) {
            $sql_where .= "p.event =".$event_id;
        }
        
        if ($room_id) {
            $sql_where .= " AND p.gradeLevel =".$room_id;
        }
        
        if ($table_id) {
            $sql_where .= " AND p.tableId =".$table_id;
        }
        
        if ($sql_where !="") {
            $queryBuilder = $em
                ->createQueryBuilder('p')
                ->select('p')
                ->from('NwpAssessmentBundle:EventUser', 'p')
                ->where($sql_where)
                ->orderBy('p.user ASC,p.event,p.gradeLevel,p.tableId,p.role');
        } else {
            $queryBuilder = $em
                ->createQueryBuilder('p')
                ->select('p')
                ->from('NwpAssessmentBundle:EventUser', 'p')
                ->orderBy('p.user ASC,p.event,p.gradeLevel,p.tableId,p.role');
        }
        $query = new ProxyQuery($queryBuilder);
        return $query;
    }
 
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('event')
            ->add('gradeLevel')
            ->add('tableId')
            ->add('user')
            ->add('role')
            ->add('target')
            ->add('groupings')
        ;
    }
 
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
         ->addIdentifier('user')
            ->add('event')
            ->add('gradeLevel')
            ->add('tableId')
            ->add('role')
            ->add('target')
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
            ->add('tableId', null, array('label' => 'Table'))
            ->add('user')
            ->add('role')
            ->add('target')
        ;
    }
    
    public function getExportFields() {
        //customize Attendees (Event User) export for csv, xls, etc. in Admin site
        return array(
          'Id' => 'id',
          'User' => 'user',
          'Event' => 'event',
          'Grade Level' => 'gradeLevel',
          'Table' => 'tableId',
          'Role' => 'role',
          'Target' => 'target',
          'MaxBlock' => 'maxBlock',
        );
}
}
