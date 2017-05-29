<?php
//http://symfony.com/doc/current/book/forms.html

namespace App\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Form\Type\TitleType;
use App\Form\Type\LinkType;
use App\Entity\User;

class ForgetType extends AbstractType
{
    const TOKEN = 'user_forget';
    const NAME = 'forget';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('header', TitleType::class, [
                'label' => 'Forgot password',
                'attr' => ['class' => 'h3 text-center']
            ])
            ->add('username', TextType::class, [
                'label' => 'Username or email',
                'attr' => ['class' => 'form-control'],
                'validation_groups' => array('Forget'),
            ])
            ->add('link', LinkType::class, [
                'label' => 'Connection',
                'attr' => [
                    'class' => 'btn btn-link pull-left',
                    'route_name' => 'user_login',
                    'icon' => 'fa fa-arrow-right'
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Send',
                'attr' => ['class' => 'btn btn-success pull-right', 'icon' => 'fa fa-check']
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => ['Forget'],
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
