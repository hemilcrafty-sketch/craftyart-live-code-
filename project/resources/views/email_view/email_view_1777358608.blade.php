<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>CraftyArt Mail</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background: #eef1f5;
            font-family: Arial, Helvetica, sans-serif;
        }

        table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .wrapper {
            width: 100%;
            padding: 35px 0;
        }

        .main {
            width: 620px;
            background: #ffffff;
            border-radius: 26px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
        }

        .topbar {
            height: 7px;
            background: linear-gradient(to right, #5865f7, #18d5c2);
        }

        .inner {
            padding: 34px;
        }

        .logo {
            width: 44px;
            height: 44px;
            background: linear-gradient(to bottom right, #5865f7, #18d5c2);
            border-radius: 12px;
            color: #ffffff;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
        }

        .heading {
            font-size: 38px;
            font-weight: 700;
            color: #5566f8;
            line-height: 48px;
            padding-top: 18px;
        }

        .desc {
            font-size: 16px;
            line-height: 30px;
            color: #475569;
            padding-top: 16px;
        }

        .brand {
            color: #5566f8;
            font-weight: 700;
        }

        .card {
            margin-top: 30px;
            background: #f8fafc;
            border: 1px solid #e8edf3;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
        }

        .cardtitle {
            font-size: 12px;
            letter-spacing: 2px;
            font-weight: 700;
            color: #94a3b8;
            padding-bottom: 18px;
            text-transform: uppercase;
        }

        .label {
            font-size: 14px;
            font-weight: 700;
            color: #64748b;
            padding-bottom: 6px;
        }

        .value {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            padding-bottom: 18px;
        }

        .btnwrap {
            padding-top: 30px;
            padding-bottom: 30px;
            text-align: center;
        }

        .btn {
            display: inline-block;
            padding: 16px 42px;
            border-radius: 40px;
            background: linear-gradient(to right, #5865f7, #18d5c2);
            color: #ffffff;
            font-size: 19px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 12px 22px rgba(88, 101, 247, 0.22);
        }

        .line {
            height: 1px;
            background: #edf1f5;
            margin: 10px 0 38px;
        }

        .title {
            font-size: 22px;
            font-weight: 700;
            text-align: center;
            color: #0f172a;
            padding-bottom: 24px;
        }

        .boxbtn {
            width: 31%;
            border: 1px solid #dbe4ec;
            border-radius: 12px;
            padding: 14px 0;
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            color: #334155;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 12px;
        }

        .space {
            width: 1%;
            display: inline-block;
        }

        .tutorial {
            margin-top: 22px;
            background: #f2f6ff;
            border: 1px solid #e4ebff;
            border-radius: 18px;
            padding: 24px;
            text-align: center;
        }

        .ttitle {
            font-size: 17px;
            font-weight: 700;
            padding-bottom: 18px;
            color: #111827;
        }

        .lang {
            display: inline-block;
            padding: 10px 22px;
            border: 1px solid #c8d0ff;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 700;
            color: #5566f8;
            text-decoration: none;
            margin: 4px;
            background: #ffffff;
        }

        .contact {
            margin-top: 34px;
            border: 2px dashed #dce4ed;
            border-radius: 18px;
            padding: 24px;
        }

        .ctitle {
            font-size: 24px;
            font-weight: 700;
            padding-bottom: 24px;
            color: #111827;
        }

        mini {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #94a3b8;
            text-transform: uppercase;
            display: block;
            padding-bottom: 6px;
        }

        .info {
            font-size: 17px;
            font-weight: 700;
            color: #5566f8;
            padding-bottom: 20px;
        }

        .dark {
            color: #111827;
        }

        .footerline {
            height: 1px;
            background: #edf1f5;
            margin: 36px 0 26px;
        }

        .footer {
            text-align: center;
            font-size: 16px;
            line-height: 30px;
            color: #64748b;
            font-style: italic;
        }

        .team {
            text-align: center;
            padding-top: 12px;
            font-size: 17px;
            font-weight: 700;
            color: #111827;
        }

        .boxbtn:hover {
            background-color: color-mix(in oklab, #5961f8 5%, transparent);
            border-color: #5961f8;
            color: #5961f8;
            transition: all 0.3s ease;
        }

        .lang:hover {
            background-color: #5961f8;
            color: #ffffff;
            transition: all 0.3s ease;
        }

        @media only screen and (max-width:650px) {
            .main {
                width: 95% !important;
            }

            .inner {
                padding: 24px !important;
            }

            .heading {
                font-size: 30px !important;
                line-height: 40px !important;
            }

            .boxbtn {
                width: 100% !important;
                margin-bottom: 10px !important;
            }

            .space {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <table width="100%" class="wrapper">
        <tr>
            <td align="center">

                <table class="main">

                    <tr>
                        <td class="topbar"></td>
                    </tr>

                    <tr>
                        <td class="inner">

                            <table width="100%">
                                <tr>
                                    <td>

                                        <table class="logo">
                                            <tr>
                                                <td align="center">
                                                    <!-- icon here -->
                                                    ✦
                                                </td>
                                            </tr>
                                        </table>

                                        <div class="heading">Dear {{$data['userData']['name']}},</div>

                                        <div class="desc">
                                            We’re excited to welcome you to the
                                            <span class="brand">CraftyArt</span> family.
                                        </div>

                                        <div class="card">

                                            <div class="cardtitle">Here are your login details:</div>

                                            <div class="label">Login ID (Email):</div>
                                            <div class="value">{{$data['userData']['email']}}</div>

                                            <div class="label">Password:</div>
                                            <div class="value">{{$data['userData']['password']}}</div>

                                        </div>

                                        <div class="btnwrap">
                                            <a href="https://www.craftyartapp.com/login" class="btn">Login to
                                                CraftyArt</a>
                                        </div>

                                        <div class="line"></div>

                                        <div class="title">Explore Invitation</div>

                                        <a href="https://www.craftyartapp.com/templates/invitation/wedding"
                                            class="boxbtn">Wedding</a>
                                        <span class="space"></span>
                                        <a href="https://www.craftyartapp.com/templates/invitation/baby-shower"
                                            class="boxbtn">Baby Shower</a>
                                        <span class="space"></span>
                                        <a href="https://www.craftyartapp.com/templates/invitation/birthday"
                                            class="boxbtn">Birthday</a>

                                        <br>

                                        <a href="https://www.craftyartapp.com/templates/invitation/puja"
                                            class="boxbtn">Puja & Religious</a>
                                        <span class="space"></span>
                                        <a href="https://www.craftyartapp.com/templates/invitation/engagement"
                                            class="boxbtn">Engagement</a>

                                        <div class="tutorial">

                                            <div class="ttitle">Here is your step-by-step tutorial guide</div>

                                            <a href="https://www.youtube.com/watch?v=xduAenQa0vM"
                                                class="lang">Gujarati</a>
                                            <a href="https://www.youtube.com/watch?v=YkOxjALJWsc" class="lang">Hindi</a>
                                            <a href="https://www.youtube.com/watch?v=1YhDQK48djY&t=6s"
                                                class="lang">Marathi</a>

                                        </div>

                                        <div class="contact">

                                            <div class="ctitle">Contact us anytime</div>

                                            <mini>Email</mini>
                                            <div class="info">support@craftyartapp.com</div>

                                            <mini>Phone / WhatsApp</mini>
                                            <div class="info dark">+91 98989 78207</div>

                                        </div>

                                        <div class="footerline"></div>

                                        <div class="footer">
                                            We can’t wait to see the beautiful designs you create with CraftyArt!
                                        </div>

                                        <div class="team">
                                            The CraftyArt Team
                                        </div>

                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>