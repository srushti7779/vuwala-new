<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to COP Education!</title>
</head>

<body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f7f7f7;">

    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse; margin-top: 20px; background-color: #ffffff; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
        <tr>
            <td align="center" style="padding: 20px;">
                <img src="https://ik.imagekit.io/asbgbgese/Group_1000004746.png?updatedAt=1682576249866" alt="COP Education Logo" width="150" height="auto" style="display: block;">
            </td>
        </tr>
        <tr>
            <td style="padding: 20px;">
                <h1 style="font-size: 24px; color: #333333;">Dear <?= $data->full_name ?> <?= $data->last_name ?>,</h1>
                <p style="font-size: 16px; color: #666666; line-height: 1.6;">We are thrilled to welcome you to COP Education! 🚀 Congratulations on taking the first step towards unlocking a world of knowledge and opportunities. Whether you're pursuing academic excellence, acquiring new skills, or exploring your passions, our platform is here to support and guide you every step of the way.</p>
                <p style="font-size: 16px; color: #666666; line-height: 1.6;">Here's a quick overview of what you can expect from your learning journey with us:</p>
                <ul style="font-size: 16px; color: #666666; line-height: 1.6; padding-left: 20px;">
                    <li>Expert Instructors: Learn from industry experts, scholars, and practitioners who are passionate about sharing their knowledge and experience with you.</li>
                    <li>Diverse Content: Explore a wide range of courses, lectures, interactive quizzes, and projects that cater to your interests and goals.</li>
                    <li>Progress Tracking: Monitor your progress, review completed modules, and set achievable goals to keep yourself motivated.</li>
                </ul>
                <p style="font-size: 16px; color: #666666; line-height: 1.6;">To get started, here are a few simple steps:</p>
                <ol style="font-size: 16px; color: #666666; line-height: 1.6; padding-left: 20px;">
                    <li><b>Log in:</b> Use your registered email (<?= $data->email ?>) and the password you created during registration to access your account.</li>
                    <li><b>Explore Courses:</b> Browse through our course catalog and find topics that resonate with you. Enroll in your first course to embark on your learning journey.</li>
                    <li><b>Stay Updated:</b> Keep an eye on your inbox for announcements about new courses, updates, and special events.</li>
                </ol>
                <p style="font-size: 16px; color: #666666; line-height: 1.6;">We are committed to helping you achieve your learning goals and make the most of your time with COP Education. Thank you for choosing us to be a part of your educational journey.</p>
                <p style="font-size: 16px; color: #666666; line-height: 1.6;">If you have any questions or need assistance, please don't hesitate to reach out to our support team at <a href="mailto:support@copeducation.com" style="color: #007bff; text-decoration: none;">support@copeducation.com</a>.</p>
                <p style="font-size: 16px; color: #666666; line-height: 1.6;">Let's make learning an exciting and enriching experience together!</p>
                <p style="font-size: 16px; color: #666666; line-height: 1.6;">Happy learning!</p>
                <p style="font-size: 16px; color: #333333; font-weight: bold;">Best regards,<br>Sivarami Reddy<br>Founder<br>COP Education<br><a href="https://copeducation.com/" style="color: #007bff; text-decoration: none;">https://copeducation.com/</a><br><a href="mailto:support@copeducation.com" style="color: #007bff; text-decoration: none;">support@copeducation.com</a></p>
            </td>
        </tr>
    </table>

</body>

</html>