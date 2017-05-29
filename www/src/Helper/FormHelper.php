<?php

namespace App\Helper;

use Symfony\Component\Form\FormInterface;

abstract class FormHelper
{
    public static function getErrors(FormInterface $form): array
    {
        $errors = [];

        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }

        /** @var FormInterface $child */
        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = self::getErrors($child);
            }
        }

        return $errors;
    }
}
