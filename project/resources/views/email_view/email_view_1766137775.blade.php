<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Thank You for Your Purchase! | Crafty Art</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet" type="text/css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f8f4f0;
            font-family: "Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 20px 0;
            -webkit-text-size-adjust: none;
            text-size-adjust: none;
            line-height: 1.5;
        }

        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(67, 40, 24, 0.1);
        }

        .section {
            width: 100%;
        }

        .content-wrapper {
            padding: 15px 40px;
        }

        .text-center {
            text-align: center;
        }

        .text-white {
            color: #ffffff;
        }

        .text-primary {
            color: #432818;
        }

        .text-accent {
            color: #bb7a52;
        }

        .text-dark {
            color: #333333;
        }

        .text-muted {
            color: #6c757d;
        }

        .bg-header {
            background: #432818;
        }

        h1 {
            font-size: 28px;
            font-weight: 700;
            line-height: 1.3;
            margin: 0 0 10px 0;
        }

        h2 {
            font-size: 24px;
            font-weight: 700;
            line-height: 1.3;
            margin: 0 0 15px 0;
        }

        h3 {
            font-size: 20px;
            font-weight: 600;
            line-height: 1.4;
            margin: 0 0 12px 0;
        }

        p {
            line-height: 1.6;
            margin: 0 0 15px 0;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            text-align: center;
            font-weight: 700;
            border-radius: 8px;
            padding: 16px 40px;
            font-size: 18px;
            line-height: 1.5;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            min-width: 250px;
        }

        .btn-primary {
            background: #432818;
            color: #ffffff !important;
            box-shadow: 0 4px 15px rgba(67, 40, 24, 0.3);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 40, 24, 0.4);
        }

        /* Thank You Header */
        .thankyou-header {
            background: linear-gradient(135deg, #432818 0%, #5c3926 100%);
            padding: 40px 20px 30px;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .thankyou-header:before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.05)"/></svg>');
            background-size: cover;
        }

        .thankyou-icon {
            font-size: 48px;
            margin-bottom: 20px;
            display: block;
        }

        /* Celebration Banner */
        .celebration-banner {
            background: #fef7e0;
            border: 2px solid #f0c674;
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0;
            text-align: center;
        }

        .celebration-icon {
            font-size: 36px;
            color: #e6b325;
            margin-bottom: 10px;
            display: block;
        }

        /* Reward Box */
        .reward-box {
            background: #f9f2ec;
            border-radius: 10px;
            padding: 25px 30px;
            margin: 25px 0;
            border: 2px solid #e8d4c9;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .reward-box:before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #432818, #bb7a52, #432818);
        }

        .reward-title {
            font-size: 24px;
            font-weight: 700;
            color: #432818;
            margin-bottom: 20px;
        }

        .promo-code-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin: 20px auto;
            border: 2px dashed #bb7a52;
            max-width: 400px;
        }

        .promo-label {
            font-size: 16px;
            color: #856404;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .promo-code {
            font-size: 32px;
            font-weight: 800;
            color: #432818;
            letter-spacing: 3px;
            padding: 15px 30px;
            background: white;
            border-radius: 8px;
            border: 2px solid #432818;
            display: inline-block;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
        }

        .discount-badge {
            background: #bb7a52;
            color: white;
            font-weight: 700;
            padding: 8px 20px;
            border-radius: 20px;
            display: inline-block;
            margin: 0 auto 15px;
            font-size: 18px;
        }

        /* Next Template Section */
        .next-template-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 10px;
            margin: 0 0;
            text-align: center;
        }

        .template-image-placeholder {
            width: 100%;
            max-width: 300px;
            height: 200px;
            background: linear-gradient(135deg, #e8d4c9 0%, #d4b9a6 100%);
            border-radius: 8px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #432818;
            font-weight: 600;
            font-size: 18px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .cta-container {
            margin: 25px 0 10px;
        }

        /* Benefits Section */
        .benefits-section {
            background: #f9f2ec;
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
        }

        .benefits-title {
            font-size: 22px;
            font-weight: 700;
            color: #432818;
            margin-bottom: 20px;
            text-align: center;
        }

        .benefits-list {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: left;
            max-width: 500px;
            margin: 0 auto;
        }

        .benefits-list li {
            margin-bottom: 15px;
            padding-left: 35px;
            position: relative;
            font-size: 16px;
            color: #333;
            line-height: 1.5;
        }

        .benefits-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #432818;
            font-weight: bold;
            font-size: 20px;
            width: 25px;
            height: 25px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #432818;
        }

        /* Footer */
        .footer {
            background: #432818;
            color: white;
            padding: 30px 40px;
            text-align: center;
        }

        .footer a {
            color: #ffffff;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .social-icons {
            display: flex;
            justify-content: center;
            gap: 20px;
            padding: 20px 0;
            flex-wrap: wrap;
        }

        .social-icon img {
            width: 28px;
            height: 28px;
            display: block;
        }

        /* Urgency Banner */
        .urgency-banner {
            background: #bb7a52;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: center;
            font-weight: 700;
            font-size: 18px;
        }

        /* Logo */
        .logo-container {
            padding: 30px 0 20px;
        }

        .logo {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            box-shadow: 0 8px 25px rgba(67, 40, 24, 0.3);
            border: 3px solid #432818;
        }

        .logo img {
            width: 60px;
            height: auto;
        }

        @media (max-width: 670px) {
            body {
                padding: 10px;
            }

            .email-container {
                border-radius: 8px;
            }

            .content-wrapper {
                padding: 20px;
            }

            h1 {
                font-size: 24px;
            }

            h2 {
                font-size: 22px;
            }

            h3 {
                font-size: 20px;
            }

            .btn {
                padding: 14px 30px;
                font-size: 16px;
                min-width: 200px;
            }

            .promo-code {
                font-size: 24px;
                letter-spacing: 2px;
                padding: 12px 20px;
            }

            .benefits-list li {
                font-size: 15px;
                padding-left: 30px;
            }
        }

        @media (max-width: 480px) {
            .content-wrapper {
                padding: 15px;
            }

            .promo-code {
                font-size: 20px;
                letter-spacing: 1px;
                padding: 10px 15px;
            }

            .benefits-list li {
                font-size: 14px;
                padding-left: 25px;
            }

            .benefits-list li:before {
                font-size: 16px;
                width: 20px;
                height: 20px;
            }
        }
    </style>
</head>
<body>
<div class="email-container">
    <!-- Thank You Header -->
    <div class="section bg-header">
        <div class="content-wrapper text-center">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center"
                   style="margin:0 auto 10px;">
                <tr>
                    <td align="center" valign="middle"
                        style="width:80px;height:80px;border-radius:50%;background:#ffffff;
                     box-shadow:0 5px 15px rgba(95, 15, 64, 0.3);text-align:center;">
                        <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/bvy/oe5/ff9/logo%404x.png"
                             alt="Crafty Art" width="40" height="40"
                             style="display:block;margin:0 auto;border:0;outline:none;text-decoration:none;">
                    </td>
                </tr>
            </table>
            <h1 class="text-white">Hello {{ $data['userData']['name'] ?? 'Creative Professional' }}!</h1>
            <h3 style="color: #ffffff; margin-bottom: 10px;">Thank You for Your Purchase!</h3>
        </div>
    </div>

    <!-- Main Content -->
    <div class="section">
        <div class="content-wrapper">
            <!-- Celebration Banner -->
            <div class="celebration-banner">
                <div class="celebration-icon">✨</div>
                <h3 style="color: #e6b325; margin-bottom: 10px;">Here's a Special Reward for Your Next Template Purchase!</h3>
                <p style="font-weight: 600; margin-bottom: 0;">
                    As a thank you for being our valued customer, we're offering you an exclusive discount on your next template!
                </p>
            </div>

            <!-- Reward Box -->
            <div class="reward-box">
                <div class="reward-title">Your Exclusive Reward</div>

                <div class="discount-badge">
                    🔥 Get {{ $data['promo']['disc'] }} OFF on Your Next Template
                </div>

                <div class="promo-code-container">
                    <div class="promo-label">YOUR EXCLUSIVE PROMO CODE</div>
                    <div class="promo-code">{{ $data['promo']['code'] }}</div>
                    <p class="text-muted" style="margin-top: 10px;">
                        Apply this code at checkout to get {{ $data['promo']['disc'] }} off your next template purchase
                    </p>
                </div>

                <div class="urgency-banner">
                    ⏰ Limited-time offer — don't miss it!
                </div>
            </div>

            <!-- Next Template Section -->
            <div class="next-template-section">
                <h3 class="text-primary">Discover Your Next Creative Template</h3>
                <p class="text-dark" style="margin-bottom: 25px;">
                    Explore thousands of professionally designed templates for invitations, social media, marketing materials, and more!
                </p>

                <div class="cta-container">
                    <a href="{{ $data['link'] ?? 'https://www.craftyartapp.com/templates/invitation' }}"
                       class="btn btn-primary">
                        Browse Templates & Use Your Code
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <h3 class="text-white">The Crafty Art Team</h3>
        <p style="margin-bottom: 0px; opacity: 0.9;">B - 815, IT Park, Opposite AR Mall, Uttran, Surat - 394105</p>

        <!-- Social Icons -->
        <div class="social-icons">
            <a href="https://www.instagram.com/craftyart_invitation/" target="_blank" style="display: inline-block;">
                <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/8eo/pm2/e8i/instagram%20%282%29.png"
                     alt="Instagram"
                     width="28"
                     height="28"
                     style="display:block;border:0;outline:none;text-decoration:none; width: 28px; height: 28px; max-width: 28px; filter: brightness(0) invert(1);">
            </a>
            <a href="https://in.pinterest.com/craftyart_official" target="_blank" style="display: inline-block;">
                <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/qxl/elc/7nd/pinterest%20%281%29.png"
                     alt="Pinterest"
                     width="28"
                     height="28"
                     style="display:block;border:0;outline:none;text-decoration:none; width: 28px; height: 28px; max-width: 28px; filter: brightness(0) invert(1);">
            </a>
            <a href="https://www.youtube.com/@craftyartgraphic7864" target="_blank" style="display: inline-block;">
                <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/tit/95c/jw8/youtube20%281%29.png"
                     alt="YouTube"
                     width="28"
                     height="28"
                     style="display:block;border:0;outline:none;text-decoration:none; width: 28px; height: 28px; max-width: 28px; filter: brightness(0) invert(1);">
            </a>
            <a href="https://www.facebook.com/craftyartapp/" target="_blank" style="display: inline-block;">
                <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/h63/d76/caw/facebook%20%281%29.png"
                     alt="Facebook"
                     width="28"
                     height="28"
                     style="display:block;border:0;outline:none;text-decoration:none; width: 28px; height: 28px; max-width: 28px; filter: brightness(0) invert(1);">
            </a>
            <a href="https://x.com/craftyartstudio" target="_blank" style="display: inline-block;">
                <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/leu/a25/pzr/twitter%20%281%29.png"
                     alt="Twitter"
                     width="28"
                     height="28"
                     style="display:block;border:0;outline:none;text-decoration:none; width: 28px; height: 28px; max-width: 28px; filter: brightness(0) invert(1);">
            </a>
        </div>

        <div style="margin-top: 0px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
            <p style="font-size: 14px; margin-bottom: 10px; opacity: 0.8;">
                You are receiving this email because you recently made a purchase from Crafty Art.
            </p>
            <p style="font-size: 14px;">
                <a href="https://www.craftyartapp.com/email-unsubscribe" target="_blank" style="color: #ffffff;">
                    Click here to unsubscribe
                </a>
            </p>
        </div>
    </div>
</div>
</body>
</html>