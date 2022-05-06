<?php

namespace App\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('project_key', TextType::class, [
                "label" => "Введите slug ключ"
            ])
            ->add('name', TextType::class, [
                "label" => "Название проекта"
            ])
            ->add('save', SubmitType::class)
        ;
    }

}