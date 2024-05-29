<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Form\Type;

use Override;
use App\Entity\Doctor;
use App\Entity\Patient;
use App\Entity\Prescription;
use App\Entity\PrescriptionItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrescriptionType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('doctor', HiddenType::class, ['property_path' => 'doctor.id'])
            ->add('patient', HiddenType::class, ['property_path' => 'patient.id'])

            ->add(
                'items',
                CollectionType::class,
                [
                    'entry_type' => PrescriptionItemType::class,
                    //                    'data_class' => PrescriptionItem::class,
                    'entry_options' => ['label' => 'Prescription'],
                    'prototype' => true, // pas utile, val par défaut
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'attr' => ['data-index' => '0'], // compteur d'items, initializé en js.
                ])

            ->add('submit', SubmitType::class, ['label' => 'Enregistrer'])
        ;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Prescription::class,
            'empty_data' => new Prescription(
                null,
                new Doctor(),
                new Patient(), [
                    new PrescriptionItem(null, '', ''),
                ]),
        ]);
    }
}
