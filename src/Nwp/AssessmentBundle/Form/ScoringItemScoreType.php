<?php

namespace Nwp\AssessmentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ScoringItemScoreType extends AbstractType
{
    
    protected $scoring_scale;

    public function __construct($scoring_scale)
    {
        $this->scoring_scale = $scoring_scale;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           ->add('score', 'choice', array('label'=>'Select a Choice','choices' => $this->scoring_scale ))
           // ->add('eventScoringItemUser')
           // ->add('scoringRubricAttribute')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Nwp\AssessmentBundle\Entity\ScoringItemScore'
        ));
    }

    public function getName()
    {
        return 'nwp_assessmentbundle_scoringitemscoretype';
    }
}
