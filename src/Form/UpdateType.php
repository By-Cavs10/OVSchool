<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\User;
use App\Entity\Ville;
use Faker\Provider\Text;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder




//            ->add('lieu', EntityType::class, [
//                'class' => Lieu::class,
//                'required' => true,
//                'choice_label' => 'nom',
//                'label' => 'nom',
//                'multiple' => false,
//                'expanded' => true,
//            ])

//            ->add('ville', EntityType::class, [
//                'class' => Ville::class,
//                'choice_label' => 'nom',
//                'required' => true,
//                'label' => 'Ville',
//                'mapped' => false,
//            ])

            ->add('dateHeureDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée',
                'help' => 'min (minutes)',
                'required' => true,
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('nbInscriptionsMax')
            ->add('infosSortie')
            ->add('urlPhoto', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'URL Photo',
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'maxSizeMessage' => 'Ton Image est trop lourde. Max : 1mo',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpg'
                        ]
                    ])
                ]
            ])
//            ->add('etat', EntityType::class, [
//                'class' => Etat::class,
//                'choice_label' => 'libelle',
//                'required' => true,
//            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'required' => true,
            ])
            ->add('organisateur', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nom',
                'required' => true,
            ])
            ->add('participants', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nom',
                'multiple' => true,
            ])

            ->add('dateDebutInscription', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Date de début d\'inscription',
            ])


            ->add('submit', SubmitType::class, [
                'label' => 'OK'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
