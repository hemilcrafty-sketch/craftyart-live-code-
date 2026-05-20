<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Crafty Art - Upgrade Your Template</title>
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
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
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
            color: #5f0f40;
        }

        .text-accent {
            color: #9a031e;
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
            background: #5f0f40;
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
            background: #5f0f40;
            color: #ffffff !important;
            box-shadow: 0 4px 15px rgba(95, 15, 64, 0.3);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(95, 15, 64, 0.4);
        }

        /* Template Name Box */
        .template-name-box {
            background: #ffffff;
            border-radius: 10px;
            padding: 15px 30px;
            margin: 10px auto 0px;
            display: inline-block;
            border: 2px solid #5f0f40;
            box-shadow: 0 4px 12px rgba(95, 15, 64, 0.15);
            max-width: 90%;
        }

        .template-name {
            font-size: 22px;
            font-weight: 800;
            color: #5f0f40;
            margin: 0;
            text-align: center;
        }

        /* Watermark Warning Box */
        .watermark-warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0;
            text-align: center;
        }

        .watermark-icon {
            font-size: 36px;
            color: #dc3545;
            margin-bottom: 10px;
        }

        /* Price Comparison Box */
        .price-comparison-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 10px 0;
            margin: 20px 0 0;
            text-align: center;
            overflow: hidden;
        }

        .original-price {
            font-size: 24px;
            color: #6c757d;
            text-decoration: line-through;
            font-weight: 600;
        }

        .special-price {
            font-size: 42px;
            color: #5f0f40;
            font-weight: 800;
            line-height: 1;
        }

        .urgency-banner {
            background: #9a031e;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: center;
            font-weight: 700;
            font-size: 18px;
        }

        /* Promo Code Box */
        .promo-box {
            background: #f8e1e7;
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0 0;
            border: 2px dashed #5f0f40;
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
            color: #5f0f40;
            letter-spacing: 3px;
            padding: 15px 30px;
            background: white;
            border-radius: 8px;
            border: 2px solid #5f0f40;
            display: inline-block;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
        }

        .watermark-free-section {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
            border: 2px solid #28a745;
            text-align: center;
        }

        .watermark-free-icon {
            font-size: 48px;
            color: #28a745;
            margin-bottom: 15px;
        }

        .watermark-free-title {
            font-size: 24px;
            font-weight: 700;
            color: #28a745;
            margin-bottom: 20px;
        }

        .watermark-free-benefits {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: left;
            max-width: 500px;
            margin: 0 auto;
        }

        .watermark-free-benefits li {
            margin-bottom: 12px;
            padding-left: 35px;
            position: relative;
            font-size: 16px;
            color: #333;
            line-height: 1.5;
        }

        .watermark-free-benefits li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #28a745;
            font-weight: bold;
            font-size: 20px;
            width: 25px;
            height: 25px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #28a745;
        }

        .template-stacked {
            background: white;
            border-radius: 10px;
            margin: 20px 0;
            padding: 20px;
            border: 2px solid #e8d4e1;
            box-shadow: 0 4px 15px rgba(95, 15, 64, 0.08);
        }

        .template-image-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .template-image-container img {
            width: 100%;
            height: auto;
            display: block;
        }

        .template-details {
            text-align: center;
        }

        .template-details .title {
            font-size: 20px;
            font-weight: 700;
            color: #5f0f40;
            margin: 0 0 15px 0;
        }

        .template-details .description {
            color: #666;
            margin-bottom: 20px;
            font-size: 16px;
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

        .footer {
            background: #5f0f40;
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
            box-shadow: 0 8px 25px rgba(95, 15, 64, 0.3);
            border: 3px solid #5f0f40;
        }

        .logo img {
            width: 60px;
            height: auto;
        }

        /* Price Comparison Table */
        .price-table {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        .price-table td {
            padding: 0 15px;
            vertical-align: middle;
            text-align: center;
        }

        .price-arrow {
            font-size: 28px;
            color: #5f0f40;
            font-weight: bold;
        }

        /* Template Benefits */
        .template-benefits {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin: 15px 0 0;
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

            .special-price {
                font-size: 32px;
            }

            .original-price {
                font-size: 20px;
            }

            .template-name {
                font-size: 20px;
            }

            .promo-code {
                font-size: 24px;
                letter-spacing: 2px;
                padding: 12px 20px;
            }

            .watermark-free-benefits li {
                font-size: 15px;
                padding-left: 30px;
            }

            .template-image-container {
                max-width: 100%;
            }
        }

        @media (max-width: 480px) {
            .content-wrapper {
                padding: 15px;
            }

            .special-price {
                font-size: 28px;
            }

            .original-price {
                font-size: 18px;
            }

            .template-name {
                font-size: 18px;
            }

            .promo-code {
                font-size: 20px;
                letter-spacing: 1px;
                padding: 10px 15px;
            }

            .watermark-free-benefits li {
                font-size: 14px;
                padding-left: 25px;
            }

            .watermark-free-benefits li:before {
                font-size: 16px;
                width: 20px;
                height: 20px;
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
                        style="width:80px;height:80px;border-radius:50%;background:#ffffff;
                     box-shadow:0 5px 15px rgba(95, 15, 64, 0.3);text-align:center;">
                        <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/bvy/oe5/ff9/logo%404x.png"
                             alt="Crafty Art" width="40" height="40"
                             style="display:block;margin:0 auto;border:0;outline:none;text-decoration:none;">
                    </td>
                </tr>
            </table>
            <h1 class="text-white">Hello {{ $data['userData']['name'] ?? 'Creative Professional' }}!</h1>
        </div>
    </div>
    <div class="section">
        <div class="content-wrapper">
            <div class="text-center">
                <h2 class="text-primary">Remove Watermark From Your Downloaded Template!</h2>
                <p class="text-dark">Ready-to-use templates without any watermarks.</p>

                @if (!empty($data['data']['templates']))
                @php $template = $data['data']['templates'][0]; @endphp
                <div class="template-name-box">
                    <p class="template-name">{{ $template['title'] ?? 'Your Downloaded Template' }}</p>
                </div>
                @endif
            </div>

            <div class="watermark-warning">
                <div class="watermark-icon">⚠️</div>
                <h3 style="color: #dc3545; margin-bottom: 10px;">Your Template Has a Watermark</h3>
                <p style="margin-bottom: 0; font-weight: 600;">
                    You downloaded this template with a watermark. Get the watermark-free version now!
                </p>
            </div>

            <div class="price-comparison-box">
                <table class="price-table" border="0" cellpadding="0" cellspacing="0" align="center">
                    <tr>
                        <td style="width: 40%;">
                            <p class="original-price" style="margin-bottom: 5px;">{{ $template['amount'] ?? '₹100'}}</p>
                            <p style="font-size: 14px; color: #6c757d; margin: 0;">Original Price</p>
                        </td>
                        <td style="width: 20%;">
                            <div class="price-arrow">➡</div>
                        </td>
                        <td style="width: 40%;">
                            <p class="special-price" style="margin-bottom: 5px;">{{ $data['promo']['discount_price'] ?? '' }}</p>
                            <p style="font-size: 14px; color: #6c757d; margin: 0;">Upgrade Price</p>
                        </td>
                    </tr>
                </table>

                <div class="urgency-banner">
                    ⏰ LIMITED TIME OFFER: Remove watermark at {{ $data['promo']['disc'] ?? '50%' }} OFF!
                </div>

                <div style="margin: 10px 0 0px;">
                    <h3 style="color: #5f0f40; font-size: 22px; margin-bottom: 10px;">
                        Remove Watermark only at {{ $data['promo']['discount_price'] ?? '' }} instead of <span style="text-decoration: line-through">{{ $template['amount'] ?? '₹100' }}</span>
                    </h3>
                    <div style="background: #f8e1e7; padding: 12px 25px; border-radius: 25px; display: inline-block; margin: 10px 0;">
                        <p style="color: #5f0f40; font-size: 18px; font-weight: 700; margin: 0;">
                            ⭐ {{ $data['promo']['disc'] ?? '50%' }} Discount for Watermark Removal ⭐
                        </p>
                    </div>
                </div>
            </div>

            @if (!empty($data['promo']['code']))
            <div class="promo-box">
                <div class="promo-label">YOUR EXCLUSIVE UPGRADE PROMO CODE</div>
                <div class="promo-code">{{ $data['promo']['code'] }}</div>
                <p class="text-muted" style="margin-top: 10px;">
                    Apply this code during checkout to remove the watermark at {{ $data['promo']['disc'] ?? '50%' }} OFF
                </p>
            </div>
            @endif

            @if (!empty($data['data']['templates']))
            <div class="template-stacked">
                <div class="template-image-container">
                    <img src="{{ $template['image'] ?? '' }}" alt="{{ $template['title'] ?? '' }}">
                </div>
                <div class="template-details">
                    <h3 class="title">{{ $template['title'] ?? '' }}</h3>
                    <p class="description">
                        You downloaded this template with a watermark. Get the watermark-free version now!
                    </p>
                </div>
            </div>
            @endif

            <div class="text-center" style="margin: 25px 0 10px 0;">
                <a href="{{ $data['link'] ?? '#' }}"
                   class="btn btn-primary">
                    Upgrade Now - Only {{ $data['promo']['discount_price'] ?? '' }}!
                </a>
                <p class="text-muted" style="margin-top: 15px; font-size: 14px;">
                    One-time payment • Instant access • Commercial license included
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
                You are receiving this email because you downloaded a watermarked template from CraftyArt.
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