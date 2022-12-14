<?php

namespace App\Form;

use App\Entity\Ville;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VilleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, array(
                    'label' => 'Nom de la ville',
                    'required' => true)
            )
            ->add('codePostal', TextType::class, array(
                    'label' => 'Code postal',
                    'required' => true)
            )
            ->add('enregistrer', SubmitType::class, array(
                    'label' => 'Enregistrer')
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ville::class,
        ]);
    }
}
