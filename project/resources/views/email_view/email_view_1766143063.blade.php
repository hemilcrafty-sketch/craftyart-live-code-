<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Crafty Art - Renew Your Subscription</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet" type="text/css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f8f9fa;
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
            background-color: #faf5f5;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(64, 63, 76, 0.1);
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

        .text-left {
            text-align: left;
        }

        .text-white {
            color: #ffffff;
        }

        .text-primary {
            color: #403f4c;
        }

        .text-accent {
            color: #6a6978;
        }

        .text-dark {
            color: #333333;
        }

        .text-success {
            color: #28a745;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-muted {
            color: #6c757d;
        }

        .bg-header {
            background: linear-gradient(135deg, #403f4c 0%, #2d2c36 100%);
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
            background: linear-gradient(135deg, #403f4c 0%, #2d2c36 100%);
            color: #ffffff !important;
            box-shadow: 0 4px 15px rgba(64, 63, 76, 0.3);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(64, 63, 76, 0.4);
        }

        /* Plan Name Styling */
        .plan-name-box {
            background: linear-gradient(135deg, #ffffff 0%, #f5f4f8 100%);
            border-radius: 10px;
            padding: 15px 30px;
            margin: 10px auto 0px;
            display: inline-block;
            border: 2px solid #403f4c;
            box-shadow: 0 4px 12px rgba(64, 63, 76, 0.15);
        }

        .plan-name {
            font-size: 22px;
            font-weight: 800;
            color: #403f4c;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .plan-subtitle {
            font-size: 14px;
            color: #6a6978;
            margin: 5px 0 0 0;
            font-weight: 600;
        }

        /* Promo Code Box */
        .promo-box {
            background: linear-gradient(135deg, #fff9c4 0%, #ffecb3 100%);
            border-radius: 10px;
            padding: 20px;
            margin: 10px 0;
            border: 2px dashed #ff9800;
            text-align: center;
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
            color: #dc3545;
            letter-spacing: 3px;
            padding: 15px 30px;
            background: white;
            border-radius: 8px;
            border: 2px solid #dc3545;
            display: inline-block;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
        }

        .urgency-banner {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: 700;
            font-size: 18px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }

        .feature-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .feature-icon {
            font-size: 36px;
            margin-bottom: 15px;
            color: #403f4c;
        }

        .feature-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .testimonial {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
            border-left: 5px solid #2196f3;
        }

        .testimonial-text {
            font-size: 18px;
            font-style: italic;
            color: #333;
            margin-bottom: 15px;
        }

        .testimonial-author {
            font-size: 16px;
            font-weight: 700;
            color: #403f4c;
            text-align: right;
        }

        .social-icons {
            display: flex;
            justify-content: center;
            gap: 20px;
            padding: 20px 0;
            flex-wrap: wrap;
        }

        .social-icon img {
            width: 32px;
            height: 32px;
            display: block;
            transition: transform 0.3s;
        }

        .social-icon:hover img {
            transform: scale(1.1);
        }

        .footer {
            background: #2d2c36;
            color: white;
            padding: 30px 40px;
            text-align: center;
        }

        .footer a {
            color: #bb86fc;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

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
            box-shadow: 0 8px 25px rgba(64, 63, 76, 0.3);
            border: 3px solid #403f4c;
        }

        .logo img {
            width: 60px;
            height: auto;
        }

        .timer {
            background: #dc3545;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            display: inline-block;
            font-weight: 700;
            font-size: 18px;
            margin: 15px 0;
        }

        .expired-badge {
            background: #dc3545;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 16px;
            display: inline-block;
            margin-bottom: 15px;
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

            .features-grid {
                grid-template-columns: 1fr;
            }

            .plan-name {
                font-size: 20px;
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

            .plan-name {
                font-size: 18px;
            }
        }

        /* Mobile Responsive Styles */
        @media (max-width:620px) {
            .desktop_hide table.icons-inner {
                display: inline-block !important;
            }

            .icons-inner {
                text-align: center;
            }

            .icons-inner td {
                margin: 0 auto;
            }

            .mobile_hide {
                display: none;
            }

            .row-content {
                width: 100% !important;
            }

            .stack .column {
                width: 100%;
                display: block;
            }

            .mobile_hide {
                min-height: 0;
                max-height: 0;
                max-width: 0;
                overflow: hidden;
                font-size: 0px;
            }

            .desktop_hide,
            .desktop_hide table {
                display: table !important;
                max-height: none !important;
            }

            .row-1 .row-content {
                padding: 10px 10px 0 !important;
            }
        }

    </style>
</head>
<body>
<div class="email-container">
    <div class="section bg-header">
        <div class="content-wrapper text-center">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center"
                   style="margin:0 auto 10px;">
                <tr>
                    <td align="center" valign="middle"
                        style="width:80px;height:80px;border-radius:50%;background:#e6d4f5;
                     box-shadow:0 5px 15px #7B5E92;text-align:center;">
                        <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/bvy/oe5/ff9/logo%404x.png"
                             alt="Crafty Art" width="40" height="40"
                             style="display:block;margin:0 auto;border:0;outline:none;text-decoration:none;">
                    </td>
                </tr>
            </table>
            <h1 class="text-white">Welcome Back, {{ $data['userData']['name'] ?? 'Valued Member' }}!</h1>
        </div>
    </div>
    <div class="section">
        <div class="content-wrapper">
            <div class="text-center">
                <div class="expired-badge">🔥 YOUR PLAN JUST EXPIRED</div>
                <h2 class="text-primary">We'd Love to Have You Back! 🎉</h2>
                <p class="text-dark">Today only — renew your subscription with an exclusive <strong>{{$data['promo']['disc']}} OFF</strong> discount!</p>
                <div class="plan-name-box">
                    <p class="plan-name">{{ $data['data']['package_name'] ?? 'Your Previous Plan' }}</p>
                    <p class="plan-subtitle">Renew Now & Continue Your Unlimited Creative Access</p>
                </div>
            </div>
            <div style="text-align: center; padding: 5px 0 0px;">
                <table border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 0 auto;">
                    <tr>
                        <td style="padding: 0 15px; vertical-align: middle;">
                            <p style="color: #666666; text-decoration: line-through; margin: 0; font-size: 20px;">
                                {{ $data['data']['offer_price'] }}
                            </p>
                            <p style="margin: 5px 0 0 0; font-size: 14px; color: #666666;">
                                Regular Price
                            </p>
                        </td>
                        <td style="padding: 0 15px; font-size: 24px; color: #403f4c; vertical-align: middle;">➡</td>
                        <td style="padding: 0 15px; vertical-align: middle;">
                            <p style="color: #28a745; font-size: 36px; font-weight: bold; margin: 0;">
                                {{ $data['promo']['discount_price'] }}
                            </p>
                            <p style="margin: 5px 0 0 0; font-size: 14px; color: #666666;">
                                Renewal Special Price
                            </p>
                        </td>
                    </tr>
                </table>

                <div class="urgency-banner">
                    ⏳ LIMITED-TIME OFFER: Valid till tonight — don't miss out!
                </div>
                <div style="margin: 10px 0 0px;">
                    <h3 style="color: #dc3545; font-size: 22px; margin-bottom: 10px;">
                        Renew for just {{ $data['promo']['discount_price'] }} instead of <span style="text-decoration: line-through">{{ $data['data']['offer_price'] }}</span>
                    </h3>
                    <div style="background: #ffebee; padding: 12px 25px; border-radius: 25px; display: inline-block; margin: 10px 0;">
                        <p style="color: #c62828; font-size: 18px; font-weight: 700; margin: 0;">
                            ⭐ {{$data['promo']['disc']}} INSTANT DISCOUNT APPLIED ⭐
                        </p>
                    </div>
                </div>
            </div>
            <div class="promo-box">
                <div class="promo-label">YOUR EXCLUSIVE RENEWAL COUPON CODE</div>
                <div class="promo-code">{{$data['promo']['code']}}</div>
                <p class="text-muted" style="margin-top: 10px;">
                    Copy this code and apply during checkout to activate your {{$data['promo']['disc']}} discount
                </p>
            </div>
            <div class="text-center" style="margin: 20px 0 10px 0;">
                <a href="{{ $data['link'] ?? '#'}}"
                   class="btn btn-primary">
                    🔄 Renew Your Plan Now - Only {{ $data['promo']['discount_price']}}!
                </a>
                <p class="text-muted" style="margin-top: 15px; font-size: 14px;">
                    Secure renewal • Instant reactivation • Continue your creative journey
                </p>
            </div>

            <table class="nl-container" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation"
                   style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                <tbody>
                <tr>
                    <td>
                        <table class="row row-1" align="center" width="100%" border="0" cellpadding="0" cellspacing="0"
                               role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                            <tbody>
                            <tr>
                                <td>
                                    <table class="row-content stack" align="center" border="0" cellpadding="0" cellspacing="0"
                                           role="presentation"
                                           style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-radius: 0; color: #000000; padding: 10px; width: 600px; margin: 0 auto;"
                                           width="600">
                                        <tbody>
                                        <tr>
                                            <td class="column column-1" width="50%"
                                                style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; background: linear-gradient(135deg, #ffffff 0%, #f5f4f8 100%); border: 2px solid #403f4c; vertical-align: top; border-radius: 10px;">
                                                <table class="html_block block-1" width="100%" border="0" cellpadding="0" cellspacing="0"
                                                       role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                                                    <tbody>
                                                    <tr>
                                                        <td class="pad">
                                                            <div style="font-family:Arial, Helvetica, sans-serif;text-align:center;"
                                                                 align="center">
                                                                <table class="paragraph_block block-1" width="100%" border="0" cellpadding="0"
                                                                       cellspacing="0"
                                                                       style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td class="pad" style="padding: 20px;">
                                                                            <div
                                                                                    style="color:#403f4c;direction:ltr;font-family:Arial, Helvetica, sans-serif;font-size:32px;font-weight:400;letter-spacing:0px;line-height:1.2;text-align:center;mso-line-height-alt:38px;">
                                                                                <p style="margin: 0 0 10px 0;">🎨</p>
                                                                            </div>
                                                                            <div
                                                                                    style="color:#403f4c;direction:ltr;font-family:Arial, Helvetica, sans-serif;font-size:16px;font-weight:700;letter-spacing:0px;line-height:1.3;text-align:center;mso-line-height-alt:21px; margin-bottom: 8px;">
                                                                                15,000+ Premium Templates
                                                                            </div>
                                                                            <div
                                                                                    style="color:#666666;direction:ltr;font-family:Arial, Helvetica, sans-serif;font-size:14px;font-weight:400;letter-spacing:0px;line-height:1.4;text-align:center;mso-line-height-alt:20px;">
                                                                                Professional designs for all occasions
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                            <td class="column gap"
                                                style="vertical-align: top; mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left;">
                                                <table style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 10px; height: 10px;"
                                                       width="10" height="10"></table>
                                            </td>
                                            <td class="column column-2" width="50%"
                                                style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; background: linear-gradient(135deg, #ffffff 0%, #f5f4f8 100%); border: 2px solid #403f4c; padding-bottom: 5px; padding-top: 5px; vertical-align: top; border-radius: 10px;">
                                                <table class="html_block block-1" width="100%" border="0" cellpadding="0" cellspacing="0"
                                                       role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                                                    <tbody>
                                                    <tr>
                                                        <td class="pad">
                                                            <div style="font-family:Arial, Helvetica, sans-serif;text-align:center;"
                                                                 align="center">
                                                                <table class="paragraph_block block-1" width="100%" border="0" cellpadding="0"
                                                                       cellspacing="0"
                                                                       style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td class="pad" style="padding: 20px;">
                                                                            <div
                                                                                    style="color:#403f4c;direction:ltr;font-family:Arial, Helvetica, sans-serif;font-size:32px;font-weight:400;letter-spacing:0px;line-height:1.2;text-align:center;mso-line-height-alt:38px;">
                                                                                <p style="margin: 0 0 10px 0;">💎</p>
                                                                            </div>
                                                                            <div
                                                                                    style="color:#403f4c;direction:ltr;font-family:Arial, Helvetica, sans-serif;font-size:16px;font-weight:700;letter-spacing:0px;line-height:1.3;text-align:center;mso-line-height-alt:21px; margin-bottom: 8px;">
                                                                                Premium Assets
                                                                            </div>
                                                                            <div
                                                                                    style="color:#666666;direction:ltr;font-family:Arial, Helvetica, sans-serif;font-size:14px;font-weight:400;letter-spacing:0px;line-height:1.4;text-align:center;mso-line-height-alt:20px;">
                                                                                Fonts, graphics &amp; elements included
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <table class="row row-2" align="center" width="100%" border="0" cellpadding="0" cellspacing="0"
                               role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                            <tbody>
                            <tr>
                                <td>
                                    <table class="row-content stack" align="center" border="0" cellpadding="0" cellspacing="0"
                                           role="presentation"
                                           style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-radius: 0; color: #000000; padding: 10px; width: 600px; margin: 0 auto;"
                                           width="600">
                                        <tbody>
                                        <tr>
                                            <td class="column column-1" width="50%"
                                                style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; background: linear-gradient(135deg, #ffffff 0%, #f5f4f8 100%); border: 2px solid #403f4c; padding-bottom: 5px; padding-top: 5px; vertical-align: top; border-radius: 10px;">
                                                <table class="html_block block-1" width="100%" border="0" cellpadding="0" cellspacing="0"
                                                       role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                                                    <tbody>
                                                    <tr>
                                                        <td class="pad">
                                                            <div style="font-family:Arial, Helvetica, sans-serif;text-align:center;"
                                                                 align="center">
                                                                <table class="paragraph_block block-1" width="100%" border="0" cellpadding="0"
                                                                       cellspacing="0"
                                                                       style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td class="pad" style="padding: 20px;">
                                                                            <div
                                                                                    style="color:#403f4c;direction:ltr;font-family:Arial, Helvetica, sans-serif;font-size:32px;font-weight:400;letter-spacing:0px;line-height:1.2;text-align:center;mso-line-height-alt:38px;">
                                                                                <p style="margin: 0 0 10px 0;">⚡</p>
                                                                            </div>
                                                                            <div
                                                                                    style="color:#403f4c;direction:ltr;font-family:Arial, Helvetica, sans-serif;font-size:16px;font-weight:700;letter-spacing:0px;line-height:1.3;text-align:center;mso-line-height-alt:21px; margin-bottom: 8px;">
                                                                                80% Faster Design
                                                                            </div>
                                                                            <div
                                                                                    style="color:#666666;direction:ltr;font-family:Arial, Helvetica, sans-serif;font-size:14px;font-weight:400;letter-spacing:0px;line-height:1.4;text-align:center;mso-line-height-alt:20px;">
                                                                                Complete projects in minutes
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                            <td class="column gap"
                                                style="vertical-align: top; mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left;">
                                                <table style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 10px; height: 10px;"
                                                       width="10" height="10"></table>
                                            </td>
                                            <td class="column column-2" width="50%"
                                                style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; background: linear-gradient(135deg, #ffffff 0%, #f5f4f8 100%); border: 2px solid #403f4c; padding-bottom: 5px; padding-top: 5px; vertical-align: top; border-radius: 10px;">
                                                <table class="html_block block-1" width="100%" border="0" cellpadding="0" cellspacing="0"
                                                       role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                                                    <tbody>
                                                    <tr>
                                                        <td class="pad">
                                                            <div style="font-family:Arial, Helvetica, sans-serif;text-align:center;"
                                                                 align="center">
                                                                <table class="paragraph_block block-1" width="100%" border="0" cellpadding="0"
                                                                       cellspacing="0"
                                                                       style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td class="pad" style="padding: 20px;">
                                                                            <div
                                                                                    style="color:#403f4c;direction:ltr;font-family:Arial, Helvetica, sans-serif;font-size:32px;font-weight:400;letter-spacing:0px;line-height:1.2;text-align:center;mso-line-height-alt:38px;">
                                                                                <p style="margin: 0 0 10px 0;">🎬</p>
                                                                            </div>
                                                                            <div
                                                                                    style="color:#403f4c;direction:ltr;font-family:Arial, Helvetica, sans-serif;font-size:16px;font-weight:700;letter-spacing:0px;line-height:1.3;text-align:center;mso-line-height-alt:21px; margin-bottom: 8px;">
                                                                                HD Video Templates
                                                                            </div>
                                                                            <div
                                                                                    style="color:#666666;direction:ltr;font-family:Arial, Helvetica, sans-serif;font-size:14px;font-weight:400;letter-spacing:0px;line-height:1.4;text-align:center;mso-line-height-alt:20px;">
                                                                                Ready-to-use motion graphics
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>

            <div style="background: linear-gradient(135deg, #f0f8ff 0%, #e1f5fe 100%); padding: 20px; border-radius: 10px; margin: 15px 0;">
                <h3 class="text-primary text-center" style="margin-bottom: 20px;">Renew Now & Get These Exclusive Benefits:</h3>

                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 10px;">
                    <tr>
                        <td width="30" valign="top" style="padding: 8px 0;">
                            <span style="color: #28a745; font-weight: bold; font-size: 18px;">✓</span>
                        </td>
                        <td valign="top" style="padding: 8px 0 8px 10px;">
                            <strong>Resume Your Work:</strong> Get back to your saved projects instantly
                        </td>
                    </tr>
                    <tr>
                        <td width="30" valign="top" style="padding: 8px 0;">
                            <span style="color: #28a745; font-weight: bold; font-size: 18px;">✓</span>
                        </td>
                        <td valign="top" style="padding: 8px 0 8px 10px;">
                            <strong>Unlimited Downloads:</strong> Continue without watermarks or limits
                        </td>
                    </tr>
                    <tr>
                        <td width="30" valign="top" style="padding: 8px 0;">
                            <span style="color: #28a745; font-weight: bold; font-size: 18px;">✓</span>
                        </td>
                        <td valign="top" style="padding: 8px 0 8px 10px;">
                            <strong>Priority Support:</strong> 24/7 dedicated assistance for members
                        </td>
                    </tr>
                    <tr>
                        <td width="30" valign="top" style="padding: 8px 0;">
                            <span style="color: #28a745; font-weight: bold; font-size: 18px;">✓</span>
                        </td>
                        <td valign="top" style="padding: 8px 0 8px 10px;">
                            <strong>New Templates:</strong> Access 1000+ templates added while you were away
                        </td>
                    </tr>
                    <tr>
                        <td width="30" valign="top" style="padding: 8px 0;">
                            <span style="color: #28a745; font-weight: bold; font-size: 18px;">✓</span>
                        </td>
                        <td valign="top" style="padding: 8px 0 8px 10px;">
                            <strong>Commercial License:</strong> Use for all your client projects
                        </td>
                    </tr>
                </table>
            </div>

            <div class="text-center">
                <h3 class="text-danger">Don't Let Your Creative Tools Expire!</h3>
                <p class="text-dark" style="margin-bottom: 20px; font-size: 16px;">
                    This exclusive {{$data['promo']['disc']}} renewal discount is available for today only. Thousands of designers have renewed and continued their creative journey with Crafty Art. Don't miss out on this special welcome-back offer!
                </p>
                <a href="{{ $data['link'] ?? '#' }}"
                   class="btn btn-primary">
                    🔥 Renew Now & Save {{$data['promo']['disc']}}! 🔥
                </a>
                <p class="text-muted" style="margin-top: 15px; font-size: 14px;">
                    Limited time offer • Valid only for expired members • Instant reactivation
                </p>
            </div>
        </div>
    </div>
    <div class="footer">
        <h3 class="text-white">Crafty Art Team</h3>
        <p style="margin-bottom: 0px;">B - 815, IT Park, Opposite AR Mall, Uttran, Surat - 394105</p>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td align="center" style="padding: 5px 0 0;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td align="center" width="20%" style="padding: 10px 0;">
                                <a href="https://www.instagram.com/craftyart_invitation/" target="_blank" style="display: inline-block;">
                                    <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/8eo/pm2/e8i/instagram%20%282%29.png"
                                         alt="Instagram"
                                         width="28"
                                         height="28"
                                         class="text-white"
                                         style="display:block;border:0;outline:none;text-decoration:none; width: 28px; height: 28px; max-width: 28px;">
                                </a>
                            </td>

                            <td align="center" width="20%" style="padding: 10px 0;">
                                <a href="https://in.pinterest.com/craftyart_official" target="_blank" style="display: inline-block;">
                                    <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/qxl/elc/7nd/pinterest%20%281%29.png"
                                         alt="Pinterest"
                                         width="28"
                                         class="text-white"
                                         height="28"
                                         style="display:block;border:0;outline:none;text-decoration:none; width: 28px; height: 28px; max-width: 28px; filter: brightness(0) invert(1);">
                                </a>
                            </td>

                            <td align="center" width="20%" style="padding: 10px 0;">
                                <a href="https://www.youtube.com/@craftyartgraphic7864" target="_blank" style="display: inline-block;">
                                    <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/tit/95c/jw8/youtube%20%281%29.png"
                                         alt="YouTube"
                                         width="28"
                                         height="28"
                                         style="display:block;border:0;outline:none;text-decoration:none; width: 28px; height: 28px; max-width: 28px; filter: brightness(0) invert(1);">
                                </a>
                            </td>
                            <td align="center" width="20%" style="padding: 10px 0;">
                                <a href="https://www.facebook.com/craftyartapp/" target="_blank" style="display: inline-block;">
                                    <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/h63/d76/caw/facebook%20%281%29.png"
                                         alt="Facebook"
                                         width="28"
                                         height="28"
                                         style="display:block;border:0;outline:none;text-decoration:none; width: 28px; height: 28px; max-width: 28px; filter: brightness(0) invert(1);">
                                </a>
                            </td>
                            <td align="center" width="20%" style="padding: 10px 0;">
                                <a href="https://x.com/craftyartstudio" target="_blank" style="display: inline-block;">
                                    <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/leu/a25/pzr/twitter%20%281%29.png"
                                         alt="Twitter"
                                         width="28"
                                         height="28"
                                         style="display:block;border:0;outline:none;text-decoration:none; width: 28px; height: 28px; max-width: 28px; filter: brightness(0) invert(1);">
                                </a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div style="margin-top: 0px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
            <p style="font-size: 14px; margin-bottom: 10px;">
                You are receiving this email because your CraftyArt subscription has recently expired.
            </p>
            <p style="font-size: 14px;">
                <a href="https://www.craftyartapp.com/email-unsubscribe" target="_blank" style="color: #ff6b6b;">
                    Click here to unsubscribe from renewal reminders
                </a>
            </p>
        </div>
    </div>
</div>

</body>
</html>