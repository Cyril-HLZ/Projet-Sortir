<?php
namespace App\Form;

use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'required' => false,
                'placeholder' => 'Tous les campus'
            ])
            ->add('search', TextType::class, [
                'required' => false,
                'label' => 'Recherche',
                'mapped' => false
            ])
            ->add('dateMin', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Date de début'
            ])
            ->add('dateMax', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Date de fin'
            ])
            ->add('isOrganizer', CheckboxType::class, [
                'required' => false,
                'label' => 'Mes sorties'
            ])
            ->add('isRegistered', CheckboxType::class, [
                'required' => false,
                'label' => 'Sorties auxquelles je suis inscrit'
            ])
            ->add('isNotRegistered', CheckboxType::class, [
                'required' => false,
                'label' => 'Sorties auxquelles je ne suis pas inscrit'
            ])
            ->add('isPast', CheckboxType::class, [
                'required' => false,
                'label' => 'Sorties passées'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}