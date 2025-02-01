<?php
require_once __DIR__ . '/helpers/mailer.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to test email sending
function testEmailFunctionality() {
    $mailer = new Mailer();
    $results = [];
    $testEmail = "yourlifelink.org@gmail.com";
    
    try {
        // Test 1: Basic Test Email
        echo "Testing basic email functionality...\n";
        echo "Sending to: " . $testEmail . "\n";
        
        $result = $mailer->sendTestEmail($testEmail);
        $results['test_email'] = $result ? "Success" : "Failed";
        echo "Test email result: " . ($result ? "Success" : "Failed") . "\n\n";
        
        // Test 2: Hospital Approval Email
        echo "Testing hospital approval email...\n";
        // Verify template exists
        $hospitalTemplate = __DIR__ . '/../../email_templates/hospital_approval.html';
        echo "Checking hospital template at: " . $hospitalTemplate . "\n";
        if (!file_exists($hospitalTemplate)) {
            echo "WARNING: Hospital template not found!\n";
        } else {
            echo "Hospital template found successfully\n";
        }
        
        $result = $mailer->sendHospitalApproval(
            $testEmail,
            "Test Hospital",
            "HOSP123"
        );
        $results['hospital_approval'] = $result ? "Success" : "Failed";
        echo "Hospital approval email result: " . ($result ? "Success" : "Failed") . "\n\n";

        // Test 2.1: Hospital Rejection Email
        echo "Testing hospital rejection email...\n";
        // Verify template exists
        $hospitalRejTemplate = __DIR__ . '/../../email_templates/hospital_rejection.html';
        echo "Checking hospital rejection template at: " . $hospitalRejTemplate . "\n";
        if (!file_exists($hospitalRejTemplate)) {
            echo "WARNING: Hospital rejection template not found!\n";
        } else {
            echo "Hospital rejection template found successfully\n";
        }
        
        $result = $mailer->sendHospitalRejection(
            $testEmail,
            "Test Hospital",
            "Missing required documentation"
        );
        $results['hospital_rejection'] = $result ? "Success" : "Failed";
        echo "Hospital rejection email result: " . ($result ? "Success" : "Failed") . "\n\n";
        
        // Test 3: Donor Approval Email
        echo "Testing donor approval email...\n";
        // Verify template exists
        $donorTemplate = __DIR__ . '/../../email_templates/donor_approval.html';
        echo "Checking donor template at: " . $donorTemplate . "\n";
        if (!file_exists($donorTemplate)) {
            echo "WARNING: Donor template not found!\n";
        } else {
            echo "Donor template found successfully\n";
        }
        
        $result = $mailer->sendDonorApproval(
            $testEmail,
            "Test Donor",
            "DNR456"
        );
        $results['donor_approval'] = $result ? "Success" : "Failed";
        echo "Donor approval email result: " . ($result ? "Success" : "Failed") . "\n\n";

        // Test 3.1: Donor Rejection Email
        echo "Testing donor rejection email...\n";
        // Verify template exists
        $donorRejTemplate = __DIR__ . '/../../email_templates/donor_rejection.html';
        echo "Checking donor rejection template at: " . $donorRejTemplate . "\n";
        if (!file_exists($donorRejTemplate)) {
            echo "WARNING: Donor rejection template not found!\n";
        } else {
            echo "Donor rejection template found successfully\n";
        }
        
        $result = $mailer->sendDonorRejection(
            $testEmail,
            "Test Donor",
            "Incomplete medical history"
        );
        $results['donor_rejection'] = $result ? "Success" : "Failed";
        echo "Donor rejection email result: " . ($result ? "Success" : "Failed") . "\n\n";

        // Test 4: Recipient Approval Email
        echo "Testing recipient approval email...\n";
        // Verify template exists
        $recipientTemplate = __DIR__ . '/../../email_templates/recipient_approval.html';
        echo "Checking recipient template at: " . $recipientTemplate . "\n";
        if (!file_exists($recipientTemplate)) {
            echo "WARNING: Recipient template not found!\n";
        } else {
            echo "Recipient template found successfully\n";
        }
        
        $result = $mailer->sendRecipientApproval(
            $testEmail,
            "Test Recipient",
            "RCP789"
        );
        $results['recipient_approval'] = $result ? "Success" : "Failed";
        echo "Recipient approval email result: " . ($result ? "Success" : "Failed") . "\n\n";

        // Test 5: Recipient Rejection Email
        echo "Testing recipient rejection email...\n";
        // Verify template exists
        $recipientRejTemplate = __DIR__ . '/../../email_templates/recipient_rejection.html';
        echo "Checking recipient rejection template at: " . $recipientRejTemplate . "\n";
        if (!file_exists($recipientRejTemplate)) {
            echo "WARNING: Recipient rejection template not found!\n";
        } else {
            echo "Recipient rejection template found successfully\n";
        }
        
        $result = $mailer->sendRecipientRejection(
            $testEmail,
            "Test Recipient",
            "Application incomplete"
        );
        $results['recipient_rejection'] = $result ? "Success" : "Failed";
        echo "Recipient rejection email result: " . ($result ? "Success" : "Failed") . "\n\n";

        // Test 6: ODML ID Update Emails for all types
        echo "Testing ODML ID update emails for all types...\n";
        
        // Hospital ODML Update
        $result = $mailer->sendODMLUpdateEmail(
            $testEmail,
            "Test Hospital",
            "hospital",
            "HOSP789"
        );
        $results['hospital_odml_update'] = $result ? "Success" : "Failed";
        echo "Hospital ODML ID update email result: " . ($result ? "Success" : "Failed") . "\n";

        // Donor ODML Update
        $result = $mailer->sendODMLUpdateEmail(
            $testEmail,
            "Test Donor",
            "donor",
            "DNR789"
        );
        $results['donor_odml_update'] = $result ? "Success" : "Failed";
        echo "Donor ODML ID update email result: " . ($result ? "Success" : "Failed") . "\n";

        // Recipient ODML Update
        $result = $mailer->sendODMLUpdateEmail(
            $testEmail,
            "Test Recipient",
            "recipient",
            "RCP789"
        );
        $results['recipient_odml_update'] = $result ? "Success" : "Failed";
        echo "Recipient ODML ID update email result: " . ($result ? "Success" : "Failed") . "\n\n";
        
        echo "\nFinal Test Results:\n";
        print_r($results);
        
    } catch (Exception $e) {
        echo "\nERROR DETAILS:\n";
        echo "Error Message: " . $e->getMessage() . "\n";
        echo "Error File: " . $e->getFile() . "\n";
        echo "Error Line: " . $e->getLine() . "\n";
        echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
    }
}

// Run the test
header('Content-Type: text/plain');
testEmailFunctionality();
