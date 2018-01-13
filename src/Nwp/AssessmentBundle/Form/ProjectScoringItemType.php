<?php

namespace Nwp\AssessmentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProjectScoringItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('project', null, array('label' =>'Project*','required' =>'true'))
            #->add('scoringItem')
            ->add('gradeLevel', null, array('label' =>'Grade Level*','required' =>'true'))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Nwp\AssessmentBundle\Entity\ProjectScoringItem'
        ));
    }

    public function getName()
    {
        return 'nwp_assessmentbundle_projectscoringitemtype';
    }
    
}
