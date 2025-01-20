<?php
function getHospitalApprovalTemplate($hospitalName, $odmlId, $additionalContent = '') {
    $template = <<<EOT
Subject: Welcome to LifeLink - Your ODML ID Confirmation

Dear {$hospitalName},

Congratulations! Your registration with LifeLink has been approved. We are pleased to welcome you to our organ donation management platform.

Your ODML ID: {$odmlId}

Important Information:
- Please use this ODML ID for all future communications
- Keep your login credentials secure
- Update your hospital's organ requirements regularly
- Maintain accurate records of all organ donation activities

{$additionalContent}

We trust that you will use our platform responsibly to help save lives through efficient organ donation management.

For any assistance, please contact our support team.

Best regards,
LifeLink Admin Team
EOT;
    return $template;
}
?>
