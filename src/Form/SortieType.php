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

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => true,
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'required' => true,
                'mapped' => false,
            ])
            ->add('latitude', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('longitude', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('nomLieu', TextType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un lieu',]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Le lieu doit faire au minimum {{ limit }} caractères.',
                        'maxMessage' => 'Le lieu doit faire au minimum {{ limit }} caractères.',
                        'max' => 30,

                    ])
                ],
            ])
            ->add('rue', TextType::class, [
                'mapped' => false,
            ])
            ->add('nomVille', TextType::class, [
                'mapped' => false,
            ])
            ->add('codePostal', TextType::class, [
                'mapped' => false,
            ])

            ->add('dateHeureDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (en minutes)',
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

            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'required' => true,

            ])

            ->add('dateDebutInscription', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Date de début d\'inscription',
            ])
            ->add('inscriptionOption', ChoiceType::class, [
                'choices'  => [
                    'Maintenant' => 'now',
                    'Plus tard' => 'later',
                ],
                'expanded' => true,
                'multiple' => false,
                'mapped' => false,
                'label' => 'Ouverture des inscriptions',
                'data' => 'now',

            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Créer'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
