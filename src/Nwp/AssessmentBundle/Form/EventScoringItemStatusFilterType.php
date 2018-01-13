<?php

namespace Nwp\AssessmentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;

use Doctrine\ORM\QueryBuilder;

class EventScoringItemStatusFilterType extends AbstractType
{
    
    protected $statuses;
    protected $current_event_id;
    protected $component_id;
    protected $user_role_id;
    protected $user_grade_level_id;
    protected $user_table_id;
    protected $role_scorer1;
    protected $role_scorer2;
    protected $role_table_leader;
    protected $role_room_leader;   
    protected $role_event_leader;
    protected $role_admin;

    public function __construct($statuses,$current_event_id,$component_id,$user_role_id,$user_grade_level_id,$user_table_id,$role_scorer1,$role_scorer2,$role_table_leader,$role_room_leader,$role_event_leader,$role_admin)
    {
        $this->statuses = $statuses;
        $this->current_event_id = $current_event_id;
        $this->component_id = $component_id;
        $this->user_role_id =$user_role_id;
        $this->grade_level_id =$user_grade_level_id;
        $this->user_table_id = $user_table_id;
        $this->role_scorer1 = $role_scorer1;
        $this->role_scorer2 = $role_scorer2;
        $this->role_table_leader = $role_table_leader;
        $this->role_room_leader = $role_room_leader;
        $this->role_event_leader = $role_event_leader;
        $this->role_admin = $role_admin;
       
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $Ids = $this->statuses;
        $event_id = $this->current_event_id;
        $component_id = $this->component_id;
        $role_id = $this->user_role_id;
        $grade_level_id = $this->grade_level_id;
        $table_id = $this->user_table_id;
        $role_scorer1_id=$this->role_scorer1;
        $role_scorer2_id=$this->role_scorer2;
        $role_table_leader_id=$this->role_table_leader;
        $role_room_leader_id=$this->role_room_leader;
        $role_event_leader_id=$this->role_event_leader; 
        $role_admin_id=$this->role_admin;
        
        if ($component_id==2) {
            
            $builder->add('scoringItem', 'filter_number_range', array('label' =>'Paper Id'));
            if (($role_id== $role_scorer1_id) || ($role_id== $role_scorer2_id) || ($role_id==$role_admin_id)) {
                
                $builder->add('status', 'filter_entity', array('apply_filter'  => 
                              function (QueryBuilder $queryBuilder, $expr, $field, array $values) {
                                if (!empty($values['value'])) {
                                    if ($values['value']=="Ready") {
                                       $queryBuilder->andWhere('esi.status = :status and esu.status is null'); 
                                    } else {
                                       $queryBuilder->andWhere('esu.status = :status'); 
                                    }

                                    $queryBuilder->setParameter('status', $values['value']);
                                }
                              }, 'class' =>'NwpAssessmentBundle:ScoringItemStatus','label' =>'Current Status',
                                 'query_builder' => function ($er) use ($Ids) 
                                  {
                                    $qb = $er->createQueryBuilder('status');
                                    $qb->where('status.id IN ('.$Ids.')')
                                    ;
                                    return $qb;
                                    }
                            ));
                           
             }
            
             if (($role_id== $role_admin_id) || ($role_id== $role_event_leader_id)) {
                $builder->add('gradeLevelId', 'filter_entity', array('apply_filter'  => 
                            function (QueryBuilder $queryBuilder, $expr, $field, array $values) {
                              if (!empty($values['value'])) {
                                  $queryBuilder->andWhere('si.gradeLevel = :gradeLevelId')
                                  ->setParameter('gradeLevelId', $values['value']);
                              }
                            }, 'class' =>'NwpAssessmentBundle:GradeLevel','label' =>'Scoring Room'
                          ));
              }
                        
        } else {
         
           if ($role_id== $role_admin_id) {
                $builder->add('scoringItem', 'filter_number_range', array('label' =>'Paper Id'));
                $builder->add('eventScoringItem', 'filter_number_range', array('label' =>'Event Scoring Item Id'));
                $builder->add('scoringRoundNumber', 'filter_number_range', array('label' =>'Scoring Round Number'));
                $builder->add('createdBy', 'filter_entity', array('class' =>'Application\Sonata\UserBundle\Entity\User','label' =>'Created By',
                              'apply_filter'  => 
                              function (QueryBuilder $queryBuilder, $expr, $field, array $values) {
                                if (!empty($values['value'])) {
                                    $queryBuilder->andWhere('esu.createdBy = :createdBy')
                                     ->setParameter('createdBy', $values['value']);
                                }
                              },        
                              'query_builder' => function ($er) use ($event_id,$role_id,$grade_level_id,$table_id,$role_room_leader_id,$role_table_leader_id) 
                                {
                                    $qb = $er->createQueryBuilder('u');
                                    $qb->leftJoin('NwpAssessmentBundle:EventUser','eu',
                                                 \Doctrine\ORM\Query\Expr\Join::WITH,
                                                'eu.user = u.id');
                                    $qb->where('eu.event='.$event_id);
                                    if (($role_id==$role_room_leader_id) || ($role_id==$role_table_leader_id)) {
                                       $qb->andWhere('eu.gradeLevel='.$grade_level_id); 
                                    }
                                    if ($role_id==$role_table_leader_id) {
                                       $qb->andWhere('eu.tableId='.$table_id); 
                                    }
                                    $qb->orderBy('u.lastname,u.firstname'); 
                                    return $qb;
                                }
                    ));               
                    $builder->add('assignedTo', 'filter_entity', array('class' =>'Application\Sonata\UserBundle\Entity\User','label' =>'Assigned To',
                              'apply_filter'  => 
                              function (QueryBuilder $queryBuilder, $expr, $field, array $values) {
                                if (!empty($values['value'])) {
                                    $queryBuilder->andWhere('esu.assignedTo = :assignedTo')
                                     ->setParameter('assignedTo', $values['value']);
                                }
                              },        
                              'query_builder' => function ($er) use ($event_id,$role_id,$grade_level_id,$table_id,$role_room_leader_id,$role_table_leader_id,$role_admin_id) 
                                {
                                    $qb = $er->createQueryBuilder('u');
                                    $qb->leftJoin('NwpAssessmentBundle:EventUser','eu',
                                                 \Doctrine\ORM\Query\Expr\Join::WITH,
                                                'eu.user = u.id');
                                    $qb->where('eu.event='.$event_id);
                                    if (($role_id==$role_room_leader_id) || ($role_id==$role_table_leader_id)) {
                                       $qb->andWhere('eu.gradeLevel='.$grade_level_id); 
                                    }
                                    if ($role_id==$role_table_leader_id) {
                                       $qb->andWhere('eu.tableId='.$table_id); 
                                    }
                                    $qb->orderBy('u.lastname,u.firstname'); 
                                    return $qb;
                                }
                    ));
                     $builder->add('timeCreated', 'filter_date_range', array('label' =>'Time Created'));
           }
            
           if (($role_id== $role_admin_id) || ($role_id== $role_event_leader_id) || ($role_id== $role_room_leader_id) || ($role_id== $role_table_leader_id)) {     
                    $builder->add('status', 'filter_entity', array('apply_filter'  => 
                          function (QueryBuilder $queryBuilder, $expr, $field, array $values) {
                            if (!empty($values['value'])) {
                                $queryBuilder->andWhere('esu.status = :status')
                                ->setParameter('status', $values['value']);
                            }
                          }, 'class' =>'NwpAssessmentBundle:ScoringItemStatus','label' =>'Current Status',
                             'query_builder' => function ($er) use ($Ids) 
                              {
                                $qb = $er->createQueryBuilder('status');
                                $qb->where('status.id IN ('.$Ids.')')
                                ;
                                return $qb;
                                }
                        ));
           
                       $builder->add('scoredBy', 'filter_entity', array('class' =>'Application\Sonata\UserBundle\Entity\User','label' =>'Scored By',
                              'apply_filter'  => 
                              function (QueryBuilder $queryBuilder, $expr, $field, array $values) {
                                if (!empty($values['value'])) {
                                    $queryBuilder->andWhere('esu.scoredBy = :scoredBy')
                                     ->setParameter('scoredBy', $values['value']);
                                }
                              },        
                              'query_builder' => function ($er) use ($event_id,$role_id,$grade_level_id,$table_id,$role_room_leader_id,$role_table_leader_id,$role_admin_id) 
                                {
                                    $qb = $er->createQueryBuilder('u');
                                    $qb->leftJoin('NwpAssessmentBundle:EventUser','eu',
                                                 \Doctrine\ORM\Query\Expr\Join::WITH,
                                                'eu.user = u.id');
                                    $qb->where('eu.event='.$event_id);
                                    if (($role_id==$role_room_leader_id) || ($role_id==$role_table_leader_id)) {
                                       $qb->andWhere('eu.gradeLevel='.$grade_level_id); 
                                    }
                                    if ($role_id==$role_table_leader_id) {
                                       $qb->andWhere('eu.tableId='.$table_id); 
                                    }
                                    $qb->orderBy('u.lastname,u.firstname'); 
                                    return $qb;
                                }
                    ));
            }
            
              if (($role_id== $role_admin_id) || ($role_id== $role_event_leader_id) || ($role_id== $role_room_leader_id)) {
                  
                        
                 $builder->add('tableIdScored', 'filter_number_range', array('label' =>'Scoring Table'));
              }
              
         
       
              
              if (($role_id== $role_admin_id) || ($role_id== $role_event_leader_id)) {
                $builder->add('gradeLevelId', 'filter_entity', array('apply_filter'  => 
                            function (QueryBuilder $queryBuilder, $expr, $field, array $values) {
                              if (!empty($values['value'])) {
                                  $queryBuilder->andWhere('esu.gradeLevelId = :gradeLevelId')
                                  ->setParameter('gradeLevelId', $values['value']);
                              }
                            }, 'class' =>'NwpAssessmentBundle:GradeLevel','label' =>'Scoring Room'
                          ));
              }
           
       } //end of component check             
            
                        

        $listener = function(FormEvent $event)
        {
            // Is data empty?
            foreach ($event->getData() as $data) {
                if(is_array($data)) {
                    foreach ($data as $subData) {
                        if(!empty($subData)) return;
                    }
                }
                else {
                    if(!empty($data)) return;
                }
            }

            $event->getForm()->addError(new FormError('Filter empty'));
        };
        $builder->addEventListener(FormEvents::POST_BIND, $listener);
    }

    public function getName()
    {
        return 'nwp_assessmentbundle_eventscoringitemstatusfiltertype';
    }
}
