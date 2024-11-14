<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\User;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function __construct(private Security $security)
    {}
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('site', EntityType::class, [
                'label'=> 'Site : ',
                'class' => Site::class,
                'choice_label'=>'nom',
                'placeholder' => '-- Veuillez sélectionner un campus de rattachement --',
                'expanded' => false,
                'required' => false,
                'query_builder'=>function (SiteRepository $siteRepository) {
                    return $siteRepository->createQueryBuilder('site')
                        ->orderBy('site.nom', 'ASC');
                },
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez choisir un campus de rattachement valide.',
                    ]),
                ],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom (*) :',
                'required' => false,
                'attr' => [
                    'placeholder'=>'GUEVARRA'
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom (*) :',
                'required' => false,
                'attr' => [
                    'placeholder'=>'Che'
                ]
            ])
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudonyme (*) :',
                'required' => false,
                'attr' => [
                    'placeholder'=>'RedDeath'
                ]
            ])
            ->add('photoProfilFichier', FileType::class, [
                'mapped'=>false,
                'label'=> 'Photo de profil : ',
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize'=>'1024k',
                        'maxSizeMessage' => 'Votre image est trop lourde (maximum : 1mo).',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Ce format d\'image n\'est pas pris en charge par cette application.',
                    ]),
                ]
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Numéro de téléphone :',
                'required' => false,
                'attr' => [
                    'placeholder'=>'06XXXXXXXX'
                ]
            ])
            ->add('email', TextType::class, [
                'label' => 'E-mail (*) :',
                'required' => false,
                'attr' => [
                    'placeholder'=>'che.guevarra@free.fr',
                    'autocomplete' => 'off'
                ]
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe (*) :',
                'mapped' => false,
                'required' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit faire au minimum {{ limit }} caractères.',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('confirmPlainPassword', PasswordType::class, [
                'label' => 'Vérifier le mot de passe (*) :',
                'mapped' => false,
                'required' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez confirmer votre mot de passe',
                    ]),
                ],
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $plainPassword = $form->get('plainPassword')->getData();
                $confirmPlainPassword = $form->get('confirmPlainPassword')->getData();

                if ($plainPassword !== $confirmPlainPassword) {
                    // Ajoute une erreur de validation au champ de confirmation
                    $form->get('confirmPlainPassword')->addError(new FormError('Les mots de passe ne correspondent pas.'));
                }
            });
            if ($this->security->isGranted('ROLE_ADMIN')) {
                $builder
                    ->add('administrateur', CheckboxType::class, [
                        'label' => 'Statut Administrateur : ',
                        'data' => false,
                        'required'=> false,
                    ])
                    ->add('actif', CheckboxType::class, [
                        'label' => 'Compte actif : ',
                        'data' => true,
                        'required'=> false,
                    ]);
            }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
