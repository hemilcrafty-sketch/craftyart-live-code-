<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Email Template</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #F5F7FF;
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
        }

        a {
            color: #fff !important;
            text-decoration: none !important;
        }

        .container {
            max-width: 510px;
            margin: 20px auto;
            background: #FFFFFF;
            border-radius: 16px;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(180deg, #ec9ba9, #FDFDFF);
            padding: 35px 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 26px;
            color: #ff2d77;
            font-weight: 700;
        }

        .content {
            padding: 20px 24px 0px;
            text-align: center;
        }

        .content-btn {
            text-align: center;
        }

        .content p {
            margin: 0;
            font-size: 16px;
            line-height: 1.6;
        }

        /* ===== Card Grid ===== */
        .cards-wrapper {
            column-count: 2;
            column-gap: 22px;
            padding: 0 15px;
        }

        .card {
            flex: 0 0 calc(50% - 9px);
            background: #fff;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 15px;
            border: 1px dotted silver;
            padding: 5px;
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
        }

        .card-title {
            margin: 10px 8px;
            font-size: 14px;
            font-weight: 600;
            color: #222;
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

        .btn {
            display: inline-block;
            background: #ff478e;
            color: #fff;
            padding: 12px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            margin: 15px 0;
        }

        .footer {
            padding: 10px;
            font-size: 14px;
            color: #555;
            text-align: center;
            background: #FAFAFA;
        }

        .footer a {
            color: #ff478e !important;
            text-decoration: none;
            font-weight: bold;
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
            border: 1px dotted silver;
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
            color: #222;
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
            color: #333;
            line-height: 1.6;
        }

        .promo-code {
            text-align: center;
            font-size: small;
            margin-top: 15px;
        }

        .code {
            border: 2px dotted #ff478e;
            padding: 10px 35px;
            background-color: #ffdcea;
            color: #ff478e;
            font-weight: bolder;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Hi {{ $data['userData']['name'] ?? '' }}</h1>
        </div>

        <div class="content">
            <p style="font-weight: 700;">
                {{-- @if ($data['type'] == 'template' && !empty($data['data']['templates']))
                    @if (count($data['data']['templates']) == 1)
                        You were about to grab the
                        <strong>{{ $data['data']['templates'][0]['title'] ?? '' }}</strong>
                        but didn't finish checkout.
                    @else
                        You were about to grab the
                        <strong>Templates & Videos</strong>
                        but didn't finish checkout.
                    @endif
                @else
                    We noticed you were about to grab the
                    <strong>Crafty Art Bundle (15,000+ Templates & Videos)</strong>
                    but didn't finish checkout.
                @endif --}}
                Still thinking? Here’s why creators love Crafty Art 💎
            </p>
        </div>

        @if ($data['type'] == 'template')

            @if (!empty($data['data']['amount']) && count($data['data']['templates']) > 1)
                <div class="content">
                    <p>
                        Grab it now for
                        <strong>{{ $data['data']['amount'] }} Only</strong>
                    </p>
                    <a href="{{ $data['link'] ?? '#' }}" target="_blank" class="btn">Buy Now</a>
                </div>
            @endif

        @endif

        <!-- Templates Section -->
        @if ($data['type'] == 'template')
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
                            <h5 style="margin-top: 30px;">Here’s what you’ll get with the bundle:</h5>
                            <li>✔ 15,000+ ready-to-edit templates & videos</li>
                            <li>✔ Easy editing (no costly software needed)</li>
                            <li>✔ 100% resell rights – keep all your profits</li>
                        </ul>
                    </div>
                </div>
            @endif
        @endif

        <div class="content">
            @if (!empty($data['data']['amount']))
                <p class="offer">
                    Grab it now for
                    <strong>{{ $data['data']['amount'] }} only</strong>
                </p>
            @endif
            @if ($data['type'] == 'plan' && !empty($data['data']['actual_price']) && !empty($data['data']['price']))
                <p class="offer">
                    Grab it now for
                    <strong>{{ $data['data']['price'] }} only (originally
                        {{ $data['data']['actual_price'] }})</strong> &#128540;
                </p>
            @endif
            {{-- <p>
                Grab it now for ₹497 only: [Checkout Link]
            </p> --}}
            <a href="{{ $data['link'] ?? '#' }}" target="_blank" class="btn">Buy Now</a>
        </div>

        @if(!empty($data['promo']['code']))
        <div class="promo-code">
            <span>Use Code at checkout</span>
            <div style="margin: 20px 0px 20px;">
                <span class="code">{{ $data['promo']['code'] }}</span>
            </div>
        </div>
        @endif


        <h6 style="text-align: center; font-weight: normal; margin-top: 50px;"> <span
                style="border-radius: 20px; text-align: center;padding: 10px; color: #ff478e; border: 1px dotted silver;">Remember,
                this launch offer is limited! 🤩</span></h6>

        <div class="footer">
            Need help? WhatsApp us at <a href="tel:+919898972007">+91 98989 78207</a>
        </div>
    </div>
</body>

</html>