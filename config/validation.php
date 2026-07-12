<?php
/**
 * Request validation primitives shared by forms and AJAX endpoints.
 */
declare(strict_types=1);

function requestString(array $source, string $key, int $maxLength = 255): string
{
    $value = trim((string)($source[$key] ?? ''));
    return mb_substr($value, 0, $maxLength);
}

function validateRequired(array $input, array $fields): array
{
    $errors = [];
    foreach ($fields as $field => $label) {
        if (requestString($input, $field) === '') {
            $errors[$field] = $label . ' is required.';
        }
    }
    return $errors;
}

function validateEmailAddress(string $email): ?string
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? null : 'Enter a valid email address.';
}
