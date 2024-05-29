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
use App\Entity\HospitalStay;
use App\Entity\Patient;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HospitalStayType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('patient', HiddenType::class, [
            'property_path' => 'patient.id',
            'data' => $options['patientId'],
        ]);
        $builder->add('startDate', DateType::class);
        $builder->add('endDate', DateType::class);
        $builder->add('reason', TextType::class, ['label' => 'Motif']);
        $builder->add('medicalSpeciality', TextType::class, ['label' => 'Spécialité']);
        $builder->add('doctor', ChoiceType::class, [
            'choices' => $options['doctors'],
            'choice_label' => 'fullName',
            'choice_value' => 'id',
        ]);

        $builder->add('submit', SubmitType::class, ['label' => 'Envoyer']);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HospitalStay::class,
            'patientId' => 0,
            'doctors' => [],
            'empty_data' => new HospitalStay(patient: new Patient(), doctor: new Doctor()),
        ]);

        $resolver->setAllowedTypes('patientId', 'int');
    }
}
