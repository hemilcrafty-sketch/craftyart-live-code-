<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\RateController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Models\Draft;
use App\Models\NewCategory;
use Cache;
use Illuminate\Http\Request;
use App\Models\Design;
use Illuminate\Support\Collection;

class DashboardApiController extends ApiController
{

    public static array $templateSeo = [
        "short_desc" => "Crafty Art offers creative and customizable digital invitation templates designs for all events, including weddings, birthdays, engagements, baby showers, and more. Create stunning online invitations effortlessly and make every celebration special with unique designs tailored to your style. Perfect for any occasion!",
        "h1_tag" => "All Templates Designs",
        "h2_tag" => "Discover Unique Templates and Designs for Every Occasion with Crafty Art",
        "meta_title" => "Creative Templates Designs for Digital Invitations",
        "meta_desc" => "Explore creative templates designs for digital invitations at Crafty Art. Perfect for weddings, birthdays, engagements, baby showers, and all your special events.",
        "long_desc" => "<p>Crafty Art is your ultimate destination for stunning and customizable digital invitation templates. Whether you are planning a wedding, birthday celebration, engagement, or any special occasion, Crafty Art offers a wide range of designs to suit every event. Our templates are crafted with care to ensure they are not only visually appealing but also easy to personalize.</p>
                        <h3><strong>Why Choose Crafty Art for Your Memorable Events?</strong></h3>
                        <p>When it comes to celebrations of any event, Crafty Art stands out for its attention to detail and creativity. Here are a few reasons why our templates are perfect for your needs:</p>
                        <ul>
                        <li><strong>Customizable Designs:</strong> Each template is fully customizable, allowing you to add your personal touch with names, dates, colors, and fonts.</li>
                        <li><strong>Wide Range of Occasions:</strong> From weddings and birthdays to baby showers and housewarming parties, our collection covers every event imaginable.</li>
                        <li><strong>High-Quality Visuals:</strong> Our templates are designed by professionals to ensure they look elegant and modern.</li>
                        <li><strong>Digital Convenience:</strong> Save time and effort with <a href=\"https://www.craftyartapp.com/templates/invitation\" target=\"_blank\">digital invitations</a> that can be sent instantly via email or shared on social media.</li>
                        </ul>
                        <h3><strong>Explore Our Diverse Collection of Templates</strong></h3>
                        <h4><strong>Wedding Invitations</strong></h4>
                        <p>Celebrate love with our beautiful <a href=\"https://www.craftyartapp.com/k/wedding-invitation-template\" target=\"_blank\">wedding invitation templates</a>. Choose from classic, modern, or floral designs to match your wedding theme. These templates feature intricate patterns and elegant typography, making them perfect for announcing your special day.</p>
                        <h4><strong>Birthday Invitations</strong></h4>
                        <p>Make birthdays extra special with vibrant and fun designs tailored for all ages. Whether it&rsquo;s a child&rsquo;s party or a milestone celebration, our <a href=\"https://www.craftyartapp.com/k/birthday-invitation\" target=\"_blank\">birthday invitation </a>templates are customizable to reflect the joy of the occasion.</p>
                        <h4><strong>Engagement Invitations</strong></h4>
                        <p>Announce your engagement in style with sophisticated templates. From minimalist designs to luxurious <a href=\"https://www.craftyartapp.com/k/engagement-invitation\" target=\"_blank\">engagement invitation</a> themes, you&rsquo;ll find the perfect way to share your exciting news with family and friends.</p>
                        <h4><strong>Baby Shower Invitations</strong></h4>
                        <p>Welcome a new bundle of joy with adorable <a href=\"https://www.craftyartapp.com/k/baby-shower-invitations\" target=\"_blank\">baby shower invitation</a> templates. Featuring soft pastel colors and cute graphics, these designs are ideal for celebrating this precious moment.</p>
                        <h4><strong>Bridal Shower Invitations</strong></h4>
                        <p>Gather your closest friends and family with stunning <a href=\"https://www.craftyartapp.com/k/bridal-shower-invitation-templates\" target=\"_blank\">bridal shower invitations</a>. Our collection includes elegant and chic designs to suit every bride&rsquo;s style.</p>
                        <h4><strong>Housewarming Invitations</strong></h4>
                        <p>Invite loved ones to your new home with our charming <a href=\"https://www.craftyartapp.com/house-warming-invitation-card-in-english\">housewarming invitation</a> templates. They are designed to convey warmth and excitement, making your guests feel special.</p>
                        <h3><strong>How to Customize Your Invitation with Crafty Art</strong></h3>
                        <p>Personalizing your invitation is simple and fun with Crafty Art. Follow these steps:</p>
                        <ol>
                        <li><strong>Choose a Template:</strong> Browse our extensive collection and pick a design that fits your event.</li>
                        <li><strong>Edit the Details:</strong> Use our user-friendly editing tools to add names, dates, and other event details.</li>
                        <li><strong>Customize the Look:</strong> Adjust colors, fonts, and images to match your theme.</li>
                        <li><strong>Download or Share:</strong> Once you&rsquo;re satisfied, download the invitation or share it digitally.</li>
                        </ol>
                        <h3><strong>Make Every Event Memorable with Crafty Art</strong></h3>
                        <p>Crafty Art is more than just a platform for digital invitations&mdash;it&rsquo;s a place where creativity meets convenience. Our <a href=\"https://www.craftyartapp.com/templates/invitation\" target=\"_blank\">latest templates</a> are designed to help you create lasting memories for every occasion. Explore our collection today and find the perfect design to celebrate life&rsquo;s special moments.</p>",
    ];

    function getTemplates(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $callback = function () use ($request) {
            $response = [];

            $rates = RateController::getRates();
            $limit = HelperController::getPaginationLimit();
            $categories = NewCategory::getParentCategories();

            $datas = CategoryTemplatesApiController::getAllNewCategories(rates: $rates, categories: $categories, uid: $this->uid, isImp: 1);
            $response['catlist'] = $datas['catlist'];
            $response['cats'] = $datas['datas'];

            // inspired section
            if ($this->uid) {
                $draft = Draft::where('user_id', $this->uid)->whereNotNull('template_id')->orderByDesc('id')->first();
                if ($draft) {
                    $item = Design::where('string_id', $draft->template_id)->where('status', 1)->first();
                    if ($item) {
                        $SearchApi = new SearchApiController($request);
                        $searchData = $SearchApi->searchTemplates($rates, json_decode($item->related_tags)[0], 1, null, $limit, $item->id_name, $item->ratio);
                        $response['inspired'] = $searchData['datas'];
                    }
                }
            } else {
                $itemData = Design::where("status", 1)->whereHas('parent', function ($query) {
                    $query->where('status', 1);
                })->orderByDesc('id')->take($limit)->get();

                $response['latest'] = $this->getTemplateDatas($this->uid, $itemData, rates: $rates);
            }
            // end of inspired section

            // trending section
            $itemData = Design::where("trending_views", ">", 0)->where("status", 1)->whereHas('parent', function ($query) {
                $query->where('status', 1);
            })->orderByDesc('trending_views')->take($limit)->get();

            $response['trending'] = $this->getTemplateDatas($this->uid, $itemData, rates: $rates);
            // end trending section

            // upcoming event section
            $itemData = Design::whereRaw("STR_TO_DATE(end_date, '%m/%d/%Y') > ?", [now()])->where("status", 1)->whereHas('parent', function ($query) {
                $query->where('status', 1);
            })->orderByRaw("STR_TO_DATE(end_date, '%m/%d/%Y') ASC")->take($limit)->get();

            $response['upcomingEvents'] = $this->getTemplateDatas($this->uid, $itemData, rates: $rates);
            // end of upcoming event section

            // upcoming video section
            $itemData = Design::whereNotNull("video_thumb")->where("status", 1)->whereHas('parent', function ($query) {
                $query->where('status', 1);
            })->orderByDesc("id")->take($limit)->get();

            $response['videos'] = $this->getTemplateDatas($this->uid, $itemData, rates: $rates);
            // end of upcoming video section

            $response['seo'] = self::$templateSeo;

            return ResponseHandler::sendRealResponse(new ResponseInterface(200, true, 'Loaded!', $response));
        };

        if (HelperController::$cacheEnabled) $response = Cache::tags(["dashboard_api"])->remember("dashboard_api" . $this->uid, HelperController::$cacheTimeOut, $callback);
        else $response = $callback();


        return ResponseHandler::sendEncryptedResponse($request,  $response);
    }

    public static function getTemplateDatas($uid, $itemData, Collection $rates): array
    {
        $item_rows = array();
        $categories = [];
        if ($uid) {
            $allCategoryIds = $itemData->pluck('new_category_id')->unique();
            $categories = NewCategory::whereIn('id', $allCategoryIds)->get()->keyBy('id');
        }

        foreach ($itemData as $item) {
            $catRow = $categories[$item->new_category_id] ?? null;
            $catLink = HelperController::$webPageUrl . "templates/p/" . $item->id_name;
            if ($catRow != null) $catLink = $catRow->cat_link;

            $item_rows[] = HelperController::getItemData(
                uid: $uid,
                catRow: $catRow,
                item: $item,
                thumbArray: json_decode($item->thumb_array),
                catLink: $catLink,
                rates: $rates
            );
        }
        return $item_rows;
    }
}
