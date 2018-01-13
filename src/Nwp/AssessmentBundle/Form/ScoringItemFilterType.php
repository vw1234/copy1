<?php

namespace Nwp\AssessmentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;

use Doctrine\ORM\QueryBuilder;

class ScoringItemFilterType extends AbstractType
{
    protected $projects;

    public function __construct($projects)
    {
        $this->projects = $projects;    
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $Ids="";
        foreach($this->projects as $project) {
            $Ids .= $project->getId().",";
        }
        $Ids = substr($Ids, 0, -1); //strip last comma
        
        $builder
            ->add('project', 'filter_entity', array('class' =>'NwpAssessmentBundle:Project','label' =>'Project','choices' =>$this->projects)) 
            ->add('gradeLevel', 'filter_entity', array('class' =>'NwpAssessmentBundle:GradeLevel','label' =>'Grade Level'))
            ->add('id', 'filter_number_range', array('label' =>'Paper Id'))
            ->add('studentId', 'filter_number_range', array('label' =>'Student Id'))
            ->add('administrationTime', 'filter_number_range', array('label' =>'Administration Time'))
            ->add('year', 'filter_entity', array('class' =>'NwpAssessmentBundle:Year','label' =>'School Year'))
            ->add('prompt', 'filter_number_range', array('label' =>'Prompt Id'))
            ->add('organizationType', 'filter_entity', array('class' =>'NwpAssessmentBundle:OrganizationType','label' =>'Organization Type'))
            ->add('ncesId', 'filter_text', array('label' =>'Nces Id'))
            ->add('psId', 'filter_text', array('label' =>'Ps Id'))
            ->add('districtId', 'filter_number_range', array('label' =>'District Id'))
            ->add('ipedsId', 'filter_number_range', array('label' =>'Ipeds Id'))
            ->add('organizationName', 'filter_text', array('label' =>'Organization Name'))
            ->add('state', 'filter_entity', array('class' =>'NwpAssessmentBundle:State','label' =>'State'))
            ->add('classroomId', 'filter_text', array('label' =>'Classroom Id'))
            ->add('teacherId', 'filter_text', array('label' =>'Teacher Id'))
            ->add('dateUploaded', 'filter_date_range', array('label' =>'Date Uploaded'))
        ;

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
        return 'nwp_assessmentbundle_scoringitemfiltertype';
    }
    
}
