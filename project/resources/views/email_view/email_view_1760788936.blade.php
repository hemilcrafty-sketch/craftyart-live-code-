<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diwali Offer</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #F6F2F4;
            font-family: Arial, Helvetica, sans-serif;
        }
        .email-wrapper {
            max-width: 560px;
            margin: 40px auto;
            background: #D9A9AD;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            color: #3B1F2B;
        }
        .content {
            text-align: center;
            padding: 40px 30px 30px;
            background: #CD92A5;
            border-radius: 20px;
            position: relative;
        }
        .mandala-left,
        .mandala-right {
            position: absolute;
            width: 120px;
            height: 120px;
            opacity: 0.2;
        }
        .mandala-left {
            top: -10px;
            left: -10px;
        }
        .mandala-right {
            bottom: -10px;
            right: -10px;
            transform: rotate(180deg);
        }
        h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
            color: #5A2C3B;
        }
        h1 {
            font-size: 45px;
            font-weight: 700;
            margin: 15px 0 0px;
            font-family: "Brush Script MT", cursive;
            color: #622438;
        }
        p {
            font-size: 15px;
            margin: 10px 0;
            color: #472634;
        }
        .promo-box {
            display: inline-block;
            background: #fff;
            border: 2px dashed #FFCEDA;
            padding: 10px 28px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            color: white;
            background-color: #8B3A4D;
            margin: 3px 0px 10px;
        }
        .btn {
            display: inline-block;
            background: #fff;
            color: #8B3A4D;
            padding: 12px 40px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            margin-top: 10px;
            border: 1px solid #8B3A4D;
        }
        .btn:hover {
            background: #8B3A4D;
            color: #fff;
        }
        .note {
            font-size: 13px;
            color: #5C3942;
            margin-top: 25px;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #744D57;
            padding: 20px;
            background: #F2D5D9;
            border-top: 1px solid #E3B7BB;
        }
        .footer a {
            color: #8B3A4D;
            text-decoration: none;
            font-weight: bold;
        }
        @media (max-width: 480px) {
            .content {
                padding: 30px 20px;
            }
            h1 {
                font-size: 26px;
            }
            h2 {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="content">
            <div style="margin: 0px 0px 30px 0px;">
                <h2>Your plan has been expired</h2>
            </div>
            <p style="color: white;">Unlock premium templates, design tools, and assets at a discounted price.</p>
            {{-- <h5>( Limited-Time Offer! )</h5> --}}
            <h1>Celebrate</h1>
            <p style="color: white; font-family: Arial, Helvetica, sans-serif; font-size: 28px;margin-bottom: 30px;">
                Diwali with <span style="color: #622438; font-weight: bold;">{{ $data['promo']['disc'] }}%</span>
                OFF!</p>
            <p>Hurry, offer ends soon! <br><span style="color: white;">Use the code below at checkout.</span></p>
            @if (!empty($data['promo']['code']))
                <div class="promo-box">{{ $data['promo']['code'] }}</div>
            @endif
            <div>
                <a href="https://www.craftyartapp.com/plans" class="btn">UPGRADE TO PRO</a>
            </div>
            @if (!empty($data['promo']['expiry_date']))
            <p class="note">
                ✨ This promo code is valid until
                <strong>{{ $data['promo']['expiry_date'] }}</strong>.
            </p>
            @endif
        </div>
        <div class="footer">
            Need help? WhatsApp us at
            <a href="#">+91 98989 78207</a>
        </div>
    </div>
</body>
</html>