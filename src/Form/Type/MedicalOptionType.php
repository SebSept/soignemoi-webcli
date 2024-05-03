<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MedicalOptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('doctorId', HiddenType::class)
            ->add('patientId', HiddenType::class)

            ->add('title', TextType::class)
            ->add('description', TextareaType::class)

            ->add('submit', SubmitType::class)
        ;
    }

    //    public function configureOptions(OptionsResolver $resolver): void
    //    {
    //        $resolver->setDefaults([
    //            'data_class'      => MedicalOpinion::class,
    //            // enable/disable CSRF protection for this form
    //            'csrf_protection' => true,
    //            // the name of the hidden HTML field that stores the token
    //            'csrf_field_name' => '_token',
    //            // an arbitrary string used to generate the value of the token
    //            // using a different string for each form improves its security
    //            'csrf_token_id'   => 'task_item',
    //        ]);
    //    }
}
