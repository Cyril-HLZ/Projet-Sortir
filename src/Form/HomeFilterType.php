<?php

namespace App\Form;

use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomeFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, [
                'label' => 'Campus : ',
                'class' => Campus::class,
                'choice_label' => 'nom',
                'placeholder' => 'Tous les campus',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ]
            ])
            ->add('search', SearchType::class, [
                'label' => 'Le nom de la sortie contient : ',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Rechercher...'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ]
            ])
            ->add('dateMin', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Entre le :',
                'attr' => [
                    'class' => 'form-control'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ]
            ])
            ->add('dateMax', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Et le : ',
                'attr' => [
                    'class' => 'form-control'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ]
            ])
            ->add('isOrganizer', CheckboxType::class, [
                'label' => 'Sorties dont je suis l\'organisateur/trice',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                    'style' => 'cursor: pointer;'
                ],
                'label_attr' => [
                    'class' => 'form-check-label custom-control-label',
                    'style' => 'cursor: pointer;'
                ],
                'row_attr' => [
                    'class' => 'form-check custom-checkbox'
                ]
            ])
            ->add('isRegistered', CheckboxType::class, [
                'label' => 'Sorties auxquelles je suis inscrit/e',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                    'style' => 'cursor: pointer;'
                ],
                'label_attr' => [
                    'class' => 'form-check-label custom-control-label',
                    'style' => 'cursor: pointer;'
                ],
                'row_attr' => [
                    'class' => 'form-check custom-checkbox'
                ]
            ])
            ->add('isNotRegistered', CheckboxType::class, [
                'label' => 'Sorties auxquelles je ne suis pas inscrit/e',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                    'style' => 'cursor: pointer;'
                ],
                'label_attr' => [
                    'class' => 'form-check-label custom-control-label',
                    'style' => 'cursor: pointer;'
                ],
                'row_attr' => [
                    'class' => 'form-check custom-checkbox'
                ]
            ])
            ->add('isPast', CheckboxType::class, [
                'label' => 'Sorties passées',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                    'style' => 'cursor: pointer;'
                ],
                'label_attr' => [
                    'class' => 'form-check-label custom-control-label',
                    'style' => 'cursor: pointer;'
                ],
                'row_attr' => [
                    'class' => 'form-check custom-checkbox'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }
}