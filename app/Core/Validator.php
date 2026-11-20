<?php
/**
 * FINORA - Server-Side Data Validation Engine
 */

declare(strict_types=1);

namespace App\Core;

use PDO;

class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];
    private array $customMessages;

    public function __construct(array $data, array $rules, array $customMessages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $customMessages;
    }

    public static function make(array $data, array $rules, array $customMessages = []): self
    {
        return new self($data, $rules, $customMessages);
    }

    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $rulesList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            $value = $this->data[$field] ?? null;

            foreach ($rulesList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$ruleName, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                } else {
                    $ruleName = $rule;
                }

                // If field is optional and empty, skip further non-required checks
                if ($ruleName !== 'required' && ($value === null || $value === '' || (is_array($value) && empty($value)))) {
                    continue;
                }

                $this->applyRule($field, $ruleName, $value, $params);
            }
        }

        return empty($this->errors);
    }

    public function passes(): bool
    {
        return $this->validate();
    }

    public function fails(): bool
    {
        return !$this->validate();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        if (empty($this->errors)) {
            return null;
        }
        $first = reset($this->errors);
        return is_array($first) ? $first[0] : $first;
    }

    private function applyRule(string $field, string $rule, mixed $value, array $params): void
    {
        $label = ucwords(str_replace(['_', '-'], ' ', $field));

        switch ($rule) {
            case 'required':
                if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                    $this->addError($field, "{$label} is required.");
                }
                break;

            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "Please provide a valid email address.");
                }
                break;

            case 'numeric':
                if (!is_numeric($value)) {
                    $this->addError($field, "{$label} must be a valid number.");
                }
                break;

            case 'positive_number':
                if (!is_numeric($value) || (float)$value <= 0) {
                    $this->addError($field, "{$label} must be greater than zero.");
                }
                break;

            case 'min':
                $min = (int)($params[0] ?? 0);
                if (is_numeric($value)) {
                    if ((float)$value < $min) {
                        $this->addError($field, "{$label} must be at least {$min}.");
                    }
                } elseif (mb_strlen((string)$value) < $min) {
                    $this->addError($field, "{$label} must be at least {$min} characters.");
                }
                break;

            case 'max':
                $max = (int)($params[0] ?? 255);
                if (is_numeric($value)) {
                    if ((float)$value > $max) {
                        $this->addError($field, "{$label} must not exceed {$max}.");
                    }
                } elseif (mb_strlen((string)$value) > $max) {
                    $this->addError($field, "{$label} must not exceed {$max} characters.");
                }
                break;

            case 'matches':
                $otherField = $params[0] ?? '';
                $otherValue = $this->data[$otherField] ?? null;
                if ($value !== $otherValue) {
                    $otherLabel = ucwords(str_replace(['_', '-'], ' ', $otherField));
                    $this->addError($field, "{$label} does not match {$otherLabel}.");
                }
                break;

            case 'in':
                if (!in_array((string)$value, $params, true)) {
                    $this->addError($field, "The selected {$label} is invalid.");
                }
                break;

            case 'date':
                $d = \DateTime::createFromFormat('Y-m-d', (string)$value);
                if (!($d && $d->format('Y-m-d') === (string)$value)) {
                    $this->addError($field, "{$label} must be a valid date (YYYY-MM-DD).");
                }
                break;

            case 'unique':
                // params: table, column, ignore_id (optional)
                $table = $params[0] ?? '';
                $column = $params[1] ?? $field;
                $ignoreId = $params[2] ?? null;

                $pdo = Database::getConnection();
                $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = ?";
                $bindings = [$value];

                if ($ignoreId !== null && $ignoreId !== '') {
                    $sql .= " AND `id` != ?";
                    $bindings[] = $ignoreId;
                }

                $stmt = $pdo->prepare($sql);
                $stmt->execute($bindings);
                if ((int)$stmt->fetchColumn() > 0) {
                    $this->addError($field, "This {$label} is already taken.");
                }
                break;

            case 'exists':
                $table = $params[0] ?? '';
                $column = $params[1] ?? 'id';
                $extraCheck = $params[2] ?? null; // e.g. user_id check

                $pdo = Database::getConnection();
                $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = ?";
                $bindings = [$value];

                if ($extraCheck === 'auth_user') {
                    $sql .= " AND `user_id` = ?";
                    $bindings[] = Auth::id();
                }

                $stmt = $pdo->prepare($sql);
                $stmt->execute($bindings);
                if ((int)$stmt->fetchColumn() === 0) {
                    $this->addError($field, "Selected {$label} does not exist.");
                }
                break;
        }
    }

    private function addError(string $field, string $defaultMessage): void
    {
        $message = $this->customMessages[$field] ?? $defaultMessage;
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
    }
}
