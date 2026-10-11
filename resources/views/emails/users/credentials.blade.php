<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your sign-in details into {{ config('app.name') }}</title>
</head>
<body style="margin:0; padding:32px 16px; background-color:#f4f4f7; color:#1f2937; font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px; margin:0 auto; background-color:#ffffff; border-radius:12px; overflow:hidden;">
        <tr>
            <td style="padding:32px 36px; background-color:#6d28d9; color:#ffffff;">
                <p style="margin:0 0 8px; font-size:13px; font-weight:bold; letter-spacing:1px; text-transform:uppercase;">{{ config('app.name') }}</p>
                <h1 style="margin:0; font-size:25px; line-height:1.3;">Your sign-in details</h1>
            </td>
        </tr>
        <tr>
            <td style="padding:32px 36px;">
                <p style="margin:0 0 16px;">Hello {{ $fullName }},</p>
                <p style="margin:0 0 24px; line-height:1.6;">Use the credentials below to sign in to your account:</p>
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:24px; border:1px solid #e5e7eb; border-radius:8px;">
                    <tr>
                        <td style="padding:14px 16px; border-bottom:1px solid #e5e7eb;">
                            <strong style="display:block; margin-bottom:5px; color:#6b7280; font-size:12px; text-transform:uppercase;">Username</strong>
                            <span>{{ $email }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 16px;">
                            <strong style="display:block; margin-bottom:5px; color:#6b7280; font-size:12px; text-transform:uppercase;">Password</strong>
                            <span style="font-family:Consolas,monospace;">{{ $password }}</span>
                        </td>
                    </tr>
                </table>
                <p style="margin:0 0 24px; line-height:1.6;">For your security, change your password after signing in.</p>
                <p style="margin:0 0 28px;">
                    <a href="{{ $loginUrl }}" style="display:inline-block; padding:12px 20px; border-radius:7px; background-color:#6d28d9; color:#ffffff; font-weight:bold; text-decoration:none;">Sign in to your account</a>
                </p>
                <p style="margin:0; color:#6b7280; font-size:13px; line-height:1.6;">If you were not expecting this account, please contact the person who created it.</p>
            </td>
        </tr>
    </table>
</body>
</html>
