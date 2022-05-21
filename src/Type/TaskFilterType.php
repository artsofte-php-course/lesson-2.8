<?php

namespace App\Type;

use Doctrine\ORM\EntityRepository;
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
            ->add('isCompleted', ChoiceType::class, [
            'choices' => [
                'Да' => true,
                'Нет' => false,
                'Любое' => null
            ],
            'label' => 'Выполнена'
        ])
            ->add('due_date', ChoiceType::class, [
                'choices' => [
                    'Более новые задачи' => true,
                    'Более старые задачи' => false,
                    'Любые' => null
                ],
                'label' => 'По сроку выполнения'
            ])
            ->add('projectId', EntityType::class, [
                'class' => Project::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.id', 'ASC');
                },
                'choice_label' => 'name',
                'required'   => false,
                'label' => 'Выберите проект',
            ])
            ->add('authorId', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.id', 'ASC');
                },
                'choice_label' => 'email',
                'required'   => false,
                'label' => 'Выберите создателя'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Отфильтровать'
            ]);
    }


}