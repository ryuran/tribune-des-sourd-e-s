<?php
namespace App\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ExistEntityValidator extends ConstraintValidator
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getRepositoryName($object)
    {
        $class = get_class($object);
        $repository = str_replace(['\\Entity\\', '\\'], [':', ''], $class);

        return $repository;
    }

    public function validate($object, Constraint $constraint_base)
    {
        /** @var ExistEntity $constraint */
        $constraint = $constraint_base;

        $repository = $this->entityManager->getRepository($this->getRepositoryName($object))->createQueryBuilder('q');
        $getter = 'get' . ucfirst($constraint->value);

        foreach ($constraint->fields as $field) {
            $repository->orWhere('q.' . $field . ' = :' . $field)->setParameter($field, $object->$getter());
        }

        $entity = $repository->getQuery()->getOneOrNullResult();

        if (!$entity) {
            $this->context->buildViolation($constraint->message)
                //->setParameter('%string%', $value)
                ->addViolation();
        }
    }
}
