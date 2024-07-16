<?php

namespace App\Tests\Unit;

use DateTime;
use App\Entity\Doctor;
use App\Entity\HospitalStay;
use App\Entity\Patient;
use App\Form\Type\HospitalStayType;
use Symfony\Component\Form\Test\TypeTestCase;

class HospitalStayTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        // Arrange
        $doctorId = 11;

        $hospitalStay = new HospitalStay();
        // on a besoin d'un hospital construit, car on le passe au form.
        // on peut mettre n'importe quelle données à ce moment dans le Patient
        $hospitalStay->patient = new Patient();
        $hospitalStay->doctor = new Doctor($doctorId);
        $data = [
            'reason' => 'le motif',
            'medicalSpeciality' => 'la spé',
            'patient' => 5,
            'startDate' => '2024-10-05',
            'doctor' => $doctorId
        ];

        // $objet $hospitalStay est modifié par référence
        $form = $this->factory->create(HospitalStayType::class, $hospitalStay, ['patientId' => 7]);

        $expectedHospitalStay = new HospitalStay(
            startDate: new DateTime('2024-10-05'),
            patient: new Patient(5),
            doctor: new Doctor($doctorId),
            reason: 'le motif',
            medicalSpeciality: 'la spé'
        );

        // Act
        $form->submit($data);

        // Assert
        // check transformation failures
        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($expectedHospitalStay, $hospitalStay);
    }
}
