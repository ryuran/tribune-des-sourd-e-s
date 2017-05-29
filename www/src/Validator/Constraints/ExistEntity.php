<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ExistEntity extends Constraint
{
    public $message = "Don't exist.";
    public $fields = [];
    public $value = '';

    public function validatedBy()
    {
        return 'core_validator_exist';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
