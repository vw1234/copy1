<?php

namespace Nwp\AssessmentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;

use Doctrine\ORM\QueryBuilder;

class EventGradeLevelBlockPromptFilterType extends AbstractType
{
    protected $current_event_id;
    protected $user_role_id;
    protected $user_grade_level_id;
    protected $user_table_id;
    protected $role_scorer1;
    protected $role_scorer2;
    protected $role_table_leader;
    protected $role_room_leader;   
    protected $role_event_leader;
    protected $role_admin;

    public function __construct($current_event_id,$user_role_id,$user_grade_level_id,$user_table_id,$role_scorer1,$role_scorer2,$role_table_leader,$role_room_leader,$role_event_leader,$role_admin)
    {
        $this->current_event_id = $current_event_id;
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
        $event_id = $this->current_event_id;
        $role_id = $this->user_role_id;
        $grade_level_id = $this->grade_level_id;
        $table_id = $this->user_table_id;
        $role_scorer1_id=$this->role_scorer1;
        $role_scorer2_id=$this->role_scorer2;
        $role_table_leader_id=$this->role_table_leader;
        $role_room_leader_id=$this->role_room_leader;
        $role_event_leader_id=$this->role_event_leader; 
        $role_admin_id=$this->role_admin;
        
        if (($role_id== $role_admin_id) || ($role_id== $role_event_leader_id)) {
                $builder->add('gradeLevelId', 'filter_entity', array('apply_filter'  => 
                            function (QueryBuilder $queryBuilder, $expr, $field, array $values) {
                              if (!empty($values['value'])) {
                                  $queryBuilder->andWhere('b.gradeLevel = :gradeLevelId')
                                  ->setParameter('gradeLevelId', $values['value']);
                              }
                            }, 'class' =>'NwpAssessmentBundle:GradeLevel','label' =>'Scoring Room'
                          ));
         
            $builder->add('tableId', 'filter_number_range', array('label' =>'Table'));
            $builder->add('blockId', 'filter_number', array('label' =>'Scoring Block',
                
                 'apply_filter'  => 
                              function (QueryBuilder $queryBuilder, $expr, $field, $values) {
                                if (!empty($values['value'])) {
                                    $queryBuilder->andWhere('b.blockId = :blockId')
                                     ->setParameter('blockId', $values['value']);
                                }
                              }  
                            ));      
         }
          
          
         $builder->add('prompt', 'filter_entity', array('class' =>'NwpAssessmentBundle:Prompt','label' =>'Prompt',
                              'apply_filter'  => 
                              function (QueryBuilder $queryBuilder, $expr, $field, array $values) {
                                if (!empty($values['value'])) {
                                    $queryBuilder->andWhere('e.prompt = :prompt')
                                     ->setParameter('prompt', $values['value']);
                                }
                              },        
                              'query_builder' => function ($er) use ($event_id,$role_id,$grade_level_id,$table_id,$role_event_leader_id,$role_room_leader_id,$role_table_leader_id,$role_admin_id) 
                                {
                                    $qb = $er->createQueryBuilder('u');
                                    $qb->Join('NwpAssessmentBundle:EventGradeLevelBlockPrompt','eu',
                                                 \Doctrine\ORM\Query\Expr\Join::WITH,
                                                'eu.prompt = u.id');
                                    $qb->Join('NwpAssessmentBundle:EventGradeLevelBlock','b',
                                                 \Doctrine\ORM\Query\Expr\Join::WITH,
                                                'eu.eventGradeLevelBlock = b.id');
                                    $qb->where('b.event='.$event_id);
                                    if (($role_id!= $role_admin_id) && ($role_id!= $role_event_leader_id))  {
                                       $qb->andWhere('b.gradeLevel='.$grade_level_id); 
                                    }
                                    if (($role_id!= $role_admin_id) && ($role_id!= $role_event_leader_id) && ($role_id!= $role_room_leader_id))  {
                                       $qb->andWhere('eu.tableId='.$table_id); 
                                    }
                                    return $qb;
                                }
                    ));
          
         
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
        return 'nwp_assessmentbundle_eventgradelevelblockfiltertype';
    }
}
