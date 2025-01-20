<?php

class EmailValidator {
    public function validateEmail($email) {
        // Basic email format validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Check email domain has valid MX record
        $domain = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($domain, "MX")) {
            throw new Exception("Invalid email domain");
        }

        return true;
    }
}
