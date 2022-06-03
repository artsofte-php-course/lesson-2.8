<?php

namespace App\Type;

use App\Entity\Project;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    private $hasAdmin;
    private $id;
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'userId' => null,
            'hasAdmin' => false
        ]);

        $resolver->setAllowedTypes('userId', 'int');
        $resolver->setAllowedTypes('hasAdmin', 'Bool');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->hasAdmin = $options['hasAdmin'];
        $this->id = $options['userId'];
        $builder
            ->add('name', TextType::class)
            ->add('description', TextareaType::class)
            ->add('dueDate', DateType::class, [
                'years' => range(2022,2023)
            ])
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'query_builder' => function (EntityRepository $er)
                {
                    if($this->hasAdmin)
                    {
                        return $er->createQueryBuilder('t')
                            ->orderBy('t.id', 'ASC');
                    }
                    else
                    {
                        return $er->createQueryBuilder('t')
                            ->where(sprintf('t.author = %d', $this->id))
                            ->orderBy('t.id', 'ASC');
                    }
                },
                'choice_label' => 'name',
            ])
            ->add('save', SubmitType::class)
        ;

    }
}