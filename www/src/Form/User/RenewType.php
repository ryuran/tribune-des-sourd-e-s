<?php
//http://symfony.com/doc/current/book/forms.html

namespace App\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Form\Type\TitleType;
use App\Entity\User;

class RenewType extends AbstractType
{
    const TOKEN = 'user_reset';
    const NAME = 'renew';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'required' => true,
                    'first_options' => array(
                        'label' => 'Password',
                        'attr' => ['class' => 'form-control']
                    ),
                    'second_options' => array(
                        'label' => 'Repeat password',
                        'attr' => ['class' => 'form-control']
                    ),
                    'invalid_message' => 'The password fields must match.',
                    'validation_groups' => array('Reset')
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Update',
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
            'validation_groups' => ['Reset'],
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
