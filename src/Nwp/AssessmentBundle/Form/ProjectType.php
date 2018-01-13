<?php

namespace Nwp\AssessmentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label' =>'Project Name'))
            ->add('startDate', null, array('label' =>'Start Date'))
            ->add('endDate', null, array('label' =>'End Date'))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Nwp\AssessmentBundle\Entity\Project'
        ));
    }

    public function getName()
    {
        return 'nwp_assessmentbundle_projecttype';
    }
}
