<?php

namespace App\Type;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskType extends AbstractType
{

    protected $id;

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'userId' => 0,
            'userRole' => []
        ]);

        // you can also define the allowed types, allowed values and
        // any other feature supported by the OptionsResolver component
        $resolver->setAllowedTypes('userId', 'int');
        $resolver->setAllowedTypes('userRole', 'array');
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roles = $options['userRole'];
        $flag = false;
        foreach ($roles as $role)
        {
            if ($role === 'ROLE_ADMIN')
            {
                $flag = True;
            }
        }

        $this->id = $options['userId'];

        if ($flag)
        {
            $builder
                ->add('name', TextType::class)
                ->add('description', TextareaType::class)
                ->add('dueDate', DateType::class, [
                    'years' => range(2022,2023)
                ])
                ->add('project', EntityType::class, [
                    'class' => Project::class,
                    'query_builder' => function (EntityRepository $er) {
                        $sql = sprintf('p.author = %d', $this->id);
                        return $er->createQueryBuilder('p')
                            ->orderBy('p.id', 'ASC');
                    },
                    'choice_label' => 'id',
                ])
                ->add('save', SubmitType::class)
            ;
        }
        else
        {
            $builder
                ->add('name', TextType::class)
                ->add('description', TextareaType::class)
                ->add('dueDate', DateType::class, [
                    'years' => range(2022,2023)
                ])
                ->add('project', EntityType::class, [
                    'class' => Project::class,
                    'query_builder' => function (EntityRepository $er) {
                        $sql = sprintf('p.author = %d', $this->id);
                        return $er->createQueryBuilder('p')
                            ->where($sql)
                            ->orderBy('p.id', 'ASC');
                    },
                    'choice_label' => 'id',
                ])
                ->add('save', SubmitType::class)
            ;
        }



    }
}