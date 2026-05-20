<!DOCTYPE html>
<html lang="hi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Crafty Art - Complete Your Subscription</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f9f0ff 0%, #f0e6ff 100%);
            font-family: "Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 20px 0;
            -webkit-text-size-adjust: none;
            text-size-adjust: none;
        }

        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 10px 30px rgba(120, 74, 226, 0.15);
            border-radius: 25px;
            overflow: hidden;
        }

        .section {
            width: 100%;
        }

        .rounded-top {
            border-radius: 25px 25px 0 0;
        }

        .rounded-bottom {
            border-radius: 0 0 25px 25px;
        }

        .bg-header-footer {
            background: linear-gradient(135deg, #6A11CB 0%, #2575FC 100%);
        }

        .bg-header-footer-light {
            background: linear-gradient(135deg, #8A2BE2 0%, #4B6CB7 100%);
        }

        .bg-white {
            background-color: #ffffff;
        }

        .bg-light-accent {
            background-color: #f8f9ff;
        }

        .content-wrapper {
            padding: 10px 55px;
        }

        .text-center {
            text-align: center;
        }

        .text-white {
            color: #ffffff;
        }

        .text-primary {
            color: #2D3748;
        }

        .text-accent {
            color: #4B6CB7;
        }

        .text-dark {
            color: #2a2a2a;
        }

        h1 {
            font-size: 30px;
            font-weight: 700;
            line-height: 1.2;
            margin: 0;
        }

        h3 {
            font-size: 15px;
            font-weight: 400;
            line-height: 1.4;
            margin: 0;
        }

        p {
            line-height: 1.5;
            margin: 0;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            text-align: center;
            font-weight: 700;
            border-radius: 50px;
            padding: 5px 30px;
            font-size: 14px;
            line-height: 30px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(75, 108, 183, 0.3);
        }

        .btn-primary {
            background: linear-gradient(135deg, #8A2BE2 0%, #4B6CB7 100%);
            color: #ffffff;
        }

        .social-icons {
            display: flex;
            justify-content: center;
            gap: 80px;
            padding: 10px 0;
            flex-wrap: wrap;
        }

        .social-icon img {
            width: 32px;
            height: auto;
            display: block;
            transition: transform 0.3s ease;
        }

        .footer-text {
            font-size: 14px;
            line-height: 1.4;
        }

        .promo-section {
            background: linear-gradient(135deg, #f0f4ff 0%, #e6eeff 100%);
            border-radius: 20px;
            padding: 25px;
            margin: 20px 0;
            text-align: center;
            box-shadow: 0 8px 25px rgba(75, 108, 183, 0.1);
            overflow: hidden;
        }

        .promo-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .promo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #8A2BE2 0%, #4B6CB7 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(75, 108, 183, 0.3);
        }

        .promo-icon span {
            color: white;
            font-size: 18px;
            font-weight: bold;
        }

        .promo-title {
            color: #4B6CB7;
            font-size: 18px;
            font-weight: bold;
        }

        .promo-description {
            color: #5A6C8B;
            font-size: 13px;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        .promo-box {
            background: linear-gradient(135deg, #ffffff 0%, #f5f8ff 100%);
            border: 2px dashed #8A2BE2;
            border-radius: 15px;
            padding: 12px 20px;
            margin: 0 auto 15px;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(75, 108, 183, 0.2);
        }

        .promo-code {
            font-size: 15px;
            font-weight: bold;
            color: #8A2BE2;
            letter-spacing: 3px;
            margin: 0;
            text-shadow: 0 2px 5px rgba(75, 108, 183, 0.2);
            font-family: 'Courier New', monospace;
        }

        .copy-btn {
            background: linear-gradient(135deg, #8A2BE2 0%, #4B6CB7 100%);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(75, 108, 183, 0.3);
        }

        .promo-note {
            color: #5A6C8B;
            font-size: 12px;
            font-style: italic;
            margin-top: 10px;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #e0e8ff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            box-shadow: 0 5px 15px rgba(75, 108, 183, 0.3);
        }

        .icon-circle img {
            width: 40px;
            height: auto;
        }

        .testimonial-review {
            background: linear-gradient(135deg, #ffffff 0%, #f5f8ff 100%);
            border: 2px solid #e0e8ff;
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 8px 25px rgba(75, 108, 183, 0.15);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .reviewer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 2px solid #e0e8ff;
        }

        .reviewer-avatar img {
            width: 30px;
            height: 30px;
            object-fit: contain;
        }

        .reviewer-details h4 {
            color: #4B6CB7;
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 4px 0;
        }

        .reviewer-details p {
            color: #5A6C8B;
            font-size: 13px;
            margin: 0;
        }

        .review-rating {
            text-align: right;
        }

        .stars {
            color: #FFC107;
            font-size: 18px;
            display: block;
            margin-bottom: 5px;
        }

        .review-content {
            margin: 15px 0;
        }

        .review-text {
            color: #4B6CB7;
            font-size: 15px;
            line-height: 1.6;
            font-style: italic;
            margin: 0;
            z-index: 1;
        }

        /* Template Section Styles */
        .template-section {
            padding: 20px 25px;
        }

        .template-title {
            text-align: center;
            color: #4B6CB7;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #8A2BE2;
        }

        .cards-wrapper {
            column-count: 2;
            column-gap: 22px;
            /* padding: 0 15px; */
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

        .card-title {
            margin: 10px 8px;
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

        /* Single Template Layout */
        .single-template {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            background: #fff;
            border-radius: 10px;
            margin: 15px;
            padding: 15px;
            border: 1px solid #e0e8ff;
            box-shadow: 0 4px 15px rgba(75, 108, 183, 0.08);
        }

        .single-template .image-block {
            flex: 0 0 160px;
        }

        .single-template .image-block img {
            width: 100%;
            border-radius: 8px;
        }

        .single-template .text-block {
            flex: 1;
        }

        .single-template .title {
            font-size: 15px;
            font-weight: 600;
            color: #4B6CB7;
            margin: 0 0 6px;
        }

        .single-template .price {
            font-size: 14px;
            font-weight: bold;
            color: #28A745;
            margin: 0 0 15px;
        }

        .single-template .price span {
            font-weight: normal;
            color: #666;
        }

        .single-template ul {
            list-style: none;
            padding: 0;
            margin-top: 15px;
            font-size: 13px;
            color: #4B6CB7;
            line-height: 1.6;
        }

        .single-template ul li {
            margin-bottom: 8px;
            /* padding-left: 20px; */
        }


        @media (max-width: 670px) {
            body {
                padding: 0;
            }

            .email-container {
                margin: 0;
                border-radius: 0;
            }

            .content-wrapper {
                padding: 15px;
            }

            h1 {
                font-size: 22px;
            }

            h3 {
                font-size: 13px;
            }

            .rounded-top,
            .rounded-bottom {
                border-radius: 0;
            }

            .btn {
                padding: 12px 25px;
                font-size: 14px;
            }

            .social-icons {
                gap: 25px;
            }

            .promo-section {
                padding: 20px;
            }

            .promo-code {
                font-size: 20px;
                letter-spacing: 2px;
            }

            .testimonial-review {
                padding: 20px;
            }

            .review-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .review-rating {
                text-align: left;
            }

            /* .cards-wrapper {
                column-count: 1;
            } */

            .single-template {
                flex-direction: column;
            }

            .single-template .image-block {
                flex: 0 0 auto;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
            style="background:linear-gradient(135deg, #8A2BE2 0%, #4B6CB7 100%);border-top-left-radius:12px;border-top-right-radius:12px;">
            <tr>
                <td align="center" style="padding:30px 55px 20px;">

                    <!-- Circle background with logo -->
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center"
                        style="margin:0 auto 10px;">
                        <tr>
                            <td align="center" valign="middle"
                                style="width:80px;height:80px;border-radius:50%;background:#e0e8ff;
                     box-shadow:0 5px 15px rgba(75, 108, 183, 0.3);text-align:center;">
                                <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/bvy/oe5/ff9/logo%404x.png"
                                    alt="Crafty Art" width="40" height="40"
                                    style="display:block;margin:0 auto;border:0;outline:none;text-decoration:none;">
                            </td>
                        </tr>
                    </table>

                    <!-- Greeting -->
                    <h1
                        style="color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:24px;font-weight:bold;margin:10px 0 0 0;">
                        नमस्ते क्रिएटर, {{ $data['userData']['name'] ?? '' }}
                    </h1>

                </td>
            </tr>
        </table>


        <!-- Main Content -->
        <div class="section bg-white">
            <div style="padding: 25px 20px 0px 20px">
                <h3 class="text-primary text-center">
                    @if ($data['type'] == 'template' && !empty($data['data']['templates']))
                        @if (count($data['data']['templates']) == 1)
                            आप <strong>{{ $data['data']['templates'][0]['title'] ?? '' }}</strong> लेने ही वाले थे — यह
                            वही टेम्पलेट है जो क्रिएटर्स को
                            <strong>प्रोफेशनल डिज़ाइन</strong> के साथ असीमित एक्सेस देता है।
                        @else
                            आप <strong>{{ $data['data']['templates'][0]['title'] ?? '' }}</strong> लेने ही वाले थे — यह
                            वही प्लान है जो क्रिएटर्स को
                            <strong>15,000+ Templates & Videos</strong> के साथ असीमित एक्सेस देता है।
                        @endif
                    @endif
                </h3>
            </div>

            <!-- Buy Now Button for Multiple Templates (Above templates) -->
            @if ($data['type'] == 'template' && !empty($data['data']['templates']) && count($data['data']['templates']) > 1)
                <div style="padding: 10px 20px 10px 20px">
                    <div class="text-center" style="margin-top: 12px;">
                        <a href="{{ $data['link'] ?? '#' }}"
                            style="color: white; text-decoration: none;" class="btn btn-primary" target="_blank">Buy
                            Now</a>
                    </div>
                </div>
            @endif

            <!-- Template Section -->
            @if ($data['type'] == 'template' && !empty($data['data']['templates']))
                <div class="template-section">

                    @if (count($data['data']['templates']) > 1)
                        <div class="cards-wrapper">
                            @foreach ($data['data']['templates'] as $item)
                                <div class="card">
                                    <a href="{{ $item['link'] ?? '#' }}" target="_blank">
                                        <div class="image-wrapper">
                                            <img src="{{ $item['image'] ?? '' }}" alt="{{ $item['title'] ?? '' }}">
                                        </div>
                                    </a>
                                    <p class="card-title">{{ $item['title'] ?? '' }}</p>
                                    <p class="card-price">
                                        {{ $item['amount'] ?? '0' }} <span>one-time</span>
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        @php $item = $data['data']['templates'][0]; @endphp
                        <div class="single-template">
                            <div class="image-block">
                                <a href="{{ $item['link'] ?? '#' }}" target="_blank">
                                    <img src="{{ $item['image'] ?? '' }}" alt="{{ $item['title'] ?? '' }}">
                                </a>
                            </div>
                            <div class="text-block">
                                <p class="title">{{ $item['title'] ?? '' }}</p>
                                <p class="price">
                                    {{ $item['amount'] ?? '0' }} <span>one-time</span>
                                </p>
                                <ul>
                                    <li><span style="color: #28A745; font-size: 15px;">✔</span> Watermark-free export of
                                        this design</li>
                                    <li><span style="color: #28A745; font-size: 15px;">✔</span> No subscription required
                                    </li>
                                    <li><span style="color: #28A745; font-size: 15px;">✔</span> Available Formats ( PDF,
                                        PNG, JPG and MP4 )</li>
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if ($data['type'] == 'template' && !empty($data['data']['templates']))
                @php $item = $data['data']['templates'][0]; @endphp
                <div style="padding: 0px 20px 25px 20px">
                    <h3 class="text-primary text-center">
                        अभी अपना ऑर्डर पूरा करें <strong>सिर्फ {{ $data['data']['amount'] }} में</strong> और तुरंत बचत
                        पाएं!
                    </h3>
                    <div class="text-center" style="margin-top: 12px;">
                        <a href="{{ $data['link'] ?? '#' }}" class="btn btn-primary"
                            style="color: white; text-decoration: none;" target="_blank">Buy Now</a>
                    </div>
                </div>
            @endif

            <div class="section bg-light-accent">
                <div class="content-wrapper">
                    <div class="testimonial-review">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <div class="reviewer-details">
                                    <h4 class="reviewer-name">विराट पटेल</h4>
                                    <p class="reviewer-title">( ग्राफिक डिज़ाइनर )</p>
                                </div>
                            </div>
                        </div>
                        <div class="review-content">
                            <p class="review-text">
                                "Crafty Art के साथ, हर डिज़ाइन में छुपा है आपकी क्रिएटिविटी का जादू!"
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-header-footer">
            <div class="section">
                <div class="text-center" style="padding: 20px 0 10px">
                    <p class="text-white"><strong>Crafty Art Team</strong></p>
                </div>
                <div class="text-center" style="padding-bottom: 20px">
                    <p class="text-white">
                        B - 815, IT Park, Opposite AR Mall, Uttran, Surat - 394105
                    </p>
                </div>
            </div>

            <!-- Social Links -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td align="center" style="padding: 20px 20px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td align="center" style="padding: 0 35px;">
                                    <a href="https://www.instagram.com/craftyart_invitation/" target="_blank">
                                        <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/8eo/pm2/e8i/instagram%20%282%29.png"
                                            alt="Instagram" width="32" height="32"
                                            style="display:block;border:0;outline:none;text-decoration:none;">
                                    </a>
                                </td>
                                <td align="center" style="padding: 0 35px;">
                                    <a href="https://in.pinterest.com/craftyart_official" target="_blank">
                                        <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/qxl/elc/7nd/pinterest%20%281%29.png"
                                            alt="Pinterest" width="32" height="32"
                                            style="display:block;border:0;outline:none;text-decoration:none;">
                                    </a>
                                </td>
                                <td align="center" style="padding: 0 35px;">
                                    <a href="https://www.youtube.com/@craftyartgraphic7864" target="_blank">
                                        <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/tit/95c/jw8/youtube%20%281%29.png"
                                            alt="YouTube" width="32" height="32"
                                            style="display:block;border:0;outline:none;text-decoration:none;">
                                    </a>
                                </td>
                                <td align="center" style="padding: 0 35px;">
                                    <a href="https://www.facebook.com/craftyartapp/" target="_blank">
                                        <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/h63/d76/caw/facebook%20%281%29.png"
                                            alt="Facebook" width="32" height="32"
                                            style="display:block;border:0;outline:none;text-decoration:none;">
                                    </a>
                                </td>
                                <td align="center" style="padding: 0 35px;">
                                    <a href="https://x.com/craftyartstudio" target="_blank">
                                        <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/leu/a25/pzr/twitter%20%281%29.png"
                                            alt="Twitter" width="32" height="32"
                                            style="display:block;border:0;outline:none;text-decoration:none;">
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <!-- Unsubscribe -->
            <div class="section rounded-bottom">
                <div class="content-wrapper text-center">
                    <p class="text-white footer-text" style="padding-bottom:10px;">
                        You are receiving this email because you are a CraftyArt user or signed up to receive our
                        emails. If you'd like to stop receiving these, you can safely <strong><a target="_blank"
                                href="https://www.craftyartapp.com/email-unsubscribe" rel="noopener noreferrer"
                                style="text-decoration: underline; color: #ffffff">unsubscribe</a></strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>