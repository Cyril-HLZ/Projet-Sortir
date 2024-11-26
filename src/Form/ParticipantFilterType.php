<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', SearchType::class, [
                'label' => 'Nom',
                'required' => false,
            ])
            ->add('prenom', SearchType::class, [
                'label' => 'Prénom',
                'required' => false,
            ])
            ->add('dateMin', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Entre',
            ])
            ->add('dateMax', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'et',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // Pas de classe liée, car il s'agit de filtres
            'method' => 'GET',   // Utiliser GET pour conserver les filtres dans l'URL
            'csrf_protection' => false, // Pas nécessaire pour un filtre
        ]);
    }
}
