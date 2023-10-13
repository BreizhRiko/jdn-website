<?php

namespace App\Form;

use App\Entity\Menu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class MenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'Nom'
            ])
            ->add('price', null, [
                'label' => 'Prix'
            ])
            ->add('starter', null, [
                'label' => 'Entrée'
            ])
            ->add('dish', null, [
                'label' => 'Plat'
            ])
            ->add('dessert', null, [
                'label' => 'Dessert'
            ])
            ->add('image', FileType::class, [
                'label' => 'Image de fond (optionnel)',
                'mapped' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
        ]);
    }
}
