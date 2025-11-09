<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to <?= $data->course->name ?></title>
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
                <h1 style="font-size: 24px; color: #333333;">Dear <?= $data->user->full_name ?> <?= $data->user->last_name ?>,</h1>
                <p style="font-size: 16px; color: #666666; line-height: 1.6;">We hope this email finds you well and excited for the learning journey ahead! We want to extend our heartfelt gratitude for choosing to enroll in the <?= $data->course->name ?> offered by [COP Education]. Your dedication to education is commendable, and we're happy to have you on board.</p>
                <p style="font-size: 16px; color: #666666; line-height: 1.6;">Here at COP Education, we are committed to providing you with an enriching and transformative learning experience. Our team of experts has meticulously designed the <?= $data->course->name ?> to empower you with the knowledge and skills. Whether you're a beginner or looking to advance your expertise, we're confident that this course will be a valuable asset to your personal and professional growth.</p>
                <p style="font-size: 16px; color: #666666; line-height: 1.6;">As you embark on this educational journey, you can expect the following from us:</p>
                <ul style="font-size: 16px; color: #666666; line-height: 1.6; padding-left: 20px;">
                    <li><b>Comprehensive Learning Materials:</b> We've curated a comprehensive set of learning materials, including video lectures, reading resources and quizzes, and practical assignments.</li>
                    <li><b>Expert Instructors:</b> Our instructors are industry experts who bring years of practical experience to the table. Their insights and guidance will help you gain a real-world perspective on [Course Subject].</li>
                    <li><b>Flexible Learning:</b> We understand that life can get busy, so our course is structured to accommodate your schedule. Learn at your own pace and revisit the materials whenever you need.</li>
                </ul>
                <p style="font-size: 16px; color: #666666; line-height: 1.6;">You can access the <?= $data->course->name ?> by logging into your account on our platform at <a href="https://copeducation.com/" style="color: #007bff; text-decoration: none;">https://copeducation.com/</a>. Your course materials are waiting for you!</p>
                <p style="font-size: 16px; color: #666666; line-height: 1.6;">We're excited to see you progress through the course and hope you find it not only educational but also inspiring. If you have any questions or need assistance, please don't hesitate to contact us. Once again, thank you for choosing COP Education for your learning needs.</p>
                <p style="font-size: 16px; color: #333333; font-weight: bold;">Best regards,<br>Sivarami Reddy<br>Founder<br>COP Education<br><a href="https://copeducation.com/" style="color: #007bff; text-decoration: none;">https://copeducation.com/</a><br><a href="mailto:support@copeducation.com" style="color: #007bff; text-decoration: none;">support@copeducation.com</a></p>
            </td>
        </tr>
    </table>

</body>

</html>