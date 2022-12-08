<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
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
        $builder
            ->add('nom', TextType::class, array(
                'label' => 'Nom de la sortie : ',
                'required' => true)
            )
            ->add('dateHeureDebut', DateTimeType::class, array(
                'label' => 'Date et heure de la sortie : ',
                'date_format' => 'ddMMyyyy',
                'required' => true)
            )
            ->add('dateLimiteInscription', DateType::class, array(
                'label' => 'Date limite d\'inscription : ',
                'format' => 'ddMMyyyy',
                'required' => true)
            )
            ->add('nbInscriptionsMax', IntegerType::class, array(
                'label' => 'Nombre de places : ',
                'attr' => array(
                    'min' => 0
                ),
                'required' => true)
            )
            ->add('duree', IntegerType::class, array(
                'label' => 'Durée : ',
                'attr' => array(
                    'min' => 0,
                    'step' => 10
                ),
                'required' => true)
            )
            ->add('infoSortie', TextareaType::class, array(
                'label' => 'Description et infos : ',
                'attr' => array('rows' => 5),
                'required' => false)
            )
            ->add('campus', EntityType::class, array(
                'class' => Campus::class,
                'label' => 'Campus',
                'choice_label' => 'nom',
                'required' => true)
            )
            ->add('ville', EntityType::class, array(
                'class' => Ville::class,
                'label' => 'Ville',
                'choice_label' => 'nom',
                'mapped' => false,
                'required' => true)
            )/*
            ->add('lieu', EntityType::class, array(
                'class' => Lieu::class,
                'label' => 'Lieu : ',
                'choice_label' => 'nom',
                'required' => true)
            )*/
            ->add('ajouterLieu', ButtonType::class, array(
                'label' => '+',
                'attr' => array('class' => 'bi bi-plus')
                )
            )
            ->add('rue', TextType::class, array(
                'label' => 'Rue : ',
                'attr' => array(
                    'readonly' => true
                ),
                'mapped' => false)
            )
            ->add('codePostal', TextType::class, array(
                'label' => 'Code postal : ',
                'attr' => array(
                    'readonly' => true
                ),
                'mapped' => false)
            )
            ->add('latitude', NumberType::class, array(
                'label' => 'Latitude : ',
                'attr' => array(
                    'readonly' => true
                ),
                'mapped' => false)
            )
            ->add('longitude', NumberType::class, array(
                'label' => 'Longitude : ',
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
            ->add('annuler', ButtonType::class, array(
                'label' => 'Annuler',
                'attr' => array(
                    'onclick' => 'history.back()'
                ))
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
