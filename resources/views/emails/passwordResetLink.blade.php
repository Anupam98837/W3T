<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset</title>
</head>
<body style="font-family: Arial, sans-serif; color: #222; line-height: 1.6;">
    <h2 style="margin-bottom: 8px;">Password Reset Request</h2>

    <p>We received a request to reset your password.</p>

    <p>
        Click the button below to reset your password:
    </p>

    <p>
        <a href="{{ $resetLink }}" style="display:inline-block;padding:10px 14px;background:#9E363A;color:#fff;text-decoration:none;border-radius:6px;">
            Reset Password
        </a>
    </p>

    <p style="word-break: break-all;">
        If the button doesn’t work, copy and paste this link into your browser:<br>
        <a href="{{ $resetLink }}">{{ $resetLink }}</a>
    </p>

    <p>If you didn’t request this, you can safely ignore this email.</p>
</body>
</html>