<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', null, [
                'attr' => [
                    'class' => 'form-control-lg',
                    'placeholder' => 'Prénom'
                ],
                'label' => false
            ])
            ->add('lastName', null, [
                'attr' => [
                    'class' => 'form-control-lg',
                    'placeholder' => 'Nom'
                ],
                'label' => false
            ])
            ->add('email', null, [
                'attr' => [
                    'class' => 'form-control-lg',
                    'placeholder' => 'Email'
                ],
                'label' => false
            ])
            ->add('phoneNumber', null, [
                'attr' => [
                    'id' => 'phoneNumber',
                    'class' => 'form-control-lg',
                    'placeholder' => 'Numéro de téléphone'
                ],
                'label' => false
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'attr' => [
                    'class' => 'form-select-lg'
                ],
                'placeholder' => 'Méthode de paiement',
                'choices' => [
                    "CASH" => "CASH"
                ],
                'label' => false
            ])
            ->add('menuReservations', CollectionType::class, [
                'entry_type' => MenuReservationType::class
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'reservation_item'
        ]);
    }
}
