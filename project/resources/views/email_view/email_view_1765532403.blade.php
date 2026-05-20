<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Crafty Art - Complete Your Template Purchase</title>
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
            color: #3a0ca3;
        }

        .text-accent {
            color: #3a0ca3;
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
            background: #3a0ca3;
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
            background: #3a0ca3;
            color: #ffffff !important;
            box-shadow: 0 4px 15px rgba(58, 12, 163, 0.3);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(58, 12, 163, 0.4);
        }

        /* Template Name Box */
        .template-name-box {
            background: #ffffff;
            border-radius: 10px;
            padding: 15px 30px;
            margin: 10px auto 0px;
            display: inline-block;
            border: 2px solid #3a0ca3;
            box-shadow: 0 4px 12px rgba(58, 12, 163, 0.15);
            max-width: 90%;
        }

        .template-name {
            font-size: 22px;
            font-weight: 800;
            color: #3a0ca3;
            margin: 0;
            text-align: center;
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
            color: #3a0ca3;
            font-weight: 800;
            line-height: 1;
        }

        .urgency-banner {
            background: #dc3545;
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
            background: #d7ddfa;
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0 0;
            border: 2px dashed #3a0ca3;
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
            color: #3a0ca3;
            letter-spacing: 3px;
            padding: 15px 30px;
            background: white;
            border-radius: 8px;
            border: 2px solid #3a0ca3;
            display: inline-block;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
        }

        /* Template Grid */
        .template-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }

        .template-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #dee2e6;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .template-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .template-content {
            padding: 15px;
        }

        .template-title {
            font-size: 16px;
            font-weight: 700;
            color: #3a0ca3;
            margin: 0 0 10px 0;
            line-height: 1.3;
        }

        .template-price {
            font-size: 18px;
            font-weight: 800;
            color: #3a0ca3;
            margin: 10px 0 5px 0;
        }

        .template-features {
            list-style: none;
            padding: 0;
            margin: 10px 0 0 0;
        }

        .template-features li {
            padding: 5px 0 5px 25px;
            position: relative;
            font-size: 14px;
            color: #666;
        }

        .template-features li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #28a745;
            font-weight: bold;
            font-size: 16px;
        }

        /* Single Template View */
        .single-template {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #dee2e6;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .single-template-image {
            width: 100%;
            max-height: 250px;
            object-fit: contain;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        /* Template Section Styles (YOUR DESIGN) */
        .template-section {
            padding: 5px 0 0;
            margin: 0;
        }

        .template-section-title {
            text-align: center;
            color: #4B6CB7;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #8A2BE2;
        }

        .cards-wrapper {
            column-count: 2;
            column-gap: 22px;
            margin: 0;
        }

        .card {
            flex: 0 0 calc(50% - 9px);
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 15px;
            border: 2px solid #e0e8ff;
            padding: 5px;
            box-shadow: 0 4px 15px rgba(75, 108, 183, 0.08);
            transition: all 0.3s ease;
            break-inside: avoid;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(75, 108, 183, 0.15);
            border-color: #3a0ca3;
        }

        .image-wrapper {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
        }

        .image-wrapper img {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.3s ease;
        }

        .card:hover .image-wrapper img {
            transform: scale(1.05);
        }

        .card-title {
            margin: 10px 8px 6px;
            font-size: 14px;
            font-weight: 600;
            color: #4B6CB7;
            text-align: left;
        }

        .card-price {
            margin: 0 8px 3px;
            font-size: 13px;
            font-weight: bold;
            color: #28A745;
            text-align: left;
        }

        .card-price span {
            font-weight: normal;
            color: #666;
            font-size: 12px;
        }

        /* Single Template Layout (YOUR DESIGN) */
        .single-template-layout {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            background: #fff;
            border-radius: 10px;
            margin: 10px 0 0;
            padding: 10px;
            box-shadow: 0 4px 15px rgba(75, 108, 183, 0.08);
        }

        .single-template-layout:hover {
            border-color: #3a0ca3;
            box-shadow: 0 8px 25px rgba(75, 108, 183, 0.15);
        }

        .single-template-layout .image-block {
            flex: 0 0 160px;
        }

        .single-template-layout .image-block img {
            width: 100%;
            border-radius: 8px;
        }

        .single-template-layout .text-block {
            flex: 1;
        }

        .single-template-layout .title {
            font-size: 15px;
            font-weight: 600;
            color: #4B6CB7;
            margin: 0 0 6px;
        }

        .single-template-layout .price {
            font-size: 14px;
            font-weight: bold;
            color: #28A745;
            margin: 0 0 15px;
        }

        .single-template-layout .price span {
            font-weight: normal;
            color: #666;
        }

        .single-template-layout ul {
            list-style: none;
            padding: 0;
            margin-top: 15px;
            font-size: 13px;
            color: #4B6CB7;
            line-height: 1.6;
        }

        .single-template-layout ul li {
            margin-bottom: 8px;
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
            background: #3a0ca3;
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
            box-shadow: 0 8px 25px rgba(58, 12, 163, 0.3);
            border: 3px solid #3a0ca3;
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
            color: #3a0ca3;
            font-weight: bold;
        }

        /* Template Benefits */
        .template-benefits {
            background: #f8f9fa;
            padding: 20px;
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

            .cards-wrapper {
                column-count: 1;
            }

            .single-template-layout {
                flex-direction: column;
            }

            .single-template-layout .image-block {
                flex: 0 0 auto;
                width: 100%;
            }

            .promo-code {
                font-size: 24px;
                letter-spacing: 2px;
                padding: 12px 20px;
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
        }
    </style>
</head>
<body>
<div class="email-container">
    <!-- Header -->
    <div class="section bg-header">
        <div class="content-wrapper text-center">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center"
                   style="margin:0 auto 10px;">
                <tr>
                    <td align="center" valign="middle"
                        style="width:80px;height:80px;border-radius:50%;background:#ffffff;
                     box-shadow:0 5px 15px rgba(58, 12, 163, 0.3);text-align:center;">
                        <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/bvy/oe5/ff9/logo%404x.png"
                             alt="Crafty Art" width="40" height="40"
                             style="display:block;margin:0 auto;border:0;outline:none;text-decoration:none;">
                    </td>
                </tr>
            </table>
            <h1 class="text-white">Hello {{ $data['userData']['name'] ?? 'Creative Professional' }}!</h1>
        </div>
    </div>

    <!-- Main Content -->
    <div class="section">
        <div class="content-wrapper">
            <!-- Template Highlight -->
            <div class="text-center">
                <h2 class="text-primary">Complete Your Template Purchase!</h2>
                <p class="text-dark">Don't miss out on these professionally designed templates</p>

                <!-- Template Name Box -->
                <div class="template-name-box">
                    <p class="template-name">Your {{ $data['data']['templates'][0]['title'] ?? 'Premium Template' }} is Ready!</p>
                </div>
            </div>

            <!-- Price Comparison -->
            <div class="price-comparison-box">
                <table class="price-table" border="0" cellpadding="0" cellspacing="0" align="center">
                    <tr>
                        <!-- Original Price -->
                        <td style="width: 40%;">
                            <p class="original-price" style="margin-bottom: 5px;">{{ $data['data']['amount'] ?? '₹100'}}</p>
                            <p style="font-size: 14px; color: #6c757d; margin: 0;">Original Price</p>
                        </td>

                        <!-- Arrow -->
                        <td style="width: 20%;">
                            <div class="price-arrow">➡</div>
                        </td>

                        <!-- Special Price -->
                        <td style="width: 40%;">
                            <p class="special-price" style="margin-bottom: 5px;">{{ $data['promo']['discount_price'] ?? '' }}</p>
                            <p style="font-size: 14px; color: #6c757d; margin: 0;">Today's Price</p>
                        </td>
                    </tr>
                </table>

                <!-- Savings Badge -->
                <div style="background: #3a0ca3; color: white; padding: 8px 25px; border-radius: 20px; display: inline-block; margin-top: 15px;">
                    <p style="margin: 0; font-weight: 700; font-size: 16px;">Limited Time Offer!</p>
                </div>

                <div style="margin: 10px 0 0px;">
                    <h3 style="color: #3a0ca3; font-size: 22px; margin-bottom: 10px;">
                        Pay Only {{ $data['promo']['discount_price'] }} instead of <span style="text-decoration: line-through">{{ $data['data']['amount'] ?? '₹100' }}</span>
                    </h3>
                    <div style="background: #D7DDFAFF; padding: 12px 25px; border-radius: 25px; display: inline-block; margin: 10px 0;">
                        <p style="color: #3a0ca3; font-size: 18px; font-weight: 700; margin: 0;">
                            ⭐ {{ $data['promo']['disc'] }} Instant Discount Applied ⭐
                        </p>
                    </div>
                </div>

            </div>

            <!-- Promo Code Section (if available) -->
            @if (!empty($data['promo']['code']))
            <div class="promo-box">
                <div class="promo-label">YOUR EXCLUSIVE PROMO CODE</div>
                <div class="promo-code">{{ $data['promo']['code'] }}</div>
                <p class="text-muted" style="margin-top: 10px;">
                    Apply this code during checkout to get your special discount
                </p>
            </div>
            @endif

            <!-- Templates Display - YOUR DESIGN -->
            @if (!empty($data['data']['templates']))
            <div class="template-section">
                <h3 class="template-section-title">Your Selected Templates</h3>
                @if (count($data['data']['templates']) == 1)
                @php $template = $data['data']['templates'][0]; @endphp
                <div class="single-template-layout">
                    <div class="card">
                        <a href="{{ $template['link'] ?? '#' }}" target="_blank">
                            <div class="image-wrapper" style="width: 70%; margin: 0 auto;">
                                <img src="{{ $template['image'] ?? '' }}" alt="{{ $template['title'] ?? '' }}">
                            </div>
                        </a>
                        <p class="card-title">{{ $template['title'] ?? '' }}</p>
                        <p class="card-price"> {{$data['promo']['discount_price']}}
                            <span style="text-decoration: line-through">{{ $template['amount'] ?? '0' }}</span> <span>one-time</span>
                        </p>
                        <ul>
                            <li><span style="color: #28A745; font-size: 15px;">✔</span> Watermark-free export of this design</li>
                            <li><span style="color: #28A745; font-size: 15px;">✔</span> No subscription required</li>
                            <li><span style="color: #28A745; font-size: 15px;">✔</span> Available Formats (PDF, PNG, JPG and MP4)</li>
                        </ul>
                    </div>
                </div>
                @else
                <div class="cards-wrapper">
                    @foreach ($data['data']['templates'] as $template)
                    <div class="card">
                        <a href="{{ $template['link'] ?? '#' }}" target="_blank">
                            <div class="image-wrapper">
                                <img src="{{ $template['image'] ?? '' }}" alt="{{ $template['title'] ?? '' }}">
                            </div>
                        </a>
                        <p class="card-title">{{ $template['title'] ?? '' }}</p>
                        <p class="card-price">
                            {{ $template['amount'] ?? '0' }} <span>one-time</span>
                        </p>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            <!-- Template Benefits -->
            <div class="template-benefits">
                <h3 class="text-primary text-center" style="margin-bottom: 20px;">Why Choose Crafty Art Templates?</h3>

                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 10px;">
                    <tr>
                        <td width="30" valign="top" style="padding: 8px 0;">
                            <span style="color: #28a745; font-weight: bold; font-size: 18px;">✓</span>
                        </td>
                        <td valign="top" style="padding: 8px 0 8px 10px;">
                            <strong>Professional Quality:</strong> Designed by industry experts
                        </td>
                    </tr>
                    <tr>
                        <td width="30" valign="top" style="padding: 8px 0;">
                            <span style="color: #28a745; font-weight: bold; font-size: 18px;">✓</span>
                        </td>
                        <td valign="top" style="padding: 8px 0 8px 10px;">
                            <strong>Fully Customizable:</strong> Easy to edit and personalize
                        </td>
                    </tr>
                    <tr>
                        <td width="30" valign="top" style="padding: 8px 0;">
                            <span style="color: #28a745; font-weight: bold; font-size: 18px;">✓</span>
                        </td>
                        <td valign="top" style="padding: 8px 0 8px 10px;">
                            <strong>Commercial License:</strong> Use for client projects
                        </td>
                    </tr>
                    <tr>
                        <td width="30" valign="top" style="padding: 8px 0;">
                            <span style="color: #28a745; font-weight: bold; font-size: 18px;">✓</span>
                        </td>
                        <td valign="top" style="padding: 8px 0 8px 10px;">
                            <strong>All Formats Included:</strong> PDF, PNG, JPG, MP4
                        </td>
                    </tr>
                    <tr>
                        <td width="30" valign="top" style="padding: 8px 0;">
                            <span style="color: #28a745; font-weight: bold; font-size: 18px;">✓</span>
                        </td>
                        <td valign="top" style="padding: 8px 0 8px 10px;">
                            <strong>Lifetime Access:</strong> Download anytime, anywhere
                        </td>
                    </tr>
                    <tr>
                        <td width="30" valign="top" style="padding: 8px 0;">
                            <span style="color: #28a745; font-weight: bold; font-size: 18px;">✓</span>
                        </td>
                        <td valign="top" style="padding: 8px 0 8px 10px;">
                            <strong>High Resolution:</strong> Print-ready quality
                        </td>
                    </tr>
                </table>
            </div>

            <!-- CTA Button -->
            <div class="text-center" style="margin: 25px 0 10px 0;">
                <h3 class="text-primary">Get Your Templates Now!</h3>
                <p class="text-dark" style="margin-bottom: 20px; font-size: 16px;">
                    Get instant access to professionally designed templates for only {{ $data['promo']['discount_price'] ?? '' }}
                </p>
                <a href="{{ $data['link'] ?? '#' }}"
                   class="btn btn-primary">
                    Buy Now - Only {{ $data['promo']['discount_price'] ?? '' }}!
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <h3 class="text-white">Crafty Art Team</h3>
        <p style="margin-bottom: 0px;">B - 815, IT Park, Opposite AR Mall, Uttran, Surat - 394105</p>

        <!-- Social Links -->
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td align="center" style="padding: 5px 0 0;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <!-- Instagram -->
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

                            <!-- Pinterest -->
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

                            <!-- YouTube -->
                            <td align="center" width="20%" style="padding: 10px 0;">
                                <a href="https://www.youtube.com/@craftyartgraphic7864" target="_blank" style="display: inline-block;">
                                    <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/tit/95c/jw8/youtube%20%281%29.png"
                                         alt="YouTube"
                                         width="28"
                                         height="28"
                                         style="display:block;border:0;outline:none;text-decoration:none; width: 28px; height: 28px; max-width: 28px; filter: brightness(0) invert(1);">
                                </a>
                            </td>

                            <!-- Facebook -->
                            <td align="center" width="20%" style="padding: 10px 0;">
                                <a href="https://www.facebook.com/craftyartapp/" target="_blank" style="display: inline-block;">
                                    <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/h63/d76/caw/facebook%20%281%29.png"
                                         alt="Facebook"
                                         width="28"
                                         height="28"
                                         style="display:block;border:0;outline:none;text-decoration:none; width: 28px; height: 28px; max-width: 28px; filter: brightness(0) invert(1);">
                                </a>
                            </td>

                            <!-- Twitter/X -->
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
                You are receiving this email because you are a CraftyArt user or signed up to receive our emails.
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