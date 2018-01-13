<?php

namespace Nwp\AssessmentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Menu\ItemInterface as MenuItemInterface;
 

class ProjectUserAdmin extends Admin
{
 // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'ASC',
        '_sort_by' => 'project'
    );
 
   // public function getNewInstance()
   // {
   //     $instance = parent::getNewInstance();
    
   //     $em = $this->modelManager->getEntityManager('Nwp\AssessmentBundle\Entity\Role');
   //     $role = $em->getRepository('NwpAssessmentBundle:Role')->findOneBy(array('name' => 'Room Leader'));
   //     echo "role is ".$role->getId();
        
    //    $instance->setRole($role);
    //    return $instance;
    //}

    protected function configureFormFields(FormMapper $formMapper)
    {  
 
        
       
        $formMapper
        ->add('project', 'entity', array('class' => 'NwpAssessmentBundle:Project'))
        ->add('user', 'entity', array('class' => 'Application\Sonata\UserBundle\Entity\User'))
        ->add('role', 'entity', array('class' => 'NwpAssessmentBundle:Role',
             'query_builder' => function($er)  {
                                                $qb = $er->createQueryBuilder('r')
                                                ->select('r')
                                                ->where('r.structure =1');
                                                return $qb;
                                            })
          )
        #->add('role','hidden', array('data' => $role->getId(), 'property_path' => false))
        ;
       }
    
    #public function createQuery($context = 'list')
    #{
    #    $em = $this->modelManager->getEntityManager('Nwp\AssessmentBundle\Entity\ProjectUser');
#
   #     $queryBuilder = $em
    #        ->createQueryBuilder('p')
    #        ->select('p')
    #        ->from('NwpAssessmentBundle:ProjectUser', 'p')
    #        ->where('p.role=3');

    #    $query = new ProxyQuery($queryBuilder);
    #    return $query;
    #}
    
    
    #public function createAction($context = 'form')
   # {
    #    $em = $this->modelManager->getEntityManager('Nwp\AssessmentBundle\Entity\Role');

    #    $role = $em->getRepository('NWPWAssessmentBundle:Role')->findOneBy(array('name' => 'Room Leader'));
    #    return $role;
    #}
 
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('project')
            ->add('user')
            ->add('role')
        ;
    }
    
    

 
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('project')
            ->add('user')
            ->add('role')
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
            ->add('project')
            ->add('user')
            ->add('role')
        ;
    }
    
    public function getName()
    {
        return 'project_user';
    }
    
     public function getDefaultOptions(array $options)
    {
        $options['data_class'] = 'NWP\AssessmentBundle\Entity\ProjectUser';

        return $options;
    }
}
