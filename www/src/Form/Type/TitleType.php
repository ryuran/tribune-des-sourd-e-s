<?php
//http://symfony.com/doc/current/reference/forms/types.html
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TitleType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => [],
            'required' => false,
            'mapped' => false
        ]);
    }

    public function getParent()
    {
        return TextType::class;
    }
}
