<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Repository\VilleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
                'choice_value' => 'id',
                'query_builder' => function(VilleRepository $v) {
                    return $v->createQueryBuilder('v')
                        ->orderBy('v.nom', 'asc');
                },
                'required' => true)
            )
            ->add('enregistrer', SubmitType::class, array(
                'label' => 'Enregistrer')
            )
            ->add('annuler', ButtonType::class, array(
                'label' => 'Annuler',
                'attr' => array(
                    'onclick' => 'window.close()')
                )
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
            'allow_extra_fields' => true,
            'translation_domain' => false
        ]);
    }
}
