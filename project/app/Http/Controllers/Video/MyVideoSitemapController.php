<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Models\Video\VideoCategory;
use App\Models\Video\VideoTemplate;
use App\Models\Video\VideoVirtualCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MyVideoSitemapController extends ApiController
{
    private static array $staticPages = [
        ['path' => '/review', 'priority' => 0.7, 'frequency' => 'weekly'],
        ['path' => '/about-us', 'priority' => 0.7, 'frequency' => 'weekly'],
        ['path' => '/contact-us', 'priority' => 0.7, 'frequency' => 'weekly'],
        ['path' => '/privacy-policy', 'priority' => 0.7, 'frequency' => 'weekly'],
        ['path' => '/terms-conditions', 'priority' => 0.7, 'frequency' => 'weekly'],
        ['path' => '/refund-policy', 'priority' => 0.7, 'frequency' => 'weekly'],
    ];

    protected string $domain = 'https://www.myvideoinvites.com/';

    protected int $chunkLimit = 5000;

    protected string $sitemapPath = 'sitemap/';

    public function __construct(Request $request)
    {
        parent::__construct($request);
//        if ($url = env('MYVIDEO_FRONTEND_URL')) {
//            $this->domain = rtrim((string) $url, '/') . '/';
//        }
    }

    /**
     * MAIN INDEX METHOD
     */
    public function sitemapIndex(): Response
    {
        $xml = $this->xmlHeader('sitemapindex');

        // Add static segments
        $xml .= $this->sitemapIndexTag('category.xml');
        $xml .= $this->sitemapIndexTag('others.xml');
        $xml .= $this->sitemapIndexTag('virtualcategory.xml');

        // Add chunked template segments
        $totalTemplates = $this->getTemplateCount();
        $pages = (int) ceil($totalTemplates / $this->chunkLimit);

        for ($i = 1; $i <= $pages; $i++) {
            $xml .= $this->sitemapIndexTag("template-{$i}.xml");
        }

        $xml .= '</sitemapindex>';

        return $this->generateXmlResponse($xml);
    }

    /**
     * TEMPLATE SITEMAP METHOD (Chunked)
     */
    public function templateSitemap($page): Response
    {
        $page = max(1, (int) $page);
        $offset = ($page - 1) * $this->chunkLimit;

        $templates = VideoTemplate::where('status', 1)
            ->where('no_index', 0)
            ->orderBy('id', 'asc')
            ->offset($offset)
            ->limit($this->chunkLimit)
            ->get();

        $xml = $this->xmlHeader('urlset', 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"');

        foreach ($templates as $tpl) {
            $loc = $this->videoTemplateLoc($tpl);
            if ($loc !== '') {
                $xml .= $this->urlTag($loc, $tpl->updated_at, $tpl->priority, $tpl->frequency, $tpl);
            }
        }

        $xml .= '</urlset>';

        return $this->generateXmlResponse($xml);
    }

    /**
     * CATEGORY SITEMAP (FLATTEN)
     */
    public function categorySitemap(): Response
    {
        $categories = VideoCategory::where('status', 1)
            ->where('no_index', 0)
            ->whereNotNull('slug')
            ->orderBy('id', 'asc')
            ->get();

        $xml = $this->xmlHeader('urlset');

        foreach ($categories as $cat) {
            $loc = rtrim($this->domain, '/') . $cat->slug;
            $xml .= $this->urlTag($loc, $cat->updated_at, $cat->priority, $cat->frequency);
        }

        $xml .= '</urlset>';

        return $this->generateXmlResponse($xml);
    }

    /**
     * VIRTUAL CATEGORY SITEMAP (FLATTEN)
     */
    public function virtualCategorySitemap(): Response
    {
        $virtualCats = VideoVirtualCategory::where('status', 1)
            ->where('no_index', 0)
            ->whereNotNull('slug')
            ->orderBy('id', 'asc')
            ->get();

        $xml = $this->xmlHeader('urlset');

        foreach ($virtualCats as $vcat) {
            $loc = rtrim($this->domain, '/') . $vcat->slug;
            $xml .= $this->urlTag($loc, $vcat->updated_at, $vcat->priority, $vcat->frequency);
        }

        $xml .= '</urlset>';

        return $this->generateXmlResponse($xml);
    }

    /**
     * OTHER SITEMAP (Static Pages)
     */
    public function otherSitemap(): Response
    {
        $xml = $this->xmlHeader('urlset');

        foreach (self::$staticPages as $page) {
            $loc = rtrim($this->domain, '/') . $page['path'];
            $xml .= $this->urlTag($loc, date('Y-m-d H:i:s'), $page['priority'], $page['frequency']);
        }

        $xml .= '</urlset>';

        return $this->generateXmlResponse($xml);
    }

    /* --- HELPERS --- */

    private function getTemplateCount(): int
    {
        return VideoTemplate::where('status', 1)->where('no_index', 0)->count();
    }

    private function generateXmlResponse(string $xml): Response
    {
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    private function xmlHeader(string $root, string $extra = ''): string
    {
        $ns = 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        if ($root === 'urlset') {
            $ns .= ' xmlns:xhtml="http://www.w3.org/1999/xhtml"';
        }

        return '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL .
            '<' . $root . ' ' . $ns . ' ' . $extra . '>' . PHP_EOL;
    }

    private function sitemapIndexTag(string $path): string
    {
        $loc = htmlspecialchars(rtrim($this->domain, '/') .'/'. $this->sitemapPath . $path, ENT_XML1);
        return "<sitemap><loc>{$loc}</loc></sitemap>" . PHP_EOL;
    }

    private function urlTag(string $url, $lastMod, $priority = 0.9, $freq = 'daily', $model = null): string
    {
        $loc = htmlspecialchars($url, ENT_XML1);
        $priorityValue = number_format($priority ?: 0.9, 2);

        try {
            $isoTime = (new \DateTime($lastMod))->format('Y-m-d\TH:i:s.v\Z');
        } catch (\Exception $e) {
            $isoTime = date('Y-m-d\TH:i:s.v\Z');
        }

        $xml = "  <url>" . PHP_EOL;
        $xml .= "    <loc>{$loc}</loc>" . PHP_EOL;
        $xml .= "    <lastmod>{$isoTime}</lastmod>" . PHP_EOL;
        $xml .= "    <changefreq>{$freq}</changefreq>" . PHP_EOL;
        $xml .= "    <priority>{$priorityValue}</priority>" . PHP_EOL;

        if ($model instanceof VideoTemplate && !empty($model->video_thumb)) {
            $mediaUrl = HelperController::$mediaUrl;
            $xml .= "    <image:image>" . PHP_EOL;
            $xml .= "      <image:loc>" . htmlspecialchars($mediaUrl . $model->video_thumb, ENT_XML1) . "</image:loc>" . PHP_EOL;
            $xml .= "      <image:title>" . htmlspecialchars($model->video_name ?? '', ENT_XML1) . "</image:title>" . PHP_EOL;
            $xml .= "    </image:image>" . PHP_EOL;
        }

        $xml .= "  </url>" . PHP_EOL;
        return $xml;
    }

    /** Same rules as template row: slug path, else templates/p/{string_id}. */
    private function videoTemplateLoc(VideoTemplate $tpl): string
    {
        $slug = ($tpl->slug ?? '');
        return rtrim($this->domain, '/') . $slug;
    }
}
