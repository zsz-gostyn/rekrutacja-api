<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use FSevestre\BooleanFormType\Form\Type\BooleanType;

class AcceptedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('accepted', BooleanType::class)
        ;
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
