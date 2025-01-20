<?php
function getRecipientApprovalTemplate($recipientName, $odmlId, $additionalContent = '') {
    $template = <<<EOT
Subject: LifeLink Registration Approved - Your ODML ID

Dear {$recipientName},

We are pleased to inform you that your registration with LifeLink has been approved.

Your ODML ID: {$odmlId}

Important Guidelines:
- Keep your ODML ID handy for all communications
- Regularly update your medical status
- Stay active on the platform for potential organ matches
- Maintain contact with your healthcare provider

{$additionalContent}

We understand the importance of your journey and are here to support you throughout the process.

For urgent assistance, please contact our support team.

Best wishes,
LifeLink Admin Team
EOT;
    return $template;
}
?>
