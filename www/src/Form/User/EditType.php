<?php
//http://symfony.com/doc/current/book/forms.html

namespace App\Form\User;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Form\Type\TitleType;
use App\Form\Type\LinkType;
use App\Entity\User;

class EditType extends AbstractType
{
    const TOKEN = 'user_edit';
    const NAME = 'edit';
    private $user;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var TokenStorage $tokenStorage */
        $tokenStorage = $options['token_storage'];

        $this->user = $tokenStorage->getToken() !== null && is_object($tokenStorage->getToken()->getUser()) ?
            $tokenStorage->getToken()->getUser() : null;

        $builder
            ->add('header', TitleType::class, [
                'label' => 'Settings',
                'attr' => ['class' => 'h3 text-center']
            ])
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control',
                    'placeholder' => $this->user !== null ? $this->user->getEmail() : '',
                    'translation_domain' => false
                ],
                'validation_groups' => ['Edit'],
                'required' => false
            ])
            ->add('username', TextType::class, [
                'attr' => ['class' => 'form-control', 'autocomplete' => 'off',
                    'placeholder' => $this->user !== null ? $this->user->getUsername() : '',
                    'translation_domain' => false
                ],
                'validation_groups' => ['Edit'],
                'required' => false
            ])
            ->add('plainPassword', PasswordType::class, [
                'attr' => ['class' => 'form-control', 'autocomplete' => 'off', 'value' => ''],
                'validation_groups' => ['Edit'],
                'required' => false,
            ])
            /*->add('enabledEmails', ChoiceType::class, [
              'label' => 'Receive an email when',
              'attr' => ['class' => 'form-checkbox'],
              'choice_attr' => ['class' => 'form-checkbox'],
              'choices' => User::OPTIONS_EMAIL,
              'expanded' => true,
              'multiple' => true
            ])*/
            ->add('save', SubmitType::class, [
                'label' => 'Update',
                'attr' => ['class' => 'btn btn-success btn-block', 'icon' => 'fa fa-check']
            ])
            ->add('unsubscribeLabel', TitleType::class, [
                'label' => 'Careful! After clicking on "To unsubscribe", '.
                    'all your data will be deleted and can not be recovered',
                'attr' => ['class' => 'alert text-danger', 'icon' => 'fa fa-exclamation-triangle']
            ])
            ->add('unsubscribeLink', LinkType::class, [
                'label' => 'To unsubscribe',
                'attr' => [
                    'class' => 'btn btn-link text-danger',
                    'route_name' => 'user_unsubscribe',
                    'icon' => 'fa fa-exclamation-triangle'
                ]
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => ['Edit'],
            'csrf_token_id' => self::TOKEN,
            'attr' => ['autocomplete' => 'off'],
            'action' => '#'
        ])
            ->setRequired('token_storage');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
