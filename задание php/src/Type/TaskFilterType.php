<?php

namespace App\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class TaskFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->add('isCompleted', ChoiceType::class, [
            'choices' => [
                'Да' => true,
                'Нет' => false,
                'Любое' => null
            ],
            'label' => 'Выполнена'
        ])
            ->add('submit', SubmitType::class, [
                'label' => 'Отфильтровать'
            ]);
    }


}