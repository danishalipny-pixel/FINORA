<?php
/**
 * FINORA - Validator Interface
 *
 * Contract for all domain-specific validation services.
 *
 * @package Finora\Validators
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace App\Validators;

interface ValidatorInterface
{
    /**
     * Validate an input payload according to domain rules.
     *
     * @param array<string, mixed> $data
     * @return bool True if validation passes, false otherwise
     */
    public function validate(array $data): bool;

    /**
     * Get all validation error messages indexed by field name.
     *
     * @return array<string, string|array<string>>
     */
    public function errors(): array;

    /**
     * Get the first validation error message.
     */
    public function firstError(): ?string;
}
