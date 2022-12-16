<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Filter;
use App\Repository\CampusRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, array(
                    'class' => Campus::class,
                    'required' => false,
                    'query_builder' => function(CampusRepository $c) {
                        return $c->createQueryBuilder('c')
                            ->orderBy('c.nom', 'asc');
                    },
                    'label' => 'Campus : ',
                    'choice_label' => 'nom')
            )
            ->add('nom', SearchType::class, array(
                    'label' => 'Nom de la sortie : ',
                    'required' => false)
            )
            ->add('dateStart', DateType::class, array(
                    'label' => 'Entre ',
                    'widget' => 'single_text',
                    'required' => false)
            )
            ->add('dateEnd', DateType::class, array(
                    'label' => ' et ',
                    'widget' => 'single_text',
                    'required' => false)
            )
            ->add('organisateur', CheckboxType::class, array(
                'label' => 'Sortie dont je suis l\'organisateur/trice',
                'required' => false)
            )
            ->add('inscrit', CheckboxType::class, array(
                'label' => 'Sortie auxquelles je suis inscrit/e',
                'required' => false)
            )
            ->add('nonInscrit', CheckboxType::class, array(
                'label' => 'Sortie auxquelles je ne suis pas inscrit/e',
                'required' => false)
            )
            ->add('passees', CheckboxType::class, array(
                'label' => 'Sortie passées',
                'required' => false)
            )
            ->add('search', SubmitType::class, array(
                'label' => 'Recherche')
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Filter::class,
            'allow_extra_fields' => true,
            'translation_domain' => false
        ]);
    }
}
