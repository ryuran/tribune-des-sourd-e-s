<?php
//http://symfony.com/doc/current/reference/forms/types.html
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class LinkType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['class' => 'link'],
            'required' => false,
            'mapped' => false
        ]);
    }

    public function getParent()
    {
        return TextType::class;
    }
}
