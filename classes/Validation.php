<?php

class Validation {
    /**
     * Validate email address
     */
    public static function validateEmail($email) {
        if (empty($email)) {
            return ['valid' => false, 'message' => 'Email is required'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Invalid email format'];
        }

        return ['valid' => true];
    }

    /**
     * Validate phone number
     */
    public static function validatePhone($phone) {
        if (empty($phone)) {
            return ['valid' => false, 'message' => 'Phone number is required'];
        }

        // Check if it consists only of exactly 10 digits
        if (!preg_match('/^\d{10}$/', $phone)) {
            return ['valid' => false, 'message' => 'Phone number must be exactly 10 digits with no other characters'];
        }

        return ['valid' => true];
    }

    /**
     * Validate required fields
     */
    public static function validateRequired($value, $fieldName) {
        if (empty(trim($value))) {
            return ['valid' => false, 'message' => ucfirst($fieldName) . ' is required'];
        }
        return ['valid' => true];
    }
}
?>
