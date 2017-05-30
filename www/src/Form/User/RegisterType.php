<?php
//http://symfony.com/doc/current/book/forms.html

namespace App\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Form\Type\TitleType;
use App\Form\Type\LinkType;
use App\Form\Type\SpamType;
use App\Entity\User;

class RegisterType extends AbstractType
{
    const TOKEN = 'user_register';
    const NAME = 'register';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'validation_groups' => ['Register'],
            ])
            ->add('username', TextType::class, [
                'attr' => ['class' => 'form-control', 'maxlength' => 16],
                'validation_groups' => ['Register'],
            ])
            ->add('plainPassword', PasswordType::class, [
                'attr' => ['class' => 'form-control', 'autocomplete' => 'off'],
                'validation_groups' => ['Register'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Register',
                'attr' => ['class' => 'btn btn-success pull-right', 'icon' => 'fa fa-check']
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'POST',
            'data_class' => User::class,
            'validation_groups' => ['Register'],
            'csrf_token_id' => self::TOKEN,
            'action' => '#'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
