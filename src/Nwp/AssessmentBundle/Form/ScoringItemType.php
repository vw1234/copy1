<?php

namespace Nwp\AssessmentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


class ScoringItemType extends AbstractType
{
   
    protected $previous_scoringitem;
    protected $projects;

    public function __construct($previous_scoringitem,$projects)
    {
        $this->previous_scoringitem = $previous_scoringitem;
        $this->projects = $projects;
       
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $previous_scoringitem = $this->previous_scoringitem;
        //echo "previous_scoringitem is ". $previous_scoringitem;
        
        $factory = $builder->getFormFactory();
        
        if (($builder->getData()->getId())  && ( $builder->getData()->getId() !=$previous_scoringitem)) {  
            $state = $builder->getData()->getState();
            
            $builder
            ->add('studentId', null, array('label' =>'Student Id*'))
            ->add('administrationTime', null, array('label' =>'Administration Time*','required' => true))
            ->add('year', null, array('label' =>'School Year*','required' => true))
            
            ->add('project', 'entity', array('class' => 'NwpAssessmentBundle:Project','label' =>'Project*','required' => true,
                                             'choices' =>$this->projects))  
            //->add('prompt', null, array('label' =>'Prompt'))
            ->add('gradeLevel', null, array('label' =>'Grade Level*','required' => true))
            ->add('organizationType', null, array('label' =>'Organization Type'))
             ->add('ncesId', null, array('label' =>'Nces Id','max_length' =>12))
            ->add('psId', null, array('label' =>'Ps Id','max_length' =>8))
            ->add('districtId', null, array('label' =>'District Id'))
            ->add('ipedsId', null, array('label' =>'Ipeds Id'))
            ->add('organizationName', null, array('label' =>'Organization Name','max_length' =>255))
            ->add('state', null, array('label' =>'State'))
            ->add('classroomId', null, array('label' =>'Classroom Id','max_length' =>15))
            ->add('teacherId', null, array('label' =>'Teacher Id','max_length' =>15));
             $file_id = $builder->getData()->getFileId();
             if ($file_id==null) { //cannot replace files, have to delete record to attach another file
                $builder->add('file', null);
             }
        
        } else {
            //print_r($this->project_array);
            $state = $builder->getData()->getState();
            $builder
            ->add('studentId', null, array('label' =>'Student Id*'))
            ->add('administrationTime', null, array('label' =>'Administration Time*','required' => true))
            ->add('year', null, array('label' =>'School Year*','required' => true))
            ->add('project', 'entity', array('class' => 'NwpAssessmentBundle:Project','label' =>'Project*','required' => true,                                          'choices' =>$this->projects))   
            //->add('prompt', null, array('label' =>'Prompt'))     
            ->add('gradeLevel', null, array('label' =>'Grade Level*','required' => true))
            ->add('organizationType', null, array('label' =>'Organization Type'))
            ->add('ncesId', null, array('label' =>'Nces Id','max_length' =>12))
            ->add('psId', null, array('label' =>'Ps Id','max_length' =>12))
            ->add('districtId', null, array('label' =>'District Id'))
            ->add('ipedsId', null, array('label' =>'Ipeds Id'))
            ->add('organizationName', null, array('label' =>'Organization Name','max_length' =>255))
            ->add('state', null, array('label' =>'State'))
            ->add('classroomId', null, array('label' =>'Classroom Id','max_length' =>15))
            ->add('teacherId', null, array('label' =>'Teacher Id','max_length' =>15))
            ->add('file', null)
        ;
        }
       
       $refreshCounty = function ($form, $state) use ($factory) {
      
       $form->add($factory->createNamed('county','entity', null, array(
                'auto_initialize' => false,
                 'class'         => 'NwpAssessmentBundle:County',
                'property'      => 'name',
                'required' => false,
                'empty_value' => 'First select a state',
                'query_builder' => function ($er) use ($state) 
                                {
                                    $qb = $er->createQueryBuilder('county');
                                   // if(is_numeric($state)){
                                        $qb->where('county.state = :state')
                                        ->setParameter('state', $state);
                                   // }
                                    //echo $qb->getDql();
                                    return $qb;
                                }
                )));
         
        };
        
        $refreshPrompt = function ($form, $project) use ($factory) {
      
       $form->add($factory->createNamed('prompt','entity', null, array(
                'auto_initialize' => false,
                 'class'         => 'NwpAssessmentBundle:Prompt',
                'property'      => 'name',
                'required' => false,
                'query_builder' => function ($er) use ($project) 
                                {
                                    $qb = $er->createQueryBuilder('prompt');
                                    $qb->where('prompt.project = :project')
                                        ->setParameter('project', $project);
                                    return $qb;
                                }
                )));
         
        };
    
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($refreshCounty,$refreshPrompt){
            
           $form = $event->getForm();
           $data = $event->getData();
           
           if($data == "") {
               $refreshCounty($form, "");
               $refreshPrompt($form, $this->projects[0]->getId()); //default to prompts for first project in the list
           } else if($data == null) {
               $refreshCounty($form, null); //As of beta2, when a form is created setData(null) is called first
               $refreshPrompt($form, null);
           } else {
                if ($data->getProject()) {
                    $refreshPrompt($form, $data->getProject()->getId());
                }
                if ($data->getState()) {
                    //if($data instanceof State) {
                        $refreshCounty($form, $data->getState()->getId());
                    // } 
                } else {
                    $refreshCounty($form, ""); 
            
                }
            }
        });
        
        $builder->addEventListener(FormEvents::PRE_BIND, function (FormEvent $event) use ($refreshCounty,$refreshPrompt) {
            
            $form = $event->getForm();
            $data = $event->getData();
 
            if(array_key_exists('state', $data)) {        
                $refreshCounty($form, $data['state']);
            }
            if(array_key_exists('project', $data)) {        
                $refreshPrompt($form, $data['project']);
            }
        });
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Nwp\AssessmentBundle\Entity\ScoringItem'
        ));
    }

    public function getName()
    {
        return 'nwp_assessmentbundle_scoringitemtype';
    }
    
}
