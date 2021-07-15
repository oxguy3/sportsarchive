<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsSport extends Constraint
{
    public $message = 'The value "{{ string }}" is not a valid sport.';
}
