<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ordinal', IntegerType::class)
            ->add('topic', TextType::class)
            ->add('content', TextType::class)
            ->add('creation_date', DateTimeType::class, ['widget' => 'single_text'])
            ->add('active', CheckboxType::class)
        ;
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
