<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Premium Renewal Email</title>
    <style>
        body {
            background: #f6efe1;
            margin: 0;
            padding: 30px;
            /* font-family: 'Helvetica Neue', Arial, sans-serif; */
            font-family: Arial, Helvetica, sans-serif;

            display: flex;
            justify-content: center
        }

        .wrap {
            max-width: 720px;
            background: linear-gradient(180deg, #fff8ec, #f6e4c8);
            border-radius: 18px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden
        }

        .inner {
            padding: 42px;
            text-align: center;
            color: #3d2a14
        }

        h1 {
            font-size: 26px;
            margin: 0;
            color: #3d2a14;
            font-weight: bold;
        }

        p {
            font-size: 16px;
            line-height: 1.55;
            margin: 12px 0 0;
            color: #4b3b21
        }

        .big {
            font-size: 30px;
            font-weight: 700;
            color: #c17b11;
            margin-top: 20px
        }

        .highlight-box {
            background: #fff5df;
            padding: 18px;
            border-radius: 14px;
            border: 2px solid #f2d9ab;
            margin-top: 20px
        }

        .highlight-title {
            font-size: 20px;
            font-weight: 700;
            color: #8a5200;
            margin-bottom: 6px
        }

        /* Coupon section */
        .coupon-box {
            margin-top: 26px;
            padding: 22px;
            background: linear-gradient(135deg, #fff9f0, #fff1d9);
            border: 3px dashed #d9b57a;
            border-radius: 16px;
            display: inline-block;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            min-width: 260px;
        }

        .coupon-label {
            font-size: 14px;
            color: #8a6d37;
            margin-bottom: 6px;
            font-weight: 600
        }

        .coupon-code {
            font-size: 26px;
            font-weight: 800;
            color: #b06f00;
            margin-top: 4px;
            letter-spacing: 2px
        }

        .coupon-btn {
            margin-top: 14px;
            background: #c78c23;
            color: #fff;
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 700;
            display: inline-block;
            box-shadow: 0 6px 16px rgba(199, 140, 35, 0.28)
        }

        /* Benefits */
        .benefits {
            margin-top: 30px;
            text-align: left
        }

        .benefits-title {
            font-size: 20px;
            font-weight: 700;
            color: #8a5200;
            margin-bottom: 12px
        }

        .benefit-item {
            background: #fff;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 10px;
            border-left: 6px solid #c78c23;
            font-size: 15px
        }

        /* Footer */
        .footer {
            margin-top: 34px;
            font-size: 14px;
            color: #6d5836
        }

        @media(max-width:520px) {
            .inner {
                padding: 26px
            }

            h1 {
                font-size: 22px
            }

            .big {
                font-size: 26px
            }

            .coupon-code {
                font-size: 22px
            }

            .coupon-box {
                min-width: 200px
            }
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="inner">
            <h1 style="color: #c78c23; margin-bottom: 20px;">Your Plan Has Expired</h1>
              <p>Your creativity is still alive! ✨<br>
                All the amazing designs you created are waiting for your return.<br>
                Renew now and unlock premium templates, new design packs, and exclusive features.</p>

            <div class="highlight-box">
                <div class="highlight-title">Special Renewal Offer for You</div>
                  <p>A limited-time discount is activated on your account.<br>
                    The sooner you renew, the more benefits you get.</p>
            </div>

            <div class="coupon-box">
                <div class="coupon-label">Use this exclusive promo code</div>
                <div class="coupon-code">{{ $data['promo']['code'] }}</div>
                <a href="https://www.craftyartapp.com/plans" class="coupon-btn">Apply & Upgrade</a>
            </div>

            <div class="benefits">
                <div class="benefits-title">When you renew, you get:</div>
                <div class="benefit-item">🚀 Full access to 15,000+ premium templates</div>
                <div class="benefit-item">🖼️ High-resolution downloads — without watermark</div>
                <div class="benefit-item">✨ Exclusive festive design packs every week</div>
                <div class="benefit-item">🎨 Premium stickers, frames, and backgrounds</div>
                <div class="benefit-item">⚡ Faster export speed and advanced editing tools</div>
            </div>

            <p class="footer">🎁 Offer valid till <strong>{{ $data['promo']['expiry_date'] }}</strong> • For support, WhatsApp us at <strong>+91
                    98989 78207</strong></p>  
        </div>
    </div>
</body>

</html>