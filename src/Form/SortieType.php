<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Sortie;
use App\Entity\Ville;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dateJour = new DateTime();
        $builder
            ->add('nom', TextType::class, array(
                'label' => 'Nom de la sortie',
                'required' => true)
            )
            ->add('dateHeureDebut', DateTimeType::class, array(
                'label' => 'Date et heure de la sortie',
                'widget' => 'single_text',
                'attr' => array('min' => $dateJour->format('Y-m-d H:i')),
                'with_seconds' => false,
                'required' => true)
            )
            ->add('dateLimiteInscription', DateType::class, array(
                'label' => 'Date limite d\'inscription',
                'widget' => 'single_text',
                'attr' => array('min' => $dateJour->format('Y-m-d')),
                'required' => true)
            )
            ->add('nbInscriptionsMax', IntegerType::class, array(
                'label' => 'Nombre de places',
                'attr' => array(
                    'min' => 0
                ),
                'required' => true)
            )
            ->add('duree', IntegerType::class, array(
                'label' => 'Durée',
                'attr' => array(
                    'min' => 0,
                    'step' => 5,
                    'placeholder' => 'en minutes'
                ),
                'required' => true)
            )
            ->add('infoSortie', TextareaType::class, array(
                'label' => 'Description et infos',
                'attr' => array('rows' => 5),
                'required' => false)
            )
            ->add('campus', EntityType::class, array(
                'class' => Campus::class,
                'label' => 'Campus',
                'choice_label' => 'nom',
                'choice_value' => 'id',
                'required' => true)
            )
            ->add('ville', EntityType::class, array(
                'class' => Ville::class,
                'label' => 'Ville',
                'choice_label' => 'nom',
                'choice_value' => 'id',
                'mapped' => false,
                'required' => false)
            )
            ->add('rue', TextType::class, array(
                'label' => 'Rue',
                'attr' => array(
                    'readonly' => true
                ),
                'mapped' => false)
            )
            ->add('latitude', NumberType::class, array(
                'label' => 'Latitude',
                'attr' => array(
                    'readonly' => true
                ),
                'mapped' => false)
            )
            ->add('longitude', NumberType::class, array(
                'label' => 'Longitude',
                'attr' => array(
                    'readonly' => true
                ),
                'mapped' => false)
            )
            ->add('enregistrer', SubmitType::class, array(
                'label' => 'Enregistrer')
            )
            ->add('publier', SubmitType::class, array(
                'label' => 'Publier la sortie')
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'allow_extra_fields' => true
        ]);
    }
}
