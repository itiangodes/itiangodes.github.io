<?php
// includes/email_config.php

function sendEmailWithSMTP($to, $subject, $message, $fullname, $status, $role) {
    // For development - just log the email
    error_log("EMAIL TO: $to - SUBJECT: $subject - STATUS: $status - ROLE: $role");
    
    // In production, you would use PHPMailer or similar
    // For now, return true to simulate success
    return true;
}

function sendStatusEmail($email, $fullname, $status, $role) {
    if ($status === 'approved') {
        $subject = "🎉 Welcome to Basketball League - Account Approved!";
        $message = "Hello {$fullname},\n\nYour {$role} account has been APPROVED!\n\nYou can now login to the system.\n\nThank you!";
    } else {
        $subject = "Basketball League - Account Status Update";
        $message = "Hello {$fullname},\n\nYour {$role} account registration has been REJECTED.\n\nPlease contact support for more information.\n\nThank you!";
    }
    
    return sendEmailWithSMTP($email, $subject, $message, $fullname, $status, $role);
}
?>