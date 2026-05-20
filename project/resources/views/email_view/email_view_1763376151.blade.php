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
            background: linear-gradient(135deg, #7B2CBF 0%, #5A189A 100%);
        }

        .bg-header-footer-light {
            background: linear-gradient(0deg, #ffffff 0%, #7B2CBF 100%);
        }

        .bg-white {
            background-color: #ffffff;
        }

        .bg-light-accent {
            background-color: #f8f0ff;
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
            color: #5A189A;
        }

        .text-accent {
            color: #5A189A;
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
            padding: 10px 50px;
            font-size: 16px;
            line-height: 30px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(123, 44, 191, 0.3);
        }

        .btn-primary {
            background: linear-gradient(135deg, #7B2CBF 0%, #5A189A 100%);
            border: 2px solid #7B2CBF;
            color: #ffffff;
        }

        /* .btn-primary:hover {
            background: linear-gradient(135deg, #5A189A 0%, #3C096C 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(123, 44, 191, 0.4);
        } */

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

        /*
        .social-icon:hover img {
            transform: scale(1.1);
        } */

        .footer-text {
            font-size: 14px;
            line-height: 1.4;
        }

        .promo-section {
            background: linear-gradient(135deg, #f9f0ff 0%, #f0e6ff 100%);
            border-radius: 20px;
            padding: 25px;
            margin: 20px 0;
            text-align: center;
            box-shadow: 0 8px 25px rgba(123, 44, 191, 0.1);
            border: 2px dotted #7B2CBF;
            /* position: relative; */
            overflow: hidden;
        }

        /* .promo-section:before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(123, 44, 191, 0.1), transparent);
            animation: shine 3s infinite;
        } */

        @keyframes shine {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }

            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
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
            background: linear-gradient(135deg, #7B2CBF 0%, #5A189A 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(123, 44, 191, 0.3);
        }

        .promo-icon span {
            color: white;
            font-size: 18px;
            font-weight: bold;
        }

        .promo-title {
            color: #5A189A;
            font-size: 18px;
            font-weight: bold;
        }

        .promo-description {
            color: #7B5E92;
            font-size: 13px;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        .promo-box {
            background: linear-gradient(135deg, #ffffff 0%, #f8f5ff 100%);
            border: 2px dashed #7B2CBF;
            border-radius: 15px;
            padding: 12px 20px;
            margin: 0 auto 15px;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(123, 44, 191, 0.2);
            /* position: relative; */
        }

        .promo-code {
            font-size: 15px;
            font-weight: bold;
            color: #7B2CBF;
            letter-spacing: 3px;
            margin: 0;
            text-shadow: 0 2px 5px rgba(123, 44, 191, 0.2);
            font-family: 'Courier New', monospace;
        }

        .copy-btn {
            background: linear-gradient(135deg, #7B2CBF 0%, #5A189A 100%);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(123, 44, 191, 0.3);
        }

        /* .copy-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(123, 44, 191, 0.4);
        } */

        .promo-note {
            color: #7B5E92;
            font-size: 12px;
            font-style: italic;
            margin-top: 10px;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #e6d4f5;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            box-shadow: 0 5px 15px #7B5E92;
        }

        .icon-circle img {
            width: 40px;
            height: auto;
        }

        .testimonial-review {
            background: linear-gradient(135deg, #ffffff 0%, #f8f5ff 100%);
            border: 2px solid #e6d4f5;
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 8px 25px rgba(123, 44, 191, 0.15);
            /* position: relative; */
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
            border: 2px solid #e6d4f5;
        }

        .reviewer-avatar img {
            width: 30px;
            height: 30px;
            object-fit: contain;
        }

        .reviewer-details h4 {
            color: #5A189A;
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 4px 0;
        }

        .reviewer-details p {
            color: #7B5E92;
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
            color: #5A189A;
            font-size: 15px;
            line-height: 1.6;
            font-style: italic;
            margin: 0;
            /* position: relative; */
            z-index: 1;
        }

        .features-section {
            background: linear-gradient(135deg, #faf5ff 0%, #f5edff 100%);
            border-radius: 20px;
            padding: 18px 30px 5px;
            margin: 20px 0;
            box-shadow: 0 10px 30px rgba(123, 44, 191, 0.1);
            border: 1px solid #e6d4f5;
        }

        .features-title {
            text-align: center;
            color: #5A189A;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #7B2CBF;
            /* position: relative; */
        }

        /* .features-title:after {
            content: "";
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(135deg, #7B2CBF 0%, #00B4D8 100%);
            border-radius: 2px;
        } */

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(123, 44, 191, 0.08);
            transition: all 0.3s ease;
            border: 1px solid #f0e6ff;
            /* position: relative; */
            overflow: hidden;
        }

        /* .feature-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(123, 44, 191, 0.15);
            border-color: #7B2CBF;
        } */

        /* .feature-item:before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #7B2CBF 0%, #5A189A 100%);
        } */

        .feature-icon {
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .feature-icon img {
            width: 25px;
            height: 25px;
            filter: brightness(0) invert(1);
        }

        .feature-content {
            flex: 1;
        }

        .feature-heading {
            color: #5A189A;
            font-size: 16px;
            font-weight: normal;
            margin: 0;
            line-height: 1.4;
        }

        .feature-badge {
            display: flex;
            justify-content: center;
            gap: 8px;
            color: #7B2CBF;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 15px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .feature-badge .star {
            color: #FFC107;
            font-size: 16px;
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

            .features-section {
                padding: 15px;
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

            .features-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .feature-item {
                padding: 15px;
            }

            .feature-icon {
                width: 45px;
                height: 45px;
            }

            .feature-icon img {
                width: 22px;
                height: 22px;
            }

            .feature-badge {
                font-size: 12px;
                padding: 6px 12px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
            style="background:linear-gradient(0deg, #ffffff 0%, #7B2CBF 100%);border-top-left-radius:12px;border-top-right-radius:12px;">
            <tr>
                <td align="center" style="padding:30px 55px 20px;">

                    <!-- Circle background with logo -->
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

                    <!-- Greeting -->
                    <h1
                        style="color:#5A189A;font-family:Arial,Helvetica,sans-serif;font-size:24px;font-weight:bold;margin:10px 0 0 0;">
                        नमस्ते क्रिएटर, {{ $data['userData']['name'] ?? '' }}
                    </h1>

                </td>
            </tr>
        </table>


        <!-- Main Content -->
        <div class="section bg-white">
            <div style="padding: 15px 20px 0px 20px">
                <h3 class="text-primary text-center">
                    आप <strong>Crafty Art Subscription</strong> लेने ही वाले थे — यह वही प्लान है जो क्रिएटर्स को
                    <strong>15,000+ Templates & Videos</strong> के साथ असीमित एक्सेस देता है।
                </h3>
            </div>

            <!-- Features Section -->
            <div class="content-wrapper">
                <div class="features-section">
                    <div class="feature-badge">
                        Subscription के साथ आपको मिलेगा:
                    </div>

                    <div class="features-grid">
                        <div class="feature-item">
                            <div class="feature-content">
                                <h4 class="feature-heading">★ डिज़ाइन टाइम 70–80% तक कम</h4>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-content">
                                <h4 class="feature-heading">★ कम मेहनत में ज़्यादा आउटपुट</h4>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-content">
                                <h4 class="feature-heading">★ क्लाइंट वर्क और कंटेंट क्वालिटी दोनों में तगड़ा सुधार</h4>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-content">
                                <h4 class="feature-heading">★ 15,000+ प्रोफेशनल टेम्पलेट्स</h4>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-content">
                                <h4 class="feature-heading">★ HD वीडियो और मोशन ग्राफिक्स</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="padding: 10px 20px 25px 20px">
                <h3 class="text-primary text-center">
                    अभी अपना सब्सक्रिप्शन शुरू करें <strong>सिर्फ {{ $data['data']['price'] }} में ( पहले
                        {{ $data['data']['actual_price'] }} )</strong> और तुरंत बचत पाएं!
                </h3>
            </div>

            <div class="section bg-light-accent">
                <div class="content-wrapper">
                    <div class="testimonial-review">
                        <div class="review-header">
                            <div class="reviewer-info">

                                <div class="reviewer-details" style="display: flex;">
                                    <h4 class="reviewer-name">प्रिया जोशी</h4>
                                    <p class="reviewer-title" style="margin-left: 10px;">( ग्राफिक डिज़ाइनर )</p>
                                </div>
                            </div>
                        </div>
                        <div class="review-content">
                            <p class="review-text">
                                "Crafty Art सिर्फ एक टूल नहीं, यह हर क्रिएटर का सीक्रेट वेपन है!"
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="section bg-white" style="padding: 20px 0px 10px 0px">
                <p class="text-accent text-center" style="font-weight: bold;">
                    आपका डिस्काउंट अभी भी एक्टिव है, लेकिन <strong>सीमित समय के लिए।</strong>
                </p>
                <div class="text-center" style="padding: 10px 0px;">
                    <a href="{{ $data['link'] ?? '#' }}" class="btn btn-primary"
                        style="color: white; text-decoration: none;">अभी सब्सक्राइब करें</a>
                </div>
            </div>


            @if (!empty($data['promo']['code']))
                <div class="content-wrapper">
                    <div class="promo-section">
                        <p class="promo-description">
                            अपना एक्सक्लूसिव डिस्काउंट पाने के लिए चेकआउट के दौरान इस प्रोमो कोड का उपयोग करें
                        </p>

                        <div class="promo-box">
                            <p class="promo-code">{{ $data['promo']['code'] }}</p>
                        </div>
                    </div>
                </div>
            @endif
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