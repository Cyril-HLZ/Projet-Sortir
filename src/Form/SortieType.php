<?php

namespace App\Form;

use App\Entity\Sortie;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie : ',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('InfosSortie', TextareaType::class, [
                'label' => 'Description et infos : ',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date et heure de début : ',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'datetime',]
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'label' => 'Date limite d’inscription : ',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre de places : ',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée : ',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
