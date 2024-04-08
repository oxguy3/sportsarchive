<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class IsDocumentCategory extends Constraint
{
    public string $message = 'The value "{{ string }}" is not a valid document category.';
}
