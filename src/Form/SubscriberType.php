<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Entity\School;

class SubscriberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first_name', TextType::class)
            ->add('surname', TextType::class)
            ->add('email', EmailType::class)
            ->add('school', EntityType::class, [
                'class' => School::class,
                'expanded' => false,
                'multiple' => false,
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
