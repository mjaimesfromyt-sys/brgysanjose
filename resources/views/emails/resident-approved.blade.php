<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Account Approved</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            color: #333333;
            margin: 0;
            padding: 24px 12px;
        }
        .email-container {
            max-width: 560px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }
        .email-header {
            background-color: #1b5e20;
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }
        .email-body {
            padding: 30px 26px;
            line-height: 1.6;
            font-size: 14px;
        }
        .service-list {
            background-color: #f8fafc;
            border-left: 4px solid #16a34a;
            border-radius: 6px;
            padding: 14px 18px;
            margin: 18px 0;
        }
        .service-list ul {
            margin: 6px 0 0 0;
            padding-left: 18px;
        }
        .btn-login {
            display: inline-block;
            background-color: #1b5e20;
            color: #ffffff !important;
            padding: 12px 28px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
            margin: 20px 0;
        }
        .email-footer {
            background-color: #f1f5f9;
            padding: 16px 20px;
            font-size: 11.5px;
            color: #64748b;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Official Header -->
        <div class="email-header">
            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9;">Republic of the Philippines</div>
            <div style="font-size: 12px; opacity: 0.95;">Municipality of Talibon &bull; Province of Bohol</div>
            <h2 style="margin: 4px 0 0 0; font-size: 20px; font-weight: bold;">BARANGAY SAN JOSE</h2>
            <div style="font-size: 11px; opacity: 0.85; margin-top: 2px;">Barangay Information &amp; Booking System</div>
        </div>

        <!-- Body Content in Formal English -->
        <div class="email-body">
            <h3 style="color: #1b5e20; margin-top: 0;">Account Registration Approved</h3>

            <p>Dear <strong>{{ $user->name }}</strong>,</p>

            <p>We are pleased to inform you that your resident registration has been <strong>officially reviewed and approved</strong> by the Barangay San Jose Administration.</p>

            <p>Your account is now <strong>fully active and verified</strong>. You may now access all online barangay services, including:</p>

            <div class="service-list">
                <strong style="color: #1b5e20;">Available Online Services:</strong>
                <ul>
                    <li><strong>Document Requests:</strong> Apply online for Barangay Clearance, Certificate of Indigency, Certificate of Residency, and other legal certifications with verified digital QR codes.</li>
                    <li><strong>Facility Bookings:</strong> Reserve the Barangay Multi-Purpose Covered Court / Gym and Session Hall online.</li>
                    <li><strong>Equipment Rentals:</strong> Request chairs, tables, tents, sound systems, and other barangay supplies.</li>
                </ul>
            </div>

            <div style="text-align: center;">
                <a href="{{ route('dashboard') }}" class="btn-login">Log In to Your Dashboard</a>
            </div>

            <p style="font-size: 12.5px; color: #64748b; margin-top: 20px;">
                If you have questions or require assistance, please visit the Barangay Hall during regular office hours (Monday to Friday, 8:00 AM – 5:00 PM) or reply to this email.
            </p>

            <p style="margin-bottom: 0;">
                Sincerely,<br>
                <strong>Office of the Punong Barangay</strong><br>
                Barangay San Jose, Talibon, Bohol
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            &copy; {{ date('Y') }} Barangay San Jose, Talibon, Bohol. All rights reserved.<br>
            This is an automated notification from the official Barangay Information System.
        </div>
    </div>
</body>
</html>