<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class IsSport extends Constraint
{
    public string $message = 'The value "{{ string }}" is not a valid sport.';
}
