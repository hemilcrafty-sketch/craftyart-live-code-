<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\RateController;
use App\Models\Caricature\Attire;
use App\Models\Design;
use Illuminate\Http\Request;

class XMLController extends ApiController
{
    private array $countries = [
        ["name" => "South Georgia", "currency" => "SHP", "cc" => "GS"],
        ["name" => "Grenada", "currency" => "XCD", "cc" => "GD"],
        ["name" => "Switzerland", "currency" => "CHF", "cc" => "CH"],
        ["name" => "Sierra Leone", "currency" => "SLL", "cc" => "SL"],
        ["name" => "Hungary", "currency" => "HUF", "cc" => "HU"],
        ["name" => "Taiwan", "currency" => "TWD", "cc" => "TW"],
        ["name" => "Wallis and Futuna", "currency" => "XPF", "cc" => "WF"],
        ["name" => "Barbados", "currency" => "BBD", "cc" => "BB"],
        ["name" => "Pitcairn Islands", "currency" => "NZD", "cc" => "PN"],
        ["name" => "Ivory Coast", "currency" => "XOF", "cc" => "CI"],
        ["name" => "Tunisia", "currency" => "TND", "cc" => "TN"],
        ["name" => "Italy", "currency" => "EUR", "cc" => "IT"],
        ["name" => "Benin", "currency" => "XOF", "cc" => "BJ"],
        ["name" => "Indonesia", "currency" => "IDR", "cc" => "ID"],
        ["name" => "Cape Verde", "currency" => "CVE", "cc" => "CV"],
        ["name" => "Saint Kitts and Nevis", "currency" => "XCD", "cc" => "KN"],
        ["name" => "Laos", "currency" => "LAK", "cc" => "LA"],
        ["name" => "Caribbean Netherlands", "currency" => "USD", "cc" => "BQ"],
        ["name" => "Uganda", "currency" => "UGX", "cc" => "UG"],
        ["name" => "Andorra", "currency" => "EUR", "cc" => "AD"],
        ["name" => "Burundi", "currency" => "BIF", "cc" => "BI"],
        ["name" => "South Africa", "currency" => "ZAR", "cc" => "ZA"],
        ["name" => "France", "currency" => "EUR", "cc" => "FR"],
        ["name" => "Libya", "currency" => "LYD", "cc" => "LY"],
        ["name" => "Mexico", "currency" => "MXN", "cc" => "MX"],
        ["name" => "Gabon", "currency" => "XAF", "cc" => "GA"],
        ["name" => "Northern Mariana Islands", "currency" => "USD", "cc" => "MP"],
        ["name" => "North Macedonia", "currency" => "MKD", "cc" => "MK"],
        ["name" => "China", "currency" => "CNY", "cc" => "CN"],
        ["name" => "Yemen", "currency" => "YER", "cc" => "YE"],
        ["name" => "Saint Barthélemy", "currency" => "EUR", "cc" => "BL"],
        ["name" => "Guernsey", "currency" => "GBP", "cc" => "GG"],
        ["name" => "Solomon Islands", "currency" => "SBD", "cc" => "SB"],
        ["name" => "Svalbard and Jan Mayen", "currency" => "NOK", "cc" => "SJ"],
        ["name" => "Faroe Islands", "currency" => "DKK", "cc" => "FO"],
        ["name" => "Uzbekistan", "currency" => "UZS", "cc" => "UZ"],
        ["name" => "Egypt", "currency" => "EGP", "cc" => "EG"],
        ["name" => "Senegal", "currency" => "XOF", "cc" => "SN"],
        ["name" => "Sri Lanka", "currency" => "LKR", "cc" => "LK"],
        ["name" => "Palestine", "currency" => "EGP", "cc" => "PS"],
        ["name" => "Bangladesh", "currency" => "BDT", "cc" => "BD"],
        ["name" => "Peru", "currency" => "PEN", "cc" => "PE"],
        ["name" => "Singapore", "currency" => "SGD", "cc" => "SG"],
        ["name" => "Turkey", "currency" => "TRY", "cc" => "TR"],
        ["name" => "Afghanistan", "currency" => "AFN", "cc" => "AF"],
        ["name" => "Aruba", "currency" => "AWG", "cc" => "AW"],
        ["name" => "Cook Islands", "currency" => "CKD", "cc" => "CK"],
        ["name" => "United Kingdom", "currency" => "GBP", "cc" => "GB"],
        ["name" => "Zambia", "currency" => "ZMW", "cc" => "ZM"],
        ["name" => "Finland", "currency" => "EUR", "cc" => "FI"],
        ["name" => "Niger", "currency" => "XOF", "cc" => "NE"],
        ["name" => "Christmas Island", "currency" => "AUD", "cc" => "CX"],
        ["name" => "Tokelau", "currency" => "NZD", "cc" => "TK"],
        ["name" => "Guinea-Bissau", "currency" => "XOF", "cc" => "GW"],
        ["name" => "Azerbaijan", "currency" => "AZN", "cc" => "AZ"],
        ["name" => "Réunion", "currency" => "EUR", "cc" => "RE"],
        ["name" => "Djibouti", "currency" => "DJF", "cc" => "DJ"],
        ["name" => "North Korea", "currency" => "KPW", "cc" => "KP"],
        ["name" => "Mauritius", "currency" => "MUR", "cc" => "MU"],
        ["name" => "Montserrat", "currency" => "XCD", "cc" => "MS"],
        ["name" => "United States Virgin Islands", "currency" => "USD", "cc" => "VI"],
        ["name" => "Colombia", "currency" => "COP", "cc" => "CO"],
        ["name" => "Greece", "currency" => "EUR", "cc" => "GR"],
        ["name" => "Croatia", "currency" => "EUR", "cc" => "HR"],
        ["name" => "Morocco", "currency" => "MAD", "cc" => "MA"],
        ["name" => "Algeria", "currency" => "DZD", "cc" => "DZ"],
        ["name" => "Netherlands", "currency" => "EUR", "cc" => "NL"],
        ["name" => "Sudan", "currency" => "SDG", "cc" => "SD"],
        ["name" => "Fiji", "currency" => "FJD", "cc" => "FJ"],
        ["name" => "Liechtenstein", "currency" => "CHF", "cc" => "LI"],
        ["name" => "Nepal", "currency" => "NPR", "cc" => "NP"],
        ["name" => "Puerto Rico", "currency" => "USD", "cc" => "PR"],
        ["name" => "Georgia", "currency" => "GEL", "cc" => "GE"],
        ["name" => "Pakistan", "currency" => "PKR", "cc" => "PK"],
        ["name" => "Monaco", "currency" => "EUR", "cc" => "MC"],
        ["name" => "Botswana", "currency" => "BWP", "cc" => "BW"],
        ["name" => "Lebanon", "currency" => "LBP", "cc" => "LB"],
        ["name" => "Papua New Guinea", "currency" => "PGK", "cc" => "PG"],
        ["name" => "Mayotte", "currency" => "EUR", "cc" => "YT"],
        ["name" => "Dominican Republic", "currency" => "DOP", "cc" => "DO"],
        ["name" => "Norfolk Island", "currency" => "AUD", "cc" => "NF"],
        ["name" => "Qatar", "currency" => "QAR", "cc" => "QA"],
        ["name" => "Madagascar", "currency" => "MGA", "cc" => "MG"],
        ["name" => "India", "currency" => "INR", "cc" => "IN"],
        ["name" => "Syria", "currency" => "SYP", "cc" => "SY"],
        ["name" => "Montenegro", "currency" => "EUR", "cc" => "ME"],
        ["name" => "Eswatini", "currency" => "SZL", "cc" => "SZ"],
        ["name" => "Paraguay", "currency" => "PYG", "cc" => "PY"],
        ["name" => "El Salvador", "currency" => "USD", "cc" => "SV"],
        ["name" => "Ukraine", "currency" => "UAH", "cc" => "UA"],
        ["name" => "Isle of Man", "currency" => "GBP", "cc" => "IM"],
        ["name" => "Namibia", "currency" => "NAD", "cc" => "NA"],
        ["name" => "United Arab Emirates", "currency" => "AED", "cc" => "AE"],
        ["name" => "Bulgaria", "currency" => "BGN", "cc" => "BG"],
        ["name" => "Greenland", "currency" => "DKK", "cc" => "GL"],
        ["name" => "Germany", "currency" => "EUR", "cc" => "DE"],
        ["name" => "Cambodia", "currency" => "KHR", "cc" => "KH"],
        ["name" => "Iraq", "currency" => "IQD", "cc" => "IQ"],
        ["name" => "French Southern and Antarctic Lands", "currency" => "EUR", "cc" => "TF"],
        ["name" => "Sweden", "currency" => "SEK", "cc" => "SE"],
        ["name" => "Cuba", "currency" => "CUC", "cc" => "CU"],
        ["name" => "Kyrgyzstan", "currency" => "KGS", "cc" => "KG"],
        ["name" => "Russia", "currency" => "RUB", "cc" => "RU"],
        ["name" => "Malaysia", "currency" => "MYR", "cc" => "MY"],
        ["name" => "São Tomé and Príncipe", "currency" => "STN", "cc" => "ST"],
        ["name" => "Cyprus", "currency" => "EUR", "cc" => "CY"],
        ["name" => "Canada", "currency" => "CAD", "cc" => "CA"],
        ["name" => "Malawi", "currency" => "MWK", "cc" => "MW"],
        ["name" => "Saudi Arabia", "currency" => "SAR", "cc" => "SA"],
        ["name" => "Bosnia and Herzegovina", "currency" => "BAM", "cc" => "BA"],
        ["name" => "Ethiopia", "currency" => "ETB", "cc" => "ET"],
        ["name" => "Spain", "currency" => "EUR", "cc" => "ES"],
        ["name" => "Slovenia", "currency" => "EUR", "cc" => "SI"],
        ["name" => "Oman", "currency" => "OMR", "cc" => "OM"],
        ["name" => "Saint Pierre and Miquelon", "currency" => "EUR", "cc" => "PM"],
        ["name" => "Macau", "currency" => "MOP", "cc" => "MO"],
        ["name" => "San Marino", "currency" => "EUR", "cc" => "SM"],
        ["name" => "Lesotho", "currency" => "LSL", "cc" => "LS"],
        ["name" => "Marshall Islands", "currency" => "USD", "cc" => "MH"],
        ["name" => "Sint Maarten", "currency" => "ANG", "cc" => "SX"],
        ["name" => "Iceland", "currency" => "ISK", "cc" => "IS"],
        ["name" => "Luxembourg", "currency" => "EUR", "cc" => "LU"],
        ["name" => "Argentina", "currency" => "ARS", "cc" => "AR"],
        ["name" => "Turks and Caicos Islands", "currency" => "USD", "cc" => "TC"],
        ["name" => "Nauru", "currency" => "AUD", "cc" => "NR"],
        ["name" => "Cocos (Keeling) Islands", "currency" => "AUD", "cc" => "CC"],
        ["name" => "Western Sahara", "currency" => "DZD", "cc" => "EH"],
        ["name" => "Dominica", "currency" => "XCD", "cc" => "DM"],
        ["name" => "Costa Rica", "currency" => "CRC", "cc" => "CR"],
        ["name" => "Australia", "currency" => "AUD", "cc" => "AU"],
        ["name" => "Thailand", "currency" => "THB", "cc" => "TH"],
        ["name" => "Haiti", "currency" => "HTG", "cc" => "HT"],
        ["name" => "Tuvalu", "currency" => "AUD", "cc" => "TV"],
        ["name" => "Honduras", "currency" => "HNL", "cc" => "HN"],
        ["name" => "Equatorial Guinea", "currency" => "XAF", "cc" => "GQ"],
        ["name" => "Saint Lucia", "currency" => "XCD", "cc" => "LC"],
        ["name" => "French Polynesia", "currency" => "XPF", "cc" => "PF"],
        ["name" => "Belarus", "currency" => "BYN", "cc" => "BY"],
        ["name" => "Latvia", "currency" => "EUR", "cc" => "LV"],
        ["name" => "Palau", "currency" => "USD", "cc" => "PW"],
        ["name" => "Guadeloupe", "currency" => "EUR", "cc" => "GP"],
        ["name" => "Philippines", "currency" => "PHP", "cc" => "PH"],
        ["name" => "Gibraltar", "currency" => "GIP", "cc" => "GI"],
        ["name" => "Denmark", "currency" => "DKK", "cc" => "DK"],
        ["name" => "Cameroon", "currency" => "XAF", "cc" => "CM"],
        ["name" => "Guinea", "currency" => "GNF", "cc" => "GN"],
        ["name" => "Bahrain", "currency" => "BHD", "cc" => "BH"],
        ["name" => "Suriname", "currency" => "SRD", "cc" => "SR"],
        ["name" => "DR Congo", "currency" => "CDF", "cc" => "CD"],
        ["name" => "Somalia", "currency" => "SOS", "cc" => "SO"],
        ["name" => "Czechia", "currency" => "CZK", "cc" => "CZ"],
        ["name" => "New Caledonia", "currency" => "XPF", "cc" => "NC"],
        ["name" => "Vanuatu", "currency" => "VUV", "cc" => "VU"],
        ["name" => "Saint Helena, Ascension and Tristan da Cunha", "currency" => "GBP", "cc" => "SH"],
        ["name" => "Togo", "currency" => "XOF", "cc" => "TG"],
        ["name" => "British Virgin Islands", "currency" => "USD", "cc" => "VG"],
        ["name" => "Kenya", "currency" => "KES", "cc" => "KE"],
        ["name" => "Niue", "currency" => "NZD", "cc" => "NU"],
        ["name" => "Rwanda", "currency" => "RWF", "cc" => "RW"],
        ["name" => "Estonia", "currency" => "EUR", "cc" => "EE"],
        ["name" => "Romania", "currency" => "RON", "cc" => "RO"],
        ["name" => "Trinidad and Tobago", "currency" => "TTD", "cc" => "TT"],
        ["name" => "Guyana", "currency" => "GYD", "cc" => "GY"],
        ["name" => "Timor-Leste", "currency" => "USD", "cc" => "TL"],
        ["name" => "Vietnam", "currency" => "VND", "cc" => "VN"],
        ["name" => "Uruguay", "currency" => "UYU", "cc" => "UY"],
        ["name" => "Vatican City", "currency" => "EUR", "cc" => "VA"],
        ["name" => "Hong Kong", "currency" => "HKD", "cc" => "HK"],
        ["name" => "Austria", "currency" => "EUR", "cc" => "AT"],
        ["name" => "Antigua and Barbuda", "currency" => "XCD", "cc" => "AG"],
        ["name" => "Turkmenistan", "currency" => "TMT", "cc" => "TM"],
        ["name" => "Mozambique", "currency" => "MZN", "cc" => "MZ"],
        ["name" => "Panama", "currency" => "PAB", "cc" => "PA"],
        ["name" => "Micronesia", "currency" => "USD", "cc" => "FM"],
        ["name" => "Ireland", "currency" => "EUR", "cc" => "IE"],
        ["name" => "Curaçao", "currency" => "ANG", "cc" => "CW"],
        ["name" => "French Guiana", "currency" => "EUR", "cc" => "GF"],
        ["name" => "Norway", "currency" => "NOK", "cc" => "NO"],
        ["name" => "Åland Islands", "currency" => "EUR", "cc" => "AX"],
        ["name" => "Central African Republic", "currency" => "XAF", "cc" => "CF"],
        ["name" => "Burkina Faso", "currency" => "XOF", "cc" => "BF"],
        ["name" => "Eritrea", "currency" => "ERN", "cc" => "ER"],
        ["name" => "Tanzania", "currency" => "TZS", "cc" => "TZ"],
        ["name" => "South Korea", "currency" => "KRW", "cc" => "KR"],
        ["name" => "Jordan", "currency" => "JOD", "cc" => "JO"],
        ["name" => "Mauritania", "currency" => "MRU", "cc" => "MR"],
        ["name" => "Lithuania", "currency" => "EUR", "cc" => "LT"],
        ["name" => "United States Minor Outlying Islands", "currency" => "USD", "cc" => "UM"],
        ["name" => "Slovakia", "currency" => "EUR", "cc" => "SK"],
        ["name" => "Angola", "currency" => "AOA", "cc" => "AO"],
        ["name" => "Kazakhstan", "currency" => "KZT", "cc" => "KZ"],
        ["name" => "Moldova", "currency" => "MDL", "cc" => "MD"],
        ["name" => "Mali", "currency" => "XOF", "cc" => "ML"],
        ["name" => "Falkland Islands", "currency" => "FKP", "cc" => "FK"],
        ["name" => "Armenia", "currency" => "AMD", "cc" => "AM"],
        ["name" => "Samoa", "currency" => "WST", "cc" => "WS"],
        ["name" => "Jersey", "currency" => "GBP", "cc" => "JE"],
        ["name" => "Japan", "currency" => "JPY", "cc" => "JP"],
        ["name" => "Bolivia", "currency" => "BOB", "cc" => "BO"],
        ["name" => "Chile", "currency" => "CLP", "cc" => "CL"],
        ["name" => "United States", "currency" => "USD", "cc" => "US"],
        ["name" => "Saint Vincent and the Grenadines", "currency" => "XCD", "cc" => "VC"],
        ["name" => "Bermuda", "currency" => "BMD", "cc" => "BM"],
        ["name" => "Seychelles", "currency" => "SCR", "cc" => "SC"],
        ["name" => "British Indian Ocean Territory", "currency" => "USD", "cc" => "IO"],
        ["name" => "Guatemala", "currency" => "GTQ", "cc" => "GT"],
        ["name" => "Ecuador", "currency" => "USD", "cc" => "EC"],
        ["name" => "Martinique", "currency" => "EUR", "cc" => "MQ"],
        ["name" => "Tajikistan", "currency" => "TJS", "cc" => "TJ"],
        ["name" => "Malta", "currency" => "EUR", "cc" => "MT"],
        ["name" => "Gambia", "currency" => "GMD", "cc" => "GM"],
        ["name" => "Nigeria", "currency" => "NGN", "cc" => "NG"],
        ["name" => "Bahamas", "currency" => "BSD", "cc" => "BS"],
        ["name" => "Kosovo", "currency" => "EUR", "cc" => "XK"],
        ["name" => "Kuwait", "currency" => "KWD", "cc" => "KW"],
        ["name" => "Maldives", "currency" => "MVR", "cc" => "MV"],
        ["name" => "South Sudan", "currency" => "SSP", "cc" => "SS"],
        ["name" => "Iran", "currency" => "IRR", "cc" => "IR"],
        ["name" => "Albania", "currency" => "ALL", "cc" => "AL"],
        ["name" => "Brazil", "currency" => "BRL", "cc" => "BR"],
        ["name" => "Serbia", "currency" => "RSD", "cc" => "RS"],
        ["name" => "Belize", "currency" => "BZD", "cc" => "BZ"],
        ["name" => "Myanmar", "currency" => "MMK", "cc" => "MM"],
        ["name" => "Bhutan", "currency" => "BTN", "cc" => "BT"],
        ["name" => "Venezuela", "currency" => "VES", "cc" => "VE"],
        ["name" => "Liberia", "currency" => "LRD", "cc" => "LR"],
        ["name" => "Jamaica", "currency" => "JMD", "cc" => "JM"],
        ["name" => "Poland", "currency" => "PLN", "cc" => "PL"],
        ["name" => "Cayman Islands", "currency" => "KYD", "cc" => "KY"],
        ["name" => "Brunei", "currency" => "BND", "cc" => "BN"],
        ["name" => "Comoros", "currency" => "KMF", "cc" => "KM"],
        ["name" => "Guam", "currency" => "USD", "cc" => "GU"],
        ["name" => "Tonga", "currency" => "TOP", "cc" => "TO"],
        ["name" => "Kiribati", "currency" => "AUD", "cc" => "KI"],
        ["name" => "Ghana", "currency" => "GHS", "cc" => "GH"],
        ["name" => "Chad", "currency" => "XAF", "cc" => "TD"],
        ["name" => "Zimbabwe", "currency" => "ZWL", "cc" => "ZW"],
        ["name" => "Saint Martin", "currency" => "EUR", "cc" => "MF"],
        ["name" => "Mongolia", "currency" => "MNT", "cc" => "MN"],
        ["name" => "Portugal", "currency" => "EUR", "cc" => "PT"],
        ["name" => "American Samoa", "currency" => "USD", "cc" => "AS"],
        ["name" => "Republic of the Congo", "currency" => "XAF", "cc" => "CG"],
        ["name" => "Belgium", "currency" => "EUR", "cc" => "BE"],
        ["name" => "Israel", "currency" => "ILS", "cc" => "IL"],
        ["name" => "New Zealand", "currency" => "NZD", "cc" => "NZ"],
        ["name" => "Nicaragua", "currency" => "NIO", "cc" => "NI"],
        ["name" => "Anguilla", "currency" => "XCD", "cc" => "AI"]
    ];

    function catalog(Request $request): string|array
    {

        $shippingSitemap = "";
        foreach ($this->countries as $country) {
            $shippingSitemap .= '<g:shipping>' . PHP_EOL;
            $shippingSitemap .= '<g:country>' . $country['currency'] . '</g:country>' . PHP_EOL;
            $shippingSitemap .= '<g:service>Digital delivery</g:service>' . PHP_EOL;
            $shippingSitemap .= '<g:price>0.0 ' . $country['cc'] . '</g:price>' . PHP_EOL;
            $shippingSitemap .= '</g:shipping>' . PHP_EOL;
        }

        $country = $request->country;
        if (!$country) {
            $country = "US";
        }

        $tempData = Design::where("status", 1)->orderBy('created_at', 'DESC')->get();

        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $sitemap .= '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">' . PHP_EOL;

        $sitemap .= '<channel>' . PHP_EOL;
        $sitemap .= '<title>CraftyArt</title>' . PHP_EOL;
        $sitemap .= '<link>https://www.craftyartapp.com/templates</link>' . PHP_EOL;
        $sitemap .= '<description>craftyart premium templates</description>' . PHP_EOL;

//        $isIndia = strtoupper($country) == "IN";
        $isIndia = true;

        $rates = RateController::getRates();

        foreach ($tempData as $item) {

            if ($item->description) {
                $link = "https://www.craftyartapp.com/templates/p/" . $item->id_name;
//                $image_link = "https://panel.craftyartapp.com/templates/" . $item->post_thumb;
                $image_link = HelperController::$mediaUrl . $item->post_thumb;

                $thumbArray = json_decode($item->thumb_array);
                $size = sizeof($thumbArray);
                $payment = RateController::getTemplateRates($rates, $size, $item);

                $currency = $isIndia ? 'INR' : 'USD';

                $finalAmount = $isIndia ? $payment['inrVal'] : $payment['usdVal'];
                $saleAmount = $isIndia ? $payment['inrVal'] : $payment['usdVal'];

//                $showOffer = false;
//                if ($item->is_premium == 0 || $item->is_premium == '0') {
//                    $saleAmount = $isIndia ? 1 : 0.01;
//                    $showOffer = true;
//                }

                $finalAmount .= ' ' . $currency;
                $saleAmount .= ' ' . $currency;

                $sitemap .= '<item>' . PHP_EOL;
                $sitemap .= '<g:brand>' . 'CraftyArt' . '</g:brand>' . PHP_EOL;
                $sitemap .= '<g:id>' . htmlspecialchars($item->id) . '</g:id>' . PHP_EOL;
                $sitemap .= '<g:title>' . htmlspecialchars($item->post_name) . '</g:title>' . PHP_EOL;
                $sitemap .= '<g:description>' . htmlspecialchars($item->description) . '</g:description>' . PHP_EOL;
                $sitemap .= '<g:link>' . htmlspecialchars($link) . '</g:link>' . PHP_EOL;
                $sitemap .= '<g:image_link>' . htmlspecialchars($image_link) . '</g:image_link>' . PHP_EOL;
                $sitemap .= '<g:condition>New</g:condition>' . PHP_EOL;
                $sitemap .= '<g:availability>In stock</g:availability>' . PHP_EOL;

                $sitemap .= '<g:price>' . htmlspecialchars($finalAmount) . '</g:price>' . PHP_EOL;
//                if ($showOffer) {
//                    $sitemap .= '<g:sale_price>' . htmlspecialchars($saleAmount) . '</g:sale_price>' . PHP_EOL;
//                }

//                $sitemap .= $shippingSitemap . PHP_EOL;

                $sitemap .= '<g:product_type>' . 'Design Template' . '</g:product_type>' . PHP_EOL;
                $sitemap .= '<g:google_product_category>' . '8022' . '</g:google_product_category>' . PHP_EOL;
                $sitemap .= '<g:identifier_exists>' . 'false' . '</g:identifier_exists>' . PHP_EOL;

                $sitemap .= '</item>' . PHP_EOL;
            }
        }

        $sitemap .= '</channel>';
        $sitemap .= '</rss>';

        return $sitemap;
    }

    function caricatures(Request $request): string|array
    {

        $tempData = Attire::where("status", 1)->orderBy('created_at', 'DESC')->get();

        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $sitemap .= '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">' . PHP_EOL;

        $sitemap .= '<channel>' . PHP_EOL;
        $sitemap .= '<title>CraftyArt</title>' . PHP_EOL;
        $sitemap .= '<link>https://www.craftyartapp.com/caricature-maker</link>' . PHP_EOL;
        $sitemap .= '<description>craftyart premium caricatures</description>' . PHP_EOL;

//        $isIndia = strtoupper($country) == "IN";
        $isIndia = true;

        $rates = RateController::getRates();

        foreach ($tempData as $item) {

            if ($item->meta_description) {
                $link = $item->page_link;
//                $image_link = "https://panel.craftyartapp.com/templates/" . $item->post_thumb;
                $image_link =  $item->thumbnail_url;

                $payment = RateController::getCaricatureRates($rates, $item->head_count, false, $item->editor_choice == 1);

                $currency = $isIndia ? 'INR' : 'USD';

                $finalAmount = $isIndia ? $payment['inrVal'] : $payment['usdVal'];
                $saleAmount = $isIndia ? $payment['inrVal'] : $payment['usdVal'];

//                $showOffer = false;
//                if ($item->is_premium == 0 || $item->is_premium == '0') {
//                    $saleAmount = $isIndia ? 1 : 0.01;
//                    $showOffer = true;
//                }

                $finalAmount .= ' ' . $currency;
                $saleAmount .= ' ' . $currency;

                $sitemap .= '<item>' . PHP_EOL;
                $sitemap .= '<g:brand>' . 'CraftyArt' . '</g:brand>' . PHP_EOL;
                $sitemap .= '<g:id>' . htmlspecialchars("$item->id-$item->string_id") . '</g:id>' . PHP_EOL;
                $sitemap .= '<g:title>' . htmlspecialchars($item->post_name) . '</g:title>' . PHP_EOL;
                $sitemap .= '<g:description>' . htmlspecialchars($item->meta_description) . '</g:description>' . PHP_EOL;
                $sitemap .= '<g:link>' . htmlspecialchars($link) . '</g:link>' . PHP_EOL;
                $sitemap .= '<g:image_link>' . htmlspecialchars($image_link) . '</g:image_link>' . PHP_EOL;
                $sitemap .= '<g:condition>New</g:condition>' . PHP_EOL;
                $sitemap .= '<g:availability>In stock</g:availability>' . PHP_EOL;

                $sitemap .= '<g:price>' . htmlspecialchars($finalAmount) . '</g:price>' . PHP_EOL;
//                if ($showOffer) {
//                    $sitemap .= '<g:sale_price>' . htmlspecialchars($saleAmount) . '</g:sale_price>' . PHP_EOL;
//                }

//                $sitemap .= $shippingSitemap . PHP_EOL;

                $sitemap .= '<g:product_type>' . 'Design Template' . '</g:product_type>' . PHP_EOL;
                $sitemap .= '<g:google_product_category>' . '8022' . '</g:google_product_category>' . PHP_EOL;
                $sitemap .= '<g:identifier_exists>' . 'false' . '</g:identifier_exists>' . PHP_EOL;

                $sitemap .= '</item>' . PHP_EOL;
            }
        }

        $sitemap .= '</channel>';
        $sitemap .= '</rss>';

        return $sitemap;
    }
}
