<?php

namespace Nwp\AssessmentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EventScoringItemStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('scoringRoundNumber')
            ->add('readNumber')
            ->add('timeCreated')
            ->add('eventScoringItem')
            ->add('status')
            ->add('user')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Nwp\AssessmentBundle\Entity\EventScoringItemStatus'
        ));
    }

    public function getName()
    {
        return 'nwp_assessmentbundle_eventscoringitemstatustype';
    }
}
