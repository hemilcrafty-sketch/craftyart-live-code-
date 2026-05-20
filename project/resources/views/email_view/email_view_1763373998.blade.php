<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Crafty Art - Complete Your Purchase</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f0f8ff 0%, #e6f7ff 100%);
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
            box-shadow: 0 10px 30px rgba(74, 144, 226, 0.15);
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

        /* New Color Scheme - Teal & Coral */
        .bg-header-footer {
            background: linear-gradient(135deg, #2D9596 0%, #265073 100%);
        }

        .bg-header-footer-light {
            background: linear-gradient(0deg, #ffffff 0%, #2D9596 100%);
        }

        .bg-white {
            background-color: #ffffff;
        }

        .bg-light-accent {
            background-color: #f0fdfa;
        }

        .bg-gradient {
            background: linear-gradient(135deg, #f0f8ff 0%, #e6f7ff 100%);
        }

        .content-wrapper {
            padding: 10px 55px;
        }

        .content-wrapper-sm {
            padding: 10px 55px;
        }

        .text-center {
            text-align: center;
        }

        .text-white {
            color: #ffffff;
        }

        .text-primary {
            color: #265073;
        }

        .text-accent {
            color: #FF6B6B;
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

        h2 {
            font-size: 17px;
            font-weight: 400;
            line-height: 1.4;
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
            padding: 10px 50px;
            font-size: 16px;
            line-height: 30px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(45, 149, 150, 0.3);
        }

        .btn-primary {
            background: linear-gradient(135deg, #2D9596 0%, #265073 100%);
            /* border: 2px solid #2D9596; */
            color: white;
        }

        /* .btn-primary:hover {
            background: linear-gradient(135deg, #265073 0%, #1e3d5c 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(45, 149, 150, 0.4);
        } */

        .social-icon img {
            width: 32px;
            height: auto;
            display: block;
            transition: transform 0.3s ease;
        }

        /* .social-icon:hover img {
            transform: scale(1.1);
        } */

        .footer-text {
            font-size: 14px;
            line-height: 1.4;
        }

        /* New Styles */
        .header-icon {
            width: 60px;
            height: auto;
            margin: 0 auto 10px;
            display: block;
        }

        .offer-badge {
            display: inline-block;
            background: #FF6B6B;
            color: white;
            padding: 6px 10px;
            border-radius: 50px;
            font-size: 12px;
            margin: 12px 0 10px 0;
            animation: pulse 2s infinite;
            box-shadow: 0 0 5px rgb(251, 60, 60);
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .promo-box {
            background: linear-gradient(135deg, #f0f8ff 0%, #e6f7ff 100%);
            border: 2px dashed #2D9596;
            border-radius: 15px;
            padding: 5px 10px;
            margin: 7px 0 20px 0px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(45, 149, 150, 0.1);
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }

        .promo-code {
            font-size: 15px;
            font-weight: bold;
            color: #2D9596;
            letter-spacing: 2px;
            margin: 5px 0;
            text-shadow: 0 2px 5px rgba(45, 149, 150, 0.2);
            padding: 0 10px;
        }

        .features-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .features-list li {
            padding: 5px 0;
            /* position: relative; */
            /* padding-left: 35px; */
            font-size: 15px;
            line-height: 1.6;
        }

        /* .features-list li:before {
            content: "✓";
            color: #2D9596;
            font-weight: bold;
            position: absolute;
            left: 0;
            font-size: 18px;
        } */

        .countdown {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
        }

        .countdown-item {
            background: linear-gradient(135deg, #2D9596 0%, #265073 100%);
            color: white;
            padding: 12px;
            border-radius: 10px;
            min-width: 60px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(45, 149, 150, 0.3);
        }

        .countdown-number {
            font-size: 22px;
            font-weight: bold;
        }

        .countdown-label {
            font-size: 12px;
        }

        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #e0e0e0, transparent);
            margin: 25px 0;
        }

        .testimonial {
            background: linear-gradient(135deg, #f0fdfa 0%, #e6f7ff 100%);
            border-left: 5px solid #2D9596;
            padding: 20px;
            margin: 20px 0;
            font-style: italic;
            border-radius: 0 10px 10px 0;
            box-shadow: 0 5px 15px rgba(45, 149, 150, 0.1);
        }

        .guarantee-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
            padding: 15px;
            background: linear-gradient(135deg, #f0f8ff 0%, #e6f7ff 100%);
            border-radius: 10px;
        }

        .guarantee-badge img {
            width: 40px;
            height: auto;
        }

        .price-comparison {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin: 15px 0;
        }

        .original-price {
            text-decoration: line-through;
            color: #888;
            font-size: 18px;
        }

        .discounted-price {
            color: #FF6B6B;
            font-size: 24px;
            font-weight: bold;
        }

        .savings-badge {
            background-color: #ffebee;
            color: #FF6B6B;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #ccebe4;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            box-shadow: 0 5px 15px #556c66;
        }

        .icon-circle img {
            width: 40px;
            height: auto;
        }

        .features-section {
            background: linear-gradient(135deg, #f8fdff 0%, #f0f9ff 100%);
            border-radius: 15px;
            padding: 25px 25px 5px 25px;
            margin: 20px 0;
            box-shadow: 0 5px 15px rgba(45, 149, 150, 0.1);
        }

        .features-title {
            text-align: center;
            color: #265073;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2D9596;
        }

        /* RESPONSIVE STYLES */
        @media (max-width: 670px) {
            body {
                padding: 0;
            }

            .email-container {
                margin: 0;
                border-radius: 0;
            }

            .content-wrapper,
            .content-wrapper-sm {
                padding: 15px;
            }

            h1 {
                font-size: 22px;
            }

            h2 {
                font-size: 14px;
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

            .countdown {
                gap: 8px;
            }

            .countdown-item {
                min-width: 50px;
                padding: 10px 5px;
            }

            .countdown-number {
                font-size: 18px;
            }

            .countdown-label {
                font-size: 10px;
            }

            .promo-code {
                font-size: 18px;
                letter-spacing: 1px;
            }

            .features-section {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <!-- Header Section -->
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
            style="background:linear-gradient(0deg, #ffffff 0%, #2D9596 100%);border-top-left-radius:12px;border-top-right-radius:12px;">
            <tr>
                <td align="center" style="padding:30px 55px 20px;">

                    <!-- Circle background with logo -->
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center"
                        style="margin:0 auto 10px;">
                        <tr>
                            <td align="center" valign="middle"
                                style="width:80px;height:80px;border-radius:50%;background:#ccebe4;
                     box-shadow:0 5px 15px #556c66;text-align:center;">
                                <img src="https://261d8b77ca.imgdist.com/pub/bfra/svzus9kn/bvy/oe5/ff9/logo%404x.png"
                                    alt="Crafty Art" width="40" height="40"
                                    style="display:block;margin:0 auto;border:0;outline:none;text-decoration:none;">
                            </td>
                        </tr>
                    </table>

                    <!-- Greeting -->
                    <h1
                        style="color:#005455;font-family:Arial,Helvetica,sans-serif;font-size:24px;font-weight:bold;margin:10px 0 0 0;">
                        नमस्ते क्रिएटर, {{ $data['userData']['name'] ?? '' }}
                    </h1>

                </td>
            </tr>
        </table>


        <!-- Main Content -->
        <div class="section bg-white">
            <div style="padding: 15px 20px 0px 20px">
                <h3 class="text-primary text-center">
                    आप
                    <strong>Crafty Art Bundle </strong>लेने ही वाले थे — और यह वही पैक है जो क्रिएटर्स को
                    <strong>15,000+ Templates & Videos</strong> के साथ कम खर्च में ज़्यादा रिटर्न देता है।
                </h3>
            </div>

            <!-- Features Section -->
            <div class="content-wrapper">
                <div class="features-section">
                    <div class="features-title">🎨 Crafty Art Bundle के साथ आपको मिलेगा:</div>
                    <ul class="features-list text-primary">
                        <li>⭐ डिज़ाइन टाइम 70–80% तक कम</li>
                        <li>⭐ कम मेहनत में ज़्यादा आउटपुट</li>
                        <li>⭐ क्लाइंट वर्क और कंटेंट क्वालिटी दोनों में तगड़ा सुधार</li>
                        <li>⭐ 15,000+ प्रोफेशनल टेम्पलेट्स</li>
                        <li>⭐ HD वीडियो और मोशन ग्राफिक्स</li>
                    </ul>
                </div>
            </div>
            <div style="padding: 10px 20px 25px 20px">
                <h3 class="text-primary text-center">
                    अभी अपना ऑर्डर पूरा करें
                    <strong>सिर्फ {{ $data['data']['price'] }} में ( पहले {{ $data['data']['actual_price'] }} )</strong>
                    और तुरंत बचत पाएं!
                </h3>
            </div>
            <div class="section bg-light-accent">
                <div class="content-wrapper">
                    <div class="testimonial">
                        <p class="text-primary">
                            "हर डिज़ाइनर को एक साथी चाहिए — मेरे लिए वो है Crafty Art!"
                        </p>
                        <p class="text-primary" style="margin-top: 10px">
                            <strong>- राहुल शर्मा, ग्राफिक डिज़ाइनर</strong>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Call to Action -->
            <div class="section bg-white" style="padding: 20px 0px 10px 0px">
                <p class="text-accent text-center" style="font-weight: bold;">
                    आपका डिस्काउंट अभी भी एक्टिव है, लेकिन <strong>सीमित समय के लिए।</strong>
                </p>
                <div class="text-center" style="padding: 10px 0px;">
                    <a href="{{ $data['link'] ?? '#' }}" class="btn btn-primary" style="color: white; text-decoration: none;">अभी खरीदें</a>
                </div>
            </div>

            <!-- Button & Promo Code -->
            @if (!empty($data['promo']['code']))
                <div class="section bg-white text-center">
                    <div style="padding: 20px 0 0">
                        <p class="text-dark">
                            अपना विशेष डिस्काउंट पाने के लिए चेकआउट के दौरान इस प्रोमो कोड का उपयोग करें
                        </p>
                    </div>

                    <div class="promo-box">
                        <p class="promo-code">{{ $data['promo']['code'] }}</p>
                    </div>
                </div>
            @endif
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
                            You are receiving this email because you are a CraftyArt user or
                            signed up to receive our emails. If you'd like to stop receiving
                            these, you can safely
                            <strong><a target="_blank" href="https://www.craftyartapp.com/email-unsubscribe"
                                    rel="noopener noreferrer"
                                    style="text-decoration: underline; color: #ffffff">unsubscribe</a></strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
</body>

</html>