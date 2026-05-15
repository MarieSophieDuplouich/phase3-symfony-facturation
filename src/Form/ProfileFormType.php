<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyName', TextType::class, [
                'required' => false,
                'attr'     => ['placeholder' => 'Tech Solutions'],
            ])
            ->add('siret', TextType::class, [
                'required' => false,
                'attr'     => ['placeholder' => '123 456 789 00000'],
            ])
            ->add('iban', TextType::class, [
                'required' => false,
                'attr'     => ['placeholder' => 'FR76 1234 5678 9012 3456 7890 123'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}