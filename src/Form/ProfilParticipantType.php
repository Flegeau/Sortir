<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class ProfilParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options, ): void
    {
        $builder
            ->add('pseudo',TextType::class)
            ->add('email', EmailType::class)
            ->add('plainPassword', RepeatedType::class,
            [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
                'mapped' => false
            ])
            ->add('nom',TextType::class)
            ->add('prenom',TextType::class)
            ->add('telephone',TextType::class)
            ->add('pseudo',TextType::class)
            ->add('campus', EntityType::class,
                [
                    'class' => Campus::class,
                    'choice_label' => function ($campus) {
                        return $campus->getNom();
                    }])
            ->add('photo', FileType::class,[
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Please upload a valid image format',
                    ])
                ]

            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
