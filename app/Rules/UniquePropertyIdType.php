<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniquePropertyIdType implements ValidationRule
{
    protected $properties;

    /**
     * Create a new rule instance.
     *
     * @param array $properties The entire properties array from the request
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * Run the validation rule.
     *
     * @param string $attribute The attribute name (e.g., properties.0.id)
     * @param mixed $value The current id value
     * @param Closure $fail The callback to report validation failure
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Extract the index from the attribute (e.g., properties.0.id -> 0)
        preg_match('/properties\.(\d+)\.id/', $attribute, $matches);
        $currentIndex = $matches[1] ?? null;

        if ($currentIndex === null) {
            $fail('The :attribute field has an invalid format.');
            return;
        }

        // Get the current type for this index
        $currentType = $this->properties[$currentIndex]['type'] ?? null;

        if (!$currentType) {
            $fail('The :attribute field is missing a corresponding type.');
            return;
        }

        // Check for duplicates
        foreach ($this->properties as $index => $property) {
            if ($index != $currentIndex && $property['id'] == $value && $property['type'] == $currentType) {
                $fail('The :attribute field has a duplicate id and type combination.');
                return;
            }
        }
    }
}
