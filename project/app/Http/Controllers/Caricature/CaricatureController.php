<?php

namespace App\Http\Controllers\Caricature;

use App\Http\Controllers\CategoryTemplatesApiController;
use App\Http\Controllers\PReviewController;
use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\FacebookEvent;
use App\Http\Controllers\Utils\FbPixel;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\PaginationController;
use App\Http\Controllers\Utils\RateController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\UserData;
use App\Models\Caricature\CaricatureCategory;
use App\Models\Caricature\Attire;
use App\Models\WebTemplateViewHistory;
use Cache;
use Illuminate\Http\Request;

class CaricatureController extends ApiController
{
    public static string $defaultTagLine = 'Beautiful and elegant designs with customizable templates - perfect for any special celebrations!';
    public static array $latestSeo = [
        "h1_tag" => "Online Caricature Maker to Turn Your Photos into Art",
        "meta_title" => "Best Online Caricature Maker for Photo to Caricature",
        "meta_desc" => "Turn your photos into stunning personalized artwork with the best online caricature maker, perfect for weddings, couples, events, and creative invites.",
        "short_desc" => "Transform your photos into stylish digital artwork with an online caricature maker that lets you customize the face, including expressions, hairstyles, skin tone, and fine facial features. Perfect for weddings, engagements, birthdays, and events, it helps create elegant invitations, personalized keepsakes, and memorable celebration designs with a unique touch.",
        "long_desc" => "<h2><strong>Create Unique Designs with an Online Caricature Maker</strong></h2>
<p><span style=\"font-weight: 400;\">Creating unique designs is simple. You can turn photos into playful artwork that feels personal and special. A caricature online maker helps make every design look creative and stylish. It is great for invites, gifts, and happy memories.</span></p>
<p><span style=\"font-weight: 400;\">Different styles and themes make every design look clear and lovely. With an online caricature maker, you can create a couple of caricatures, family art, wedding designs, or party invites in a quick way. It saves time and gives your design a clean and modern look.</span></p>
<p><span style=\"font-weight: 400;\">A </span><a href=\"https://www.craftyartapp.com/caricature/wedding\" target=\"_blank\"><strong>Wedding Caricature</strong></a><span style=\"font-weight: 400;\"> maker online makes artwork easy to create. It gives simple editing tools and neat layouts. Crafty Art helps every celebration look unique, memorable, and full of joy.</span></p>
<h2><strong>What Is a Caricature Maker and How Does It Work?</strong></h2>
<p><span style=\"font-weight: 400;\">A caricature maker is a simple way to turn a photo into creative artwork. It changes normal pictures into stylish face art with playful expressions and neat designs. This makes it useful for invitations, gifts, social posts, and special memories. The process is fast, smooth, and easy to use.</span></p>
<p><span style=\"font-weight: 400;\">With a caricature generator, you can upload a photo and easily add the face to a ready caricature design. This saves time and gives every design a unique and modern look.</span></p>
<h3><strong>How Does It Work?</strong></h3>
<ul>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Upload your photo in a few simple steps</span></li>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Add the face to your selected caricature design</span></li>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Preview the final creative artwork</span></li>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Download and share your final caricature design</span></li>
</ul>
<p><span style=\"font-weight: 400;\">A caricature creator online makes it easy to create couple art, wedding invites, party graphics, and baby shower celebration artwork with simple face inclusion. Crafty Art keeps the full process simple, clear, and perfect for creative celebration designs.</span></p>
<p><span style=\"font-weight: 400;\">You can also see an&nbsp;</span><a href=\"https://www.craftyartapp.com/ai-tools/ghibli-style-image-generator\" target=\"_blank\"><strong>AI Ghibli Style Image</strong></a><strong>.</strong></p>
<h2><strong>Popular Types of Caricature Designs You Can Create</strong></h2>
<p><span style=\"font-weight: 400;\">Caricature designs come in many creative styles, from classic portraits to modern digital artwork. They are perfect for gifts, invitations, social posts, and event designs. A caricature image maker helps turn simple photos into stylish artwork with unique expressions and creative themes.</span></p>
<p><span style=\"font-weight: 400;\">Here are the popular types of caricature designs you can create:</span></p>
<h3><strong>1. Traditional Caricatures</strong></h3>
<p><span style=\"font-weight: 400;\">These designs use a classic and timeless style, and with an online caricature maker, they turn every photo into something personal, elegant, and visually memorable.</span></p>
<ul>
<li style=\"font-weight: 400;\"><strong>Couple Caricatures:</strong><span style=\"font-weight: 400;\"> Perfect for wedding invites, anniversary gifts, and engagement celebration designs. They make special moments look romantic and memorable.</span></li>
<li style=\"font-weight: 400;\"><strong>Baby Shower Caricatures:</strong><span style=\"font-weight: 400;\"> Great for baby shower invites, welcome boards, and sweet celebration memories that feel warm and joyful.</span></li>
<li style=\"font-weight: 400;\"><strong>Farewell Caricatures:</strong><span style=\"font-weight: 400;\"> A thoughtful style for office goodbyes, retirement memories, and meaningful keepsake designs.</span></li>
</ul>
<h3><strong>2. Portrait &amp; Personalised Caricatures</strong></h3>
<p><span style=\"font-weight: 400;\">These designs focus on personal moments and custom event themes. A caricature picture maker helps create unique gifts and invites with ease.</span></p>
<ul>
<li style=\"font-weight: 400;\"><strong>Birthday Caricatures:</strong><span style=\"font-weight: 400;\"> Perfect for party invites and gift designs. They add joy and color to celebrations.</span></li>
<li style=\"font-weight: 400;\"><strong>Wedding Portrait Designs:</strong><span style=\"font-weight: 400;\"> Great for couple invites and keepsake gifts. They make designs look stylish and emotional.</span></li>
<li style=\"font-weight: 400;\"><strong>Festival &amp; Theme Portraits:</strong><span style=\"font-weight: 400;\"> Best for creative ideas and themed events. These designs feel cheerful and unique.</span></li>
</ul>
<h3><strong>3. Modern Creative Styles</strong></h3>
<p><span style=\"font-weight: 400;\">These styles give a modern digital touch to every design. They make invitations, gifts, and celebration pages look more stylish and premium.</span></p>
<ul>
<li style=\"font-weight: 400;\"><strong>3D Caricature:</strong><span style=\"font-weight: 400;\"> Gives a premium and realistic depth effect. It makes the design look rich, modern, and more eye-catching for wedding and birthday pages.</span></li>
<li style=\"font-weight: 400;\"><strong>Caricature making:</strong><span style=\"font-weight: 400;\"> Smooth editing with simple customization. It helps users create invitation-ready caricatures quickly with names, themes, and event details.</span></li>
<li style=\"font-weight: 400;\"><strong>Animated caricature maker online:</strong><span style=\"font-weight: 400;\"> Perfect for digital invites and social sharing. It adds motion-style effects that make video invites, reels, and social posts look more engaging.&nbsp;</span></li>
</ul>
<p><span style=\"font-weight: 400;\">You can also see the </span><a href=\"https://www.craftyartapp.com/caricature-wedding-invitation-video-maker-online-free/templates\" target=\"_blank\"><strong>Caricature Wedding Invitation Video Maker</strong></a><strong>.</strong></p>
<h2><strong>Make Your Own Caricature with Face and Body Customization</strong></h2>
<p><span style=\"font-weight: 400;\">Creating a custom caricature is simple and turns memories into stylish artwork. With Crafty Art, you can make your caricature by easily adding the face to match your event style and celebration theme.</span></p>
<h3><strong>Customize Face Expressions and Hairstyles</strong></h3>
<p><span style=\"font-weight: 400;\">Face styling makes every caricature look unique and full of emotion. It helps your design match the event mood. A caricature face maker lets you change smile, eyes, and hairstyle details in a simple way.</span></p>
<ul>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Change smile, eyes, and facial expressions</span></li>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Select hair length, beard, and hairstyle</span></li>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Match the look with real photo details</span></li>
</ul>
<p><span style=\"font-weight: 400;\">A make photo caricature tool, combined with an online caricature maker, keeps these face changes smooth, natural, and beautifully personalized.</span></p>
<h3><strong>Edit face, Outfit, and Accessories</strong></h3>
<p><span style=\"font-weight: 400;\">Body styling helps the caricature match your celebration theme. It makes the artwork look lively and event-ready. A face caricature maker online helps create creative event-ready artwork with personalized face editing.</span></p>
<ul>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Add flowers, gifts, phones, or office props</span></li>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Include event-based decorative elements</span></li>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Create invitation-ready creative styling</span></li>
</ul>
<p><span style=\"font-weight: 400;\">This helps make caricature of photo designs more creative and useful for invitations and welcome boards.</span></p>
<h3><strong>Add Event-Based Clothing and Theme Elements</strong></h3>
<p><span style=\"font-weight: 400;\">Themes help match the caricature with your event. This gives the artwork a stronger celebration feel.</span></p>
<ul>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Select farewell, anniversary, or festival-based designs</span></li>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Choose celebration-ready creative layouts</span></li>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Pick event-specific caricature styles</span></li>
</ul>
<p><span style=\"font-weight: 400;\">These details make every caricature design feel more personal and celebration-ready, especially for </span><a href=\"https://www.craftyartapp.com/caricature/engagement\" target=\"_blank\"><strong>Engagement Caricature</strong></a><span style=\"font-weight: 400;\"> invites and special event designs.</span></p>
<h3><strong>Select Traditional, Modern, or Party Dress Styles</strong></h3>
<p><span style=\"font-weight: 400;\">Dress styling gives the final design a clean and stylish finish. It helps match cultural and event needs.</span></p>
<ul>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Traditional wedding outfits and ethnic wear</span></li>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Modern casual and formal styles</span></li>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Party dresses with stylish accessories</span></li>
</ul>
<p><span style=\"font-weight: 400;\">This makes every caricature look premium, unique, and perfect for digital invites and keepsake gifts.</span></p>
<h2><strong>Couple Caricature Maker for Wedding, Engagement, and Baby Shower Designs</strong></h2>
<p><span style=\"font-weight: 400;\">Creating special event artwork is simple with Crafty Art. A couple caricature adds a romantic and personal touch to wedding, engagement, and baby shower designs. It turns photos into creative artwork that feels special and ready for celebration.</span></p>
<h3><strong>Wedding Couple Caricature Designs</strong></h3>
<p><span style=\"font-weight: 400;\">Wedding themes look more special with custom couple artwork. A caricature maker for wedding helps create unique invites, welcome boards, and keepsake gifts. Each wedding function can have its own creative style, making the full celebration look more personalized and memorable.</span></p>
<ul>
<li style=\"font-weight: 400;\"><strong>Sangeet Caricature:</strong><span style=\"font-weight: 400;\"> Music night invites and dance celebration artwork look more lively with </span><a href=\"https://www.craftyartapp.com/caricature/wedding/sangeet\" target=\"_blank\"><strong>Sangeet Caricature</strong></a><span style=\"font-weight: 400;\"> designs that bring joyful wedding vibes.</span></li>
<li style=\"font-weight: 400;\"><strong>Haldi Caricature:</strong><span style=\"font-weight: 400;\"> Yellow-themed pre-wedding invites and floral welcome boards feel warmer and more festive with elegant </span><a href=\"https://www.craftyartapp.com/caricature/wedding/haldi\" target=\"_blank\"><strong>Haldi Caricature</strong></a><span style=\"font-weight: 400;\"> artwork.</span></li>
<li style=\"font-weight: 400;\"><strong>Mehndi Caricature:</strong><span style=\"font-weight: 400;\"> Traditional green d&eacute;cor, mehndi ceremony cards, and festive function pages look colorful and stylish with </span><a href=\"https://www.craftyartapp.com/caricature/wedding/mehndi\" target=\"_blank\"><strong>Mehndi Caricature</strong></a><span style=\"font-weight: 400;\"> designs.</span></li>
<li style=\"font-weight: 400;\"><strong>Reception Caricature:</strong><span style=\"font-weight: 400;\"> Evening celebration cards and premium welcome boards look elegant and memorable with stylish </span><a href=\"https://www.craftyartapp.com/caricature/wedding/reception\" target=\"_blank\"><strong>Reception Caricature</strong></a><span style=\"font-weight: 400;\"> layouts.</span></li>
</ul>
<p><span style=\"font-weight: 400;\">These styles make wedding invites look premium and perfect for every wedding celebration.</span></p>
<h3><strong>Engagement &amp; Save the Date Styles</strong></h3>
<p><span style=\"font-weight: 400;\">Engagement designs look more beautiful with romantic couple artwork. A couple caricature maker online helps create elegant engagement invites and save the date cards.</span></p>
<ul>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Add ring ceremony themes</span></li>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Customize a couple of expressions and matching outfits</span></li>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Include engagement decoration elements</span></li>
</ul>
<p><span style=\"font-weight: 400;\">These designs are great for digital invites and social sharing.</span></p>
<h3><strong>Baby Shower Couple Themes</strong></h3>
<p><a href=\"https://www.craftyartapp.com/caricature/baby-shower\"><strong>Baby Shower Caricature</strong></a><span style=\"font-weight: 400;\"> artwork looks warm and joyful with custom couple styles. An online wedding caricature maker can also work well for baby shower and family event pages.</span></p>
<ul>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Add baby props and soft pastel themes</span></li>
<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Include welcome board style layouts</span></li>
</ul>
<p><span style=\"font-weight: 400;\">These designs make baby shower invites look sweet and memorable. A couple caricature png makes the design easy to use on transparent backgrounds, digital invites, welcome boards, and celebration templates.</span></p>
<h2><strong>How to Make Caricatures Online for Couples and Personal Designs</strong></h2>
<p><span style=\"font-weight: 400;\">Making couple and personal caricatures online is simple and useful for invitations, gifts, and social posts. An online caricature converter helps turn a clear photo into creative artwork in a few easy steps. It saves time and gives your design a stylish and personal look.</span></p>
<p><span style=\"font-weight: 400;\">Here is the simple process to create caricatures online:</span></p>
<p><strong> Choose Caricature</strong></p>
<p><span style=\"font-weight: 400;\">Pick your favorite caricature style first. Choose a wedding, birthday, couple, or personal design that matches your event theme. This helps the final artwork look perfect for your celebration.</span></p>
<p><strong> Add Your Photo</strong></p>
<p><span style=\"font-weight: 400;\">Use an auto caricature maker with an online caricature maker platform to upload your photo and start the design process. This simple step helps create the perfect base for your final artwork.</span></p>
<p><strong> Preview and Download</strong></p>
<p><span style=\"font-weight: 400;\">Check the final artwork and download it in high quality. If you want to know caricature how to make, this last step gives you a ready design for </span><a href=\"https://www.craftyartapp.com/templates/invitation\" target=\"_blank\"><strong>invitation cards</strong></a><span style=\"font-weight: 400;\">, welcome boards, gifts, and social sharing.</span></p>
<h2><strong>Why Crafty Art is the Best Caricature Maker Online</strong></h2>
<p><span style=\"font-weight: 400;\">Crafty Art makes caricature design simple, fast, and creative with easy tools and ready templates. It is the best caricature maker online for wedding invites, birthday artwork, farewell gifts, and social posts. An automatic caricature maker helps create stylish artwork with a clean and easy system.</span></p>
<h3><strong>Multiple Caricature Styles</strong></h3>
<p><span style=\"font-weight: 400;\">You can create bright and attractive caricatures in many styles. A caricature maker pro gives smooth and premium results for every event design. Each style looks neat and easy to understand.</span></p>
<ul>
<li style=\"font-weight: 400;\"><strong>Couple &amp; Wedding Styles:</strong><span style=\"font-weight: 400;\"> Create romantic caricatures for wedding invites, anniversary cards, and save-the-date designs. These styles make special couple moments look warm and memorable.</span></li>
<li style=\"font-weight: 400;\"><strong>Birthday &amp; Baby Shower Themes:</strong><span style=\"font-weight: 400;\"> Perfect for </span><a href=\"https://www.craftyartapp.com/templates/invitation/party\" target=\"_blank\"><strong>party invites</strong></a><span style=\"font-weight: 400;\">, baby shower cards, and family keepsakes. These designs add joy and warmth to every celebration.</span></li>
<li style=\"font-weight: 400;\"><strong>Baby Shower Welcome Board Styles:</strong><span style=\"font-weight: 400;\"> Best for welcome boards, mom-to-be celebrations, and sweet event memories. These designs make baby shower themes look soft, cute, and memorable.</span></li>
<li style=\"font-weight: 400;\"><strong>Wedding Function Styles:</strong><span style=\"font-weight: 400;\"> Special wedding celebrations look more lively and elegant with Sangeet Caricature, Haldi Caricature, Mehndi Caricature, and Reception Caricature designs made for every ceremony.</span></li>
<li style=\"font-weight: 400;\"><strong>Festival &amp; Theme Designs:</strong><span style=\"font-weight: 400;\"> Best for anniversaries and custom event pages. They make themed events look more colorful and festive.</span></li>
</ul>
<h3><strong>High-Quality Downloads</strong></h3>
<p><span style=\"font-weight: 400;\">You can get clear and premium-quality caricatures for every event use. Each file looks sharp, stylish, and easy to use in digital and print formats.</span></p>
<ul>
<li style=\"font-weight: 400;\"><strong>Invitation Ready Files:</strong><span style=\"font-weight: 400;\"> Perfect for </span><a href=\"https://www.craftyartapp.com/templates/invitation/wedding\" target=\"_blank\"><strong>wedding cards</strong></a><span style=\"font-weight: 400;\">, birthday invites, and save-the-date designs. These files fit well in both print and digital templates.</span></li>
<li style=\"font-weight: 400;\"><strong>Welcome Board Quality:</strong><span style=\"font-weight: 400;\"> Great for large boards, banners, and event entry designs. They keep every detail clear, even in bigger sizes.</span></li>
<li style=\"font-weight: 400;\"><strong>Social Media Ready:</strong><span style=\"font-weight: 400;\"> Best for reels, posts, stories, and sharing on social apps. These designs look attractive on every screen size.</span></li>
<li style=\"font-weight: 400;\"><strong>Keepsake Gift Prints:</strong><span style=\"font-weight: 400;\"> Ideal for frames, mugs, and memory gifts. They help turn caricatures into long-lasting keepsakes.</span></li>
</ul>
<h3><strong>Easy Customization Options</strong></h3>
<p><span style=\"font-weight: 400;\">You can easily personalize the caricature with simple face editing options. This makes every design feel personal and perfect for your event theme.</span></p>
<ul>
<li style=\"font-weight: 400;\"><strong>Face Include:</strong><span style=\"font-weight: 400;\"> Easily add the person&rsquo;s face into the caricature design from a selected photo. This helps turn a simple image into a creative event-ready artwork.</span></li>
<li style=\"font-weight: 400;\"><strong>Face Match:</strong><span style=\"font-weight: 400;\"> Keep the facial look similar to the original photo so the caricature feels natural, recognizable, and more personalized for the celebration.</span></li>
</ul>
<h2><strong>Create Premium Caricatures with Crafty Art&rsquo;s Online Maker Today</strong></h2>
<p><span style=\"font-weight: 400;\">Premium caricature designs make every celebration look more stylish and memorable. With Crafty Art, you can create wedding invites, birthday artwork, farewell gifts, and family keepsakes simply. A carry catcher maker helps turn normal photos into creative designs with a clean and premium finish.</span></p>
<p><span style=\"font-weight: 400;\">The editing process stays smooth and easy for every event theme. A carry catcher maker online lets you easily include and adjust faces so the artwork matches your design idea. A caricature online maker also helps create modern invitation-ready artwork that feels unique and attractive.</span></p>
<p><span style=\"font-weight: 400;\">Digital celebration pages look more creative with an online carry catcher maker. It is perfect for </span><a href=\"https://www.craftyartapp.com/templates/stationery-design/welcome-board\" target=\"_blank\"><strong>welcome boards</strong></a><span style=\"font-weight: 400;\">, social posts, digital invites, and keepsake gifts. This makes every Crafty Art design feel polished, joyful, and ready for every special moment.</span>&nbsp;</p>
<h2><strong>FAQs for caricature maker</strong></h2>
<p><strong>Q1. Can I create a caricature by adding only the face?</strong><strong><br /></strong><span style=\"font-weight: 400;\">Yes, Crafty Art lets you easily create a caricature by simply adding the face to a ready-made design template. This keeps the process quick, simple, and perfect for event-ready artwork.</span></p>
<p><strong>Q2. Are a couple of caricature designs available for weddings and events?</strong><strong><br /></strong><span style=\"font-weight: 400;\">Yes, you can create a couple of caricature designs specially made for weddings, engagements, invitations, baby showers, and other special occasions. These designs are perfect for welcome boards, digital invites, save-the-date cards, and memorable keepsakes.</span></p>
<p><strong>Q3. Can I use caricature designs for baby shower and birthday invites?</strong><strong><br /></strong><span style=\"font-weight: 400;\">Yes, caricature templates work beautifully for baby shower cards, birthday party invites, welcome boards, and family celebration pages. They make every design look sweet, joyful, and memorable.</span></p>
<p><strong>Q4. Is it easy to download the final caricature design?</strong><strong><br /></strong><span style=\"font-weight: 400;\">Yes, once the face is added, you can quickly preview and download the final caricature design for digital sharing, invitations, or keepsake use.</span></p>
<p style=\"text-align: left;\"><strong>Q5. Are wedding function caricature styles available?</strong><strong><br /></strong><span style=\"font-weight: 400;\">Yes, you can create creative designs for wedding functions like Sangeet Caricature, Haldi Caricature, Mehndi Caricature, and Reception Caricature to make every celebration look unique and premium.</span></p>
"];

    public function getCategories(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $page = $request->input('page', 1);
        $requiredSeo = $request->input('seo', true);
        $categories = CaricatureCategory::getAllCatsWithChilds(page: $page);

        $isLastPage = $categories->lastPage() === $categories->currentPage();

        $datas = [];
        $cats = [];

        $rates = RateController::getRates();

        $limit = 20;

        $index = 0;
        foreach ($categories->items() as $category) {
            $categoryId = $category->id;
            $allIds = array_merge([$categoryId], $category->child_cat_ids ?? []);
            $templates = Attire::whereIn('category_id', $allIds)->where('status', 1)->orderByRaw('id DESC')->paginate($limit, ['*'], 'page', 1);

            if ($templates->isEmpty()) continue;

            $allCategoryMap = [];

            $commonData = [
                'category_id' => $categoryId,
                'id_name' => $category->id_name,
                'category_name' => $category->category_name,
                'category_thumb' => HelperController::$mediaUrl . $category->category_thumb,
                'category_mockup' => $category->mockup ? HelperController::$mediaUrl . $category->mockup : null,
                'isLastPage' => $templates->total() <= $limit,
                'pageNo' => 1
            ];

            if ($index == 0) {
                $subCatArray = CaricatureController::getSubCategories($category);
                $commonData['sub_category'] = $subCatArray['subCats'];
            }

            $index++;
            $processedTemplates = $templates->getCollection()->map(function ($template) use ($allCategoryMap, &$usedCategoryMap, $rates) {
                $cateRow = $allCategoryMap[$template->category_id] ?? null;
                return HelperController::getCaricatureData(
                    catRow: $cateRow,
                    item: $template,
                    rates: $rates
                );
            });

            $cats[] = $commonData;

            $datas[] = array_merge($commonData, [
                'attires' => $processedTemplates,
            ]);
        }

        return $this->successed(datas: [
            "pageNo" => $page,
            "isLastPage" => $isLastPage,
            "cats" => $cats,
            "datas" => $datas,
            "seo" => $requiredSeo ? self::$latestSeo : null,
        ]);
    }

    public function getCategory(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $requiredSeo = $request->input('seo', true);
        $oldSlug = $request->slug;
        if (!$oldSlug) return $this->failed(msg: "Parameters missing!");

        $hasPageInRequest = $request->has('page');
        $data = HelperController::extractAndRemoveTrailingNumber($oldSlug);
        $page = $hasPageInRequest ? $request->input('page', 1) : $data['number'] ?? 1;
        $slug = $data['string'] ?? 1;

        $limit = HelperController::getPaginationLimit();

        $filter = isset($request->filter) ? $request->filter : [];

        $cacheTag = CaricatureCategory::$preCacheTag . "category_$slug";
        $contentCacheKey = 'category_content_' . $slug;
        $faqCacheKey = 'category_faq_' . $slug;

        $cacheKey = 'categories_' . $slug . md5(json_encode(['filter' => json_encode($filter), 'page' => $page]));

        $checkCat = CaricatureCategory::findCatLink(isStatus: 1, id: $slug);

        if (!$checkCat) return $this->failed(msg: "Parameters missing!");

        $callback = function ($doCache = true) use ($cacheTag, $contentCacheKey, $faqCacheKey, $filter, $checkCat, $limit, $page, $hasPageInRequest, $requiredSeo) {

            $usedCategoryMap = array_merge([$checkCat->id], $checkCat->child_cat_ids ?? []);

            $attiresQuery = Attire::whereIn('category_id', $usedCategoryMap)->where('status', 1);

            $attires = $attiresQuery->orderByRaw('id DESC')->paginate($limit, ['*'], 'page', $page);

            if ($attires->total() == 0) return $this->failed(msg: "Parameters missing!");

            $allCategoryMap = [];

            if ($this->uid) {
                $allCategoryMap[$checkCat->id] = $checkCat;
                foreach ($checkCat->subcategories ?? [] as $sub) {
                    $allCategoryMap[$sub->id] = $sub;
                }
            }

            $rates = RateController::getRates();

            $templateDatas = [];
            foreach ($attires->items() as $attire) {
                $cateRow = $allCategoryMap[$attire->category_id] ?? null;
                $templateDatas[] = HelperController::getCaricatureData(catRow: $cateRow, item: $attire, rates: $rates);
            }

            $paginationData = PaginationController::getPagination($attires, $filter, $checkCat->cat_link);

            $response = [
                "datas" => $templateDatas,
                "pagination" => $paginationData,
                "isLastPage" => $attires->currentPage() >= $attires->lastPage(),
                "pageNo" => $attires->currentPage(),
            ];

            if ($requiredSeo) {
                $response = [
                    "new_api" => true,
                    "page_link" => $checkCat->cat_link,
                    "filter_link" => $checkCat->cat_link,
                    "templateCount" => HelperController::getTemplateCount($attires->total(), $checkCat->primary_keyword),
                    "isLastPage" => $attires->currentPage() >= $attires->lastPage(),
                    "pageNo" => $attires->currentPage(),
                    "category_id" => $checkCat->id,
                    "banner" => $checkCat->banner ? HelperController::$mediaUrl . $checkCat->banner : null,
                    "string_id" => $checkCat->string_id,
                    "datas" => $templateDatas,
                    "pagination" => $paginationData
                ];

                $subCatArray = CaricatureController::getSubCategories($checkCat);

                $response['parent_cats'] = $subCatArray['parentTags'];
                $response['sub_category'] = $subCatArray['subCats'];

                $seoDatas = collect($checkCat)->only(['h1_tag', 'h2_tag', 'meta_title', 'meta_desc', 'short_desc', 'long_desc', 'tag_line']);
                $seoDatas['tag_line'] = $seoDatas->get('tag_line') ?? CaricatureController::$defaultTagLine;

                $response['seo'] = $seoDatas;
                $response['top_keywords'] = (isset($checkCat->top_keywords)) ? HelperController::getTopKeywords(json_decode($checkCat->top_keywords)) : [];
                $response['contents'] = isset($checkCat->contents) ? ContentManager::getContentsPath(rates: $rates, contents: json_decode(StorageUtils::get($checkCat->contents)), uid: $this->uid, cacheTag: $cacheTag, cacheKey: $contentCacheKey, doCache: $doCache) : [];

                $faqsResponse = ContentManager::faqsResponse(faqs: $checkCat->faqs, premiumKeyword: $checkCat->primary_keyword, cacheTag: $cacheTag, cacheKey: $faqCacheKey, doCache: $doCache);
                $response['faqs'] = $faqsResponse['faqs'];
                $response['faqs_title'] = $faqsResponse['faqs_title'];
                $response['canonical_link'] = PaginationController::buildCanonicalLink($checkCat->canonical_link, $checkCat->cat_link, $page);
                $response['pre_breadcrumb'] = self::getCategoryBreadcrumbs($checkCat);

                $data = PReviewController::getPReviews($this->uid, 1, $checkCat->string_id, 1);
                if ($data['success']) $response['reviews'] = $data['data'];
            }

            return ResponseHandler::sendRealResponse(new ResponseInterface(200, true, "Loading Success!", $response));
        };

//        if (HelperController::$cacheEnabled) {
//            $response = Cache::tags([$cacheTag])->remember($cacheKey, HelperController::$cacheTimeOut, $callback);
//        } else {
        $response = $callback(false);
//        }

//        if (!$response['success'] || count($response['datas']) == 0) $response = $callback(false);

        if (isset($response['success']) && $response['success']) {
            $user_data = UserData::where("uid", $this->uid)->first();
            $url = $checkCat->cat_link;
            FbPixel::trackEvent(FacebookEvent::VIEW_CONTENT, $request, $user_data?->name, $user_data?->email, null, $url);
        }

        return ResponseHandler::sendEncryptedResponse($request, $response);
    }

    public function getAttire(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $requiredSeo = $request->input('seo', true);
        $id_name = $request->id;
        if (!$id_name) return $this->failed(msg: "Parameters missing!");

        $string_id = explode('-', $id_name)[0];
        $itemData = Attire::whereStringId($string_id)->whereStatus(1)->first();

        if (!$itemData) {
            if (is_numeric($id_name)) $itemData = Attire::whereIdName($id_name)->whereStatus(1)->first();
        }

        if (!$itemData) return $this->failed(msg: "Parameters missing");

        if (!$requiredSeo) {
            return $this->successed(datas: [
                "attire" => $itemData->preview_url
            ]);
        }

        $ipData = HelperController::getIpAndCountry($request);
        $userIp = $ipData['ip'];

        WebTemplateViewHistory::create([
            'user_id' => $this->uid,
            'product_id' => $itemData->string_id,
            'ip_address' => $userIp == '89.116.134.215' ? null : $userIp,
            'country' => $ipData['cn'],
            'fbc' => $request->cookie('_fbclid'),
            'fbp' => $request->cookie('_caid'),
            'gclid' => $request->cookie('_gclid'),
            'gcl_au' => $request->cookie('_gcl_au'),
            'ga' => $request->cookie('_ga'),
            'userAgent' => $request->header('User-Agent', 'Unknown'),
            'type' => 'caricature',
        ]);

        $last_24_hour_views = WebTemplateViewHistory::where('product_id', $itemData->string_id)->where('type', 'caricature')->where('created_at', '>=', now()->subDay())->count();

        $rates = RateController::getRates();
        $catRow = CaricatureCategory::findId(select: null, isStatus: 1, id: $itemData->category_id);
        $item_rows = HelperController::getCaricatureData(
            catRow: $catRow,
            item: $itemData,
            rates: $rates
        );

        $subCatArray = CaricatureController::getSubCategories($catRow);

        $cacheTag = CaricatureCategory::$preCacheTag . "attire_$itemData->string_id";
        $contentCacheKey = 'attire_content_' . $itemData->string_id;
        $faqCacheKey = 'attire_faq_' . $itemData->string_id;

        $pageUrl = $item_rows['page_link'];

        $suggestedDatas = [];
        if ($catRow) {
            $item_rows['category_name'] = $catRow->category_name;
            if ($catRow->parent) $item_rows['category_name'] = $catRow->category_name . ' ' . $catRow->parent['category_name'];
        }

        if ($catRow) {
            $usedCategoryMap = array_merge([$catRow->id], $catRow->child_cat_ids ?? []);
            $attiresQuery = Attire::whereIn('category_id', $usedCategoryMap)->where('id', '!=', $itemData->id)->where('status', 1);

            $attires = $attiresQuery->orderByRaw('id DESC')->paginate(20, ['*'], 'page', 1);

//            if ($attires->total() == 0) return $this->failed(msg: "Parameters missing!");

            $allCategoryMap = [];

            if ($this->uid) {
                $allCategoryMap[$catRow->id] = $catRow;
                foreach ($catRow->subcategories ?? [] as $sub) {
                    $allCategoryMap[$sub->id] = $sub;
                }
            }

            foreach ($attires->items() as $attire) {
                $cateRow = $allCategoryMap[$attire->category_id] ?? null;
                $suggestedDatas[] = HelperController::getCaricatureData(catRow: $cateRow, item: $attire, rates: $rates);
            }
        }

        $item_rows['ai_credit'] = $item_rows['payment']['inrVal'];
        $item_rows['last_24_hour_views'] = $last_24_hour_views;
        $item_rows['parent_cats'] = $subCatArray['parentTags'];
        $item_rows['sub_category'] = $subCatArray['subCats'];

        $item_rows['pre_breadcrumb'] = CaricatureController::getCategoryBreadcrumbs($catRow, $itemData->post_name, $pageUrl);

        $response['data'] = $item_rows;

        $response['suggested'] = $suggestedDatas;

        $response['canonical_link'] = PaginationController::buildCanonicalLink($itemData->canonical_link, $pageUrl, 1);
        $response['contents'] = isset($itemData->contents) ? ContentManager::getContentsPath(rates: $rates, contents: json_decode(StorageUtils::get($itemData->contents)), uid: $this->uid, cacheTag: $cacheTag, cacheKey: $contentCacheKey, doCache: true) : [];

        $faqsResponse = ContentManager::faqsResponse(faqs: $itemData->faqs, premiumKeyword: "", cacheTag: $cacheTag, cacheKey: $faqCacheKey, doCache: true);
        $response['faqs'] = $faqsResponse['faqs'];
        $response['faqs_title'] = $faqsResponse['faqs_title'];

        return $this->successed(datas: $response);

    }

    public static function getSubCategories(CaricatureCategory|int|null $category): array
    {
        if (is_int($category)) $category = CaricatureCategory::findId(select: null, isStatus: 1, id: $category);

        $parents = CaricatureCategory::query()->select(['id', 'id_name', 'category_name', 'category_thumb', 'cat_link'])->whereParentCategoryId(0)->where('total_templates', '>', 0)->whereStatus(1)->get();
        $parentTags = [];
        foreach ($parents as $parent) {
            $parentTags[] = [
                'id' => $parent->id,
                'category_name' => $parent->category_name,
                'category_thumb' => HelperController::$mediaUrl . $parent->category_thumb,
                'url' => $parent->cat_link,
                'link' => $parent->cat_link,
                'id_name' => $parent->id_name,
                'status' => 1,
            ];
        }

        if ($category) {
            if ($category->parent) {
                $parentCat = CaricatureCategory::findId(select: null, isStatus: 1, id: $category->parent['id']);
                return ["parentTags" => array_values($parentTags), "subCats" => self::getChilds($parentCat)];
            } else {
                return ["parentTags" => array_values($parentTags), "subCats" => self::getChilds($category)];
            }
        }

        return ["parentTags" => array_values($parentTags), "subCats" => []];
    }

    private static function getChilds(CaricatureCategory|null $category): array
    {
        $childs = [];
        if ($category && !$category->parent) {
            foreach ($category->subcategories as $subcategory) {
                $childs[] = [
                    'id' => $subcategory->id,
                    'category_name' => $subcategory->category_name,
                    'category_thumb' => HelperController::$mediaUrl . $subcategory->category_thumb,
                    'url' => $subcategory->cat_link,
                    'link' => $subcategory->cat_link,
                    'id_name' => $subcategory->id_name,
                    'status' => $subcategory->status,
                ];
            }
        }

        return array_values($childs);
    }

    public static function getCategoryBreadcrumbs(CaricatureCategory $cat = null, $last = null, $link = null): array
    {

        $pre_breadcrumb[] = [
            'value' => "Crafty Art",
            "link" => "https://www.craftyartapp.com",
            "openinnewtab" => 0,
            "nofollow" => 0
        ];

        $pre_breadcrumb[] = [
            'value' => "Caricature",
            "link" => "https://www.craftyartapp.com/caricature-maker",
            "openinnewtab" => 0,
            "nofollow" => 0
        ];

        if ($cat) {
            if ($cat->parent) {
                $pre_breadcrumb[] = [
                    'value' => $cat->parent['category_name'],
                    "link" => $cat->parent['cat_link'],
                    "openinnewtab" => 0,
                    "nofollow" => 0
                ];
            }
            $pre_breadcrumb[] = [
                'value' => $cat->category_name,
                "link" => $cat->cat_link,
                "openinnewtab" => 0,
                "nofollow" => 0
            ];
        }

        if (is_null($last) && !empty($pre_breadcrumb)) {
//            $lastIndex = count($pre_breadcrumb) - 1;
//            unset($pre_breadcrumb[$lastIndex]['link']);
//            unset($pre_breadcrumb[$lastIndex]['openinnewtab']);
//            unset($pre_breadcrumb[$lastIndex]['nofollow']);
        } else {
            if ($last) $pre_breadcrumb[] = ['value' => $last, "link" => $link];
        }

        return $pre_breadcrumb;
    }

}
