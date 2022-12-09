<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, array(
                'label' => 'Nom',
                'required' => true)
            )
            ->add('rue', TextType::class, array(
                'label' => 'Rue',
                'required' => true)
            )
            ->add('latitude', TextType::class, array(
                'label' => 'Latitude',
                'required' => true)
            )
            ->add('longitude', TextType::class, array(
                'label' => 'Longitude',
                'required' => true)
            )
            ->add('ville', EntityType::class, array(
                'class' => Ville::class,
                'label' => 'Ville',
                'choice_label' => 'nom',
                'mapped' => false,
                'required' => true)
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
            'allow_extra_fields' => true
        ]);
    }
}
