<?php

declare(strict_types=1);

namespace App\Validation;

use App\Domain\Category;
use App\Domain\Contact;
use App\Exception\ValidationException;

final class ContactValidator
{
    /**
     * Turns untrusted input ($_POST) into a valid Contact, or throws.
     *
     * @param array<string, mixed> $input
     * @throws ValidationException
     */
    public function validate(array $input, ?int $id = null): Contact
    {
        $name = trim((string) ($input['name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $phone = trim((string) ($input['phone'] ?? ''));
        $category = Category::tryFrom((string) ($input['category'] ?? ''));

        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Name is required.';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'Name must be at most 100 characters.';
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if ($phone !== '' && preg_match('/^\+?[0-9 ().-]{6,20}$/', $phone) !== 1) {
            $errors['phone'] = 'Phone may contain digits, spaces, + ( ) . - only.';
        }

        if ($category === null) {
            $errors['category'] = 'Please choose a valid category.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        /** @var Category $category */
        return new Contact($name, $email, $phone === '' ? null : $phone, $category, $id);
    }
}
