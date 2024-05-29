<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Form\Type;

use App\Entity\Doctor;
use App\Entity\MedicalOpinion;
use App\Entity\Patient;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MedicalOpinionType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('doctor', HiddenType::class, ['property_path' => 'doctor.id'])
            ->add('patient', HiddenType::class, [
                'property_path' => 'patient.id',
                'data' => $options['patientId'],
            ])

            ->add('title', TextType::class)
            ->add('description', TextareaType::class)

            ->add('submit', SubmitType::class)
        ;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MedicalOpinion::class,
            'empty_data' => new MedicalOpinion(null, '', '', new Doctor(null), new Patient()),
            'patientId' => 0,
        ]);

        $resolver->setAllowedTypes('patientId', 'int');
    }
}
