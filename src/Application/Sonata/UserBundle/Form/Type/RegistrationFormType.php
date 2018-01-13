<?php

namespace Application\Sonata\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationFormType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        // add your custom field
        $builder->add('firstname', null, array('label' =>'First Name*','required' => true));
        $builder->add('lastname', null, array('label' =>'Last Name*','required' => true));
    }

    public function getName()
    {
        return 'nwp_user_registration';
    }
}
?>
