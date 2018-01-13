<?php

namespace Nwp\AssessmentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;

use Doctrine\ORM\QueryBuilder;

class CalibrationFilterType extends AbstractType
{
    
    protected $statuses;
    protected $current_event_id;
    protected $user_role_id;
    protected $user_grade_level_id;
    protected $user_table_id;
    protected $role_room_leader;
    protected $role_table_leader;
    protected $role_event_leader;
    protected $role_admin;

    public function __construct($statuses,$current_event_id,$user_role_id,$user_grade_level_id,$user_table_id,$role_event_leader,$role_room_leader,$role_table_leader,$role_admin)
    {
        $this->statuses = $statuses;
        $this->current_event_id = $current_event_id;
        $this->user_role_id =$user_role_id;
        $this->grade_level_id =$user_grade_level_id;
        $this->user_table_id = $user_table_id;
        $this->role_event_leader = $role_event_leader;
        $this->role_room_leader = $role_room_leader;
        $this->role_table_leader = $role_table_leader;
        $this->role_admin = $role_admin;
       
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $Ids = $this->statuses;
        $event_id = $this->current_event_id;
        $role_id = $this->user_role_id;
        $grade_level_id = $this->grade_level_id;
        $table_id = $this->user_table_id;
        $role_event_leader_id=$this->role_event_leader;
        $role_room_leader_id=$this->role_room_leader;
        $role_table_leader_id=$this->role_table_leader;
        $role_admin_id=$this->role_admin;
         

        $builder->add('scoringItem', 'filter_number_range', array('label' =>'Paper Id'));
               
      
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
        return 'nwp_assessmentbundle_calibrationfiltertype';
    }
}
