<?php
function getRejectionTemplate($name, $type, $reason) {
    $template = <<<EOT
Subject: Update Regarding Your LifeLink Registration

Dear {$name},

We have reviewed your registration application for LifeLink. Unfortunately, we are unable to approve your registration at this time due to the following reason(s):

{$reason}

You may address these concerns and submit a new registration application.

If you believe this decision was made in error or need clarification, please contact our support team.

Regards,
LifeLink Admin Team
EOT;
    return $template;
}
?>
