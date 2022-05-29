<?php

namespace App\Type;

use App\Entity\Project;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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

            ->add('project', EntityType::class, [
                'class' => Project::class,
                'choice_label' => 'name',
                'required' => false,
                'label' => 'Проект',
                'placeholder' => 'Любой',
            ])

            ->add('author', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'required' => false,
                'label' => 'Автор',
                'placeholder' => 'Любой',
            ])

            ->add('dueDate', ChoiceType::class, [
                'choices' => [
                    'Новые' => true,
                    'Старые' => false,
                    'Любое' => null
                ],
                'label' => 'По дате выполнения'
            ])

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