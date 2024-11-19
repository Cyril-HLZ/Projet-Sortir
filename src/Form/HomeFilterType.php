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
                'class' => Campus::class,
                'choice_label' => 'nom',
                'placeholder' => 'Tous les campus',
                'required' => false
            ])
            ->add('search', SearchType::class, [
                'label' => 'Le nom de la sortie contient : ',
                'required' => false
            ])
            ->add('dateMin', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Entre'
            ])
            ->add('dateMax', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'et'
            ])
            ->add('isOrganizer', CheckboxType::class, [
                'label' => 'Sorties dont je suis l\'organisateur/trice',
                'required' => false
            ])
            ->add('isRegistered', CheckboxType::class, [
                'label' => 'Sorties auxquelles je suis inscrit/e',
                'required' => false
            ])
            ->add('isNotRegistered', CheckboxType::class, [
                'label' => 'Sorties auxquelles je ne suis pas inscrit/e',
                'required' => false
            ])
            ->add('isPast', CheckboxType::class, [
                'label' => 'Sorties passées',
                'required' => false
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