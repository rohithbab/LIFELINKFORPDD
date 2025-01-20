<?php
function getDonorApprovalTemplate($donorName, $odmlId, $additionalContent = '') {
    $template = <<<EOT
Subject: Thank You for Registering as an Organ Donor - ODML ID Confirmation

Dear {$donorName},

Thank you for your noble decision to register as an organ donor with LifeLink. Your registration has been approved.

Your ODML ID: {$odmlId}

Important Points:
- Keep your ODML ID safe for future reference
- Update your medical information regularly
- Inform your family about your decision
- Use our platform to stay updated about potential matches

{$additionalContent}

Your decision to become an organ donor could save multiple lives. We appreciate your commitment to this noble cause.

For any queries, our support team is always here to help.

Warm regards,
LifeLink Admin Team
EOT;
    return $template;
}
?>
