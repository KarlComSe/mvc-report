<?php

namespace App\Model;

use Exception;

class GameFormValidator
{
    private const VALID_FORM_NAMES = ['action', 'bet'];

    /**
     * Validates the form data.
     *
     * @param array<string, string> $formData The form data to validate.
     *
     * @throws Exception If an invalid form name is found.
     */
    public function isValidForm(array $formData): void
    {
        $formKeys = array_keys($formData);
        foreach ($formKeys as $key) {
            if (!in_array($key, self::VALID_FORM_NAMES)) {
                throw new Exception('Invalid form name.');
            }

            // ignorantly accepting all values...
        }
    }
}
