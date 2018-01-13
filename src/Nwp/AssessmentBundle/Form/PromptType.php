<?php

namespace Nwp\AssessmentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PromptType extends AbstractType
{
    protected $projects;

    public function __construct($projects)
    {
        $this->projects = $projects;
       
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('project', 'entity', array('class' => 'NwpAssessmentBundle:Project','label' =>'Project*','required' => true,
                                             'choices' =>$this->projects))  
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Nwp\AssessmentBundle\Entity\Prompt'
        ));
    }

    public function getName()
    {
        return 'nwp_assessmentbundle_prompttype';
    }
}
