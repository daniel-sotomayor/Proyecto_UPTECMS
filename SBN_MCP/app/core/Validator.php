<?php declare(strict_types=1);
/**
 * Sistema de Validación Robusto
 * Sistema de Gestión de Bienes Nacionales - MCP
 */

namespace App\Core;

class Validator
{
    private array $data = [];
    private array $rules = [];
    private array $errors = [];
    private array $customMessages = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Establecer reglas de validación
     */
    public function rules(array $rules): self
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * Establecer mensajes personalizados
     */
    public function messages(array $messages): self
    {
        $this->customMessages = $messages;
        return $this;
    }

    /**
     * Ejecutar validación
     */
    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                if (!$this->validateRule($field, $value, $rule)) {
                    break; // Parar en el primer error por campo
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Obtener errores de validación
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Obtener primer error de un campo
     */
    public function error(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    /**
     * Validar una regla específica
     */
    private function validateRule(string $field, $value, string $rule): bool
    {
        // Parsear regla con parámetros
        $ruleParts = explode(':', $rule, 2);
        $ruleName = $ruleParts[0];
        $ruleParams = isset($ruleParts[1]) ? explode(',', $ruleParts[1]) : [];

        switch ($ruleName) {
            case 'required':
                return $this->validateRequired($field, $value);
            
            case 'string':
                return $this->validateString($field, $value);
            
            case 'integer':
                return $this->validateInteger($field, $value);
            
            case 'numeric':
                return $this->validateNumeric($field, $value);
            
            case 'email':
                return $this->validateEmail($field, $value);
            
            case 'min':
                return $this->validateMin($field, $value, (int)$ruleParams[0]);
            
            case 'max':
                return $this->validateMax($field, $value, (int)$ruleParams[0]);
            
            case 'between':
                return $this->validateBetween($field, $value, (int)$ruleParams[0], (int)$ruleParams[1]);
            
            case 'in':
                return $this->validateIn($field, $value, $ruleParams);
            
            case 'regex':
                return $this->validateRegex($field, $value, $ruleParams[0]);
            
            case 'date':
                return $this->validateDate($field, $value, $ruleParams[0] ?? 'Y-m-d');
            
            case 'unique':
                return $this->validateUnique($field, $value, $ruleParams[0], $ruleParams[1] ?? null);
            
            case 'exists':
                return $this->validateExists($field, $value, $ruleParams[0], $ruleParams[1] ?? null);
            
            case 'confirmed':
                return $this->validateConfirmed($field, $value);
            
            case 'file':
                return $this->validateFile($field, $value);
            
            case 'image':
                return $this->validateImage($field, $value);
            
            case 'mimes':
                return $this->validateMimes($field, $value, $ruleParams);
            
            case 'max_size':
                return $this->validateMaxSize($field, $value, (int)$ruleParams[0]);
            
            default:
                return true; // Regla desconocida, pasar
        }
    }

    // Validaciones específicas

    private function validateRequired(string $field, $value): bool
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, 'required', 'El campo :field es requerido');
            return false;
        }
        return true;
    }

    private function validateString(string $field, $value): bool
    {
        if ($value !== null && !is_string($value)) {
            $this->addError($field, 'string', 'El campo :field debe ser texto');
            return false;
        }
        return true;
    }

    private function validateInteger(string $field, $value): bool
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, 'integer', 'El campo :field debe ser un número entero');
            return false;
        }
        return true;
    }

    private function validateNumeric(string $field, $value): bool
    {
        if ($value !== null && !is_numeric($value)) {
            $this->addError($field, 'numeric', 'El campo :field debe ser numérico');
            return false;
        }
        return true;
    }

    private function validateEmail(string $field, $value): bool
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'email', 'El campo :field debe ser un email válido');
            return false;
        }
        return true;
    }

    private function validateMin(string $field, $value, int $min): bool
    {
        if ($value === null) return true;
        
        $length = is_string($value) ? mb_strlen($value) : (is_numeric($value) ? $value : 0);
        
        if ($length < $min) {
            $this->addError($field, 'min', "El campo :field debe tener al menos {$min} caracteres");
            return false;
        }
        return true;
    }

    private function validateMax(string $field, $value, int $max): bool
    {
        if ($value === null) return true;
        
        $length = is_string($value) ? mb_strlen($value) : (is_numeric($value) ? $value : 0);
        
        if ($length > $max) {
            $this->addError($field, 'max', "El campo :field no debe exceder {$max} caracteres");
            return false;
        }
        return true;
    }

    private function validateBetween(string $field, $value, int $min, int $max): bool
    {
        return $this->validateMin($field, $value, $min) && $this->validateMax($field, $value, $max);
    }

    private function validateIn(string $field, $value, array $options): bool
    {
        if ($value !== null && !in_array($value, $options, true)) {
            $this->addError($field, 'in', 'El campo :field debe ser uno de: ' . implode(', ', $options));
            return false;
        }
        return true;
    }

    private function validateRegex(string $field, $value, string $pattern): bool
    {
        if ($value !== null && !preg_match($pattern, $value)) {
            $this->addError($field, 'regex', 'El campo :field tiene un formato inválido');
            return false;
        }
        return true;
    }

    private function validateDate(string $field, $value, string $format): bool
    {
        if ($value === null) return true;
        
        $date = \DateTime::createFromFormat($format, $value);
        if (!$date || $date->format($format) !== $value) {
            $this->addError($field, 'date', "El campo :field debe ser una fecha válida ({$format})");
            return false;
        }
        return true;
    }

    private function validateUnique(string $field, $value, string $table, ?string $column = null): bool
    {
        if ($value === null) return true;

        $column = $column ?: $field;
        $exists = Database::fetchValue(
            "SELECT COUNT(*) FROM {$table} WHERE {$column} = :val",
            ['val' => $value]
        );

        if ($exists > 0) {
            $this->addError($field, 'unique', 'El campo :field ya está en uso');
            return false;
        }
        return true;
    }

    private function validateExists(string $field, $value, string $table, ?string $column = null): bool
    {
        if ($value === null) return true;

        $column = $column ?: $field;
        $exists = Database::fetchValue(
            "SELECT COUNT(*) FROM {$table} WHERE {$column} = :val",
            ['val' => $value]
        );

        if ($exists == 0) {
            $this->addError($field, 'exists', 'El campo :field no existe');
            return false;
        }
        return true;
    }

    private function validateConfirmed(string $field, $value): bool
    {
        $confirmField = $field . '_confirmation';
        $confirmValue = $this->data[$confirmField] ?? null;
        
        if ($value !== $confirmValue) {
            $this->addError($field, 'confirmed', 'El campo :field no coincide con su confirmación');
            return false;
        }
        return true;
    }

    private function validateFile(string $field, $value): bool
    {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            $this->addError($field, 'file', 'Debe seleccionar un archivo válido');
            return false;
        }
        return true;
    }

    private function validateImage(string $field, $value): bool
    {
        if (!$this->validateFile($field, $value)) {
            return false;
        }
        
        $file = $_FILES[$field];
        $imageInfo = getimagesize($file['tmp_name']);
        
        if ($imageInfo === false) {
            $this->addError($field, 'image', 'El archivo debe ser una imagen válida');
            return false;
        }
        return true;
    }

    private function validateMimes(string $field, $value, array $mimes): bool
    {
        if (!isset($_FILES[$field])) return true;
        
        $file = $_FILES[$field];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $mimes, true)) {
            $this->addError($field, 'mimes', 'El archivo debe ser de tipo: ' . implode(', ', $mimes));
            return false;
        }
        return true;
    }

    private function validateMaxSize(string $field, $value, int $maxSize): bool
    {
        if (!isset($_FILES[$field])) return true;
        
        $file = $_FILES[$field];
        if ($file['size'] > $maxSize) {
            $maxSizeMB = round($maxSize / 1024 / 1024, 1);
            $this->addError($field, 'max_size', "El archivo no debe exceder {$maxSizeMB}MB");
            return false;
        }
        return true;
    }

    /**
     * Agregar error de validación
     */
    private function addError(string $field, string $rule, string $message): void
    {
        $customKey = "{$field}.{$rule}";
        $finalMessage = $this->customMessages[$customKey] ?? $message;
        $finalMessage = str_replace(':field', $field, $finalMessage);
        
        $this->errors[$field] = $finalMessage;
    }

    /**
     * Validaciones específicas del dominio
     */
    public static function validateCedula(string $cedula): bool
    {
        return preg_match('/^[VE]\d{7,8}$/', $cedula);
    }

    public static function validateCodigoSudebip(string $codigo): bool
    {
        return preg_match('/^BN-\d{4}-\d{6}$/', $codigo);
    }

    public static function validateNroBienMinisterio(string $nro): bool
    {
        return preg_match('/^\d{6,7}[A-Fa-f0-9]{3}$/', $nro);
    }

    public static function validatePassword(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Mínimo 8 caracteres';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Debe incluir al menos una mayúscula';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Debe incluir al menos una minúscula';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Debe incluir al menos un número';
        }
        if (!preg_match('/[\W_]/', $password)) {
            $errors[] = 'Debe incluir al menos un carácter especial';
        }
        
        return $errors;
    }
}