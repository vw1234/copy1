<?php

namespace Nwp\AssessmentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;

use Doctrine\ORM\QueryBuilder;

class EventScoringItemFilterType extends AbstractType
{
    
    protected $projectIds;
    protected $events;
    protected $role_admin;

    public function __construct($events,$projectIds, $role_admin)
    {
        $this->events = $events;
        $this->projectIds = $projectIds; 
        $this->role_admin = $role_admin;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $projectIds = $this->projectIds;
        $role_admin_id=$this->role_admin;
         
        $builder
            ->add('id', 'filter_number_range')
            ->add('scoringItem', 'filter_number_range', array('label' =>'Paper Id'));
            
        if ($role_admin_id !="") {
            $builder->add('project', 'filter_entity', array('class' =>'NwpAssessmentBundle:Project','label' =>'Project',
                          'apply_filter'  => 
                                  function (QueryBuilder $queryBuilder, $expr, $field, array $values) {
                                    if (!empty($values['value'])) {
                                        $queryBuilder->andWhere('s.project  = :project')
                                         ->setParameter('project', $values['value']);
                                    }
                                  },        
                                  'query_builder' => function ($er) use ($projectIds) 
                                  {
                                    $qb = $er->createQueryBuilder('p');
                                    $qb->where('p.id IN ('.$projectIds.')');
                                    return $qb;
                                    }
            ));  
        }            
                    
         $builder->add('event', 'filter_entity', array('class' =>'NwpAssessmentBundle:Event','label' =>'Event', 'choices' =>$this->events));
        $builder->add('gradeLevelId', 'filter_entity', array('apply_filter'  => 
                            function (QueryBuilder $queryBuilder, $expr, $field, array $values) {
                              if (!empty($values['value'])) {
                                  $queryBuilder->andWhere('s.gradeLevel = :gradeLevelId')
                                  ->setParameter('gradeLevelId', $values['value']);
                              }
                            }, 'class' =>'NwpAssessmentBundle:GradeLevel','label' =>'Grade Level'
                          ));
         $builder->add('component', 'filter_entity', array('class' =>'NwpAssessmentBundle:Component','label' =>'Component'))
                 ->add('dateUpdated', 'filter_date_range', array('label' =>'Date Updated'));

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
        return 'nwp_assessmentbundle_eventscoringitemfiltertype';
    }
}
