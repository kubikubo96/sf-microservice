<?php

namespace App\Rules;

use App\Library\CGlobal;
use Illuminate\Contracts\Validation\Rule;

class NotFoundObjectActionRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */

    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $check = true;
        if (empty($value) || !in_array($value, CGlobal::$listObjectAction, true)) {
            $check = false;
        }
        return $check;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "NOT_FOUND_OBJECT_ACTION";
    }
}
