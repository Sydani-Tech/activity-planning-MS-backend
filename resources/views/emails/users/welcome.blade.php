<!DOCTYPE html>
<html>

<head>
    <title>Welcome to the Activity Planning & Monitoring System</title>
</head>

<body
    style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2>Welcome, {{ $user->name }}!</h2>

    <p>An administrator has created a new account for you on the <strong>Niger State Ministry of Health Activity
            Planning & Monitoring System</strong>.</p>

    <p>Below are your generated login credentials:</p>

    <div style="background-color: #f4f6f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <p style="margin: 0 0 10px 0;"><strong>Email:</strong> {{ $user->email }}</p>
        <p style="margin: 0;"><strong>Password:</strong> <code
                style="background-color: #e2e8f0; padding: 3px 6px; border-radius: 4px;">{{ $password }}</code></p>
    </div>

    <p>You can log in to the system using the button below:</p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ env('FRONTEND_URL', 'http://localhost:5173') }}"
            style="background-color: #0d6efd; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">Log
            In to the System</a>
    </div>

    <p style="color: #d9534f; font-size: 0.9em;">
        <strong>Important:</strong> For your security, please navigate to your Profile page and change your temporary
        password immediately upon your first login.
    </p>

    <p>Best regards,<br>The System Administrator</p>
</body>

</html>