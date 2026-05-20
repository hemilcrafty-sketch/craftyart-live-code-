<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\Controller;
use App\Http\Controllers\Utils\HelperController;
use App\Models\Caricature\Attire;
use App\Models\Caricature\CaricatureCategory;
use App\Models\Design;
use App\Models\NewCategory;
use App\Models\SpecialKeyword;
use App\Models\SpecialPage;
use App\Models\VirtualCategory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\ResponseFactory;

class NewSitemapController extends Controller
{
    private static array $allLandingPages = [
        ['name' => 'Invitation', 'path' => 'invitation', 'priority' => 1.0, 'frequency' => 'daily'],
        ['name' => 'Wedding', 'path' => 'wedding', 'priority' => 1.0, 'frequency' => 'daily'],
        ['name' => 'Birthday', 'path' => 'birthday-invitation', 'priority' => 0.7, 'frequency' => 'weekly'],
        ['name' => 'About Us', 'path' => 'about-us', 'priority' => 0.7, 'frequency' => 'weekly'],
        ['name' => 'Term & Condition', 'path' => 'term-condition', 'priority' => 0.7, 'frequency' => 'weekly'],
        ['name' => 'Privacy Policy', 'path' => 'privacy-policy', 'priority' => 0.7, 'frequency' => 'weekly'],
        ['name' => 'Contact Us', 'path' => 'contact-us', 'priority' => 0.7, 'frequency' => 'weekly'],
        ['name' => 'Faqs', 'path' => 'faqs', 'priority' => 0.7, 'frequency' => 'weekly'],
        ['name' => 'Plans', 'path' => 'plans', 'priority' => 0.7, 'frequency' => 'weekly'],
        ['name' => 'Review', 'path' => 'review', 'priority' => 0.7, 'frequency' => 'weekly'],
    ];

    private static array $allToolsPages = [
        ['name' => 'QR Code Generator', 'path' => 'qr-code-generator', 'priority' => 0.7, 'frequency' => 'weekly'],
        ['name' => 'Tools', 'path' => 'tools', 'priority' => 0.7, 'frequency' => 'weekly'],
        ['name' => 'Color Wheel', 'path' => 'tools/color-wheel', 'priority' => 0.7, 'frequency' => 'weekly'],
        ['name' => 'Image Compressor', 'path' => 'tools/image-compressor', 'priority' => 0.7, 'frequency' => 'weekly'],
        ['name' => 'Image to PDF', 'path' => 'tools/image-to-pdf', 'priority' => 0.7, 'frequency' => 'weekly'],
        ['name' => 'JPG to PDF', 'path' => 'tools/jpg-to-pdf', 'priority' => 0.7, 'frequency' => 'weekly'],
        ['name' => 'PDF Merger', 'path' => 'tools/pdf-merger', 'priority' => 0.7, 'frequency' => 'weekly'],
        ['name' => 'PDF to JPG', 'path' => 'tools/pdf-to-jpg', 'priority' => 0.7, 'frequency' => 'weekly'],
        ['name' => 'Png to JPG', 'path' => 'tools/png-to-jpg', 'priority' => 0.7, 'frequency' => 'weekly'],
    ];

    private static array $aiToolsPages = [
        ['name' => 'Ghibli Style Image Generator', 'path' => 'ai-tools/ghibli-style-image-generator', 'priority' => 1.0, 'frequency' => 'daily'],
        ['name' => 'Image to Cartoon Generator', 'path' => 'ai-tools/image-to-cartoon', 'priority' => 1.0, 'frequency' => 'daily'],
        ['name' => 'Background Remover', 'path' => 'background-remover', 'priority' => 1.0, 'frequency' => 'daily'],
    ];

    protected string $domain = "https://www.craftyartapp.com/";

    protected int $limit = 500;

    protected string $fieldSitemap = 'sitemap/';
    protected string $fieldNewCategories;
    protected string $fieldCaricatureCategories;

    public function __construct(Request $request)
    {
        $this->fieldNewCategories = $this->fieldSitemap . 'categories';
        $this->fieldCaricatureCategories = $this->fieldSitemap . 'caricatures';
    }

    public function sitemapIndex(): Response
    {
        $xml = $this->xmlHeader('sitemapindex');
        $xml .= $this->sitemapTag($this->fieldNewCategories . '.xml');
        if ($this->isOrphanPageAvailable()) {
            $xml .= $this->sitemapTag($this->fieldSitemap . 'pages.xml');
        }
        $xml .= $this->sitemapTag($this->fieldSitemap . 'caricatures.xml');
        $xml .= $this->sitemapTag($this->fieldSitemap . 'caricatures-template.xml');
        $xml .= $this->sitemapTag($this->fieldSitemap . 'others.xml');

        $totalDesigns = Design::where('status', 1)->where('no_index', 0)->count();
        $pages = ceil($totalDesigns / $this->limit);
        for ($i = 1; $i <= $pages; $i++) {
            $xml .= $this->sitemapTag($this->fieldSitemap . "templates-$i.xml");
        }

        $xml .= '</sitemapindex>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function templates($page = 1): Response
    {
        $xml = $this->xmlHeader('urlset', 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"');
        $offset = ((int) $page - 1) * $this->limit;

        $designs = Design::select('id_name', 'post_name', 'meta_description', 'thumb_array', 'updated_at', 'priority', 'frequency')
            ->where('status', 1)->where('no_index', 0)
            ->offset($offset)->limit($this->limit)
            ->orderBy('id', 'DESC')
            ->get();

        $mediaUrl = HelperController::$mediaUrl;

        foreach ($designs as $design) {
            $xml .= "<url><loc>{$this->escapeXml($this->domain . $design->sitemap_url)}</loc>";
            foreach ((array) json_decode($design->thumb_array) as $thumb) {
                $xml .= "<image:image>
                        <image:loc>{$this->escapeXml($mediaUrl . $thumb)}</image:loc>
                        <image:title>{$this->escapeXml($design->post_name ?? '')}</image:title>
                        <image:caption>{$this->escapeXml($design->meta_description ?? '')}</image:caption>
                    </image:image>";
            }

            try {
                $xml .= $this->extraModification(
                    $design->updated_at,
                    $design->priority ?? 0.90,
                    $design->frequency ?? 'daily'
                );

            } catch (\Exception $e) {
            }
            $xml .= "</url>" . PHP_EOL;
        }
        $xml .= '</urlset>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function newCategoriesSitemap(): Response
    {
        $xml = $this->xmlHeader('urlset');

        $categories = NewCategory::where('status', 1)->where('no_index', 0)->get();
        $virtualCategories = VirtualCategory::where('status', 1)->where('no_index', 0)->get();

        foreach ($categories as $cat) {
            $xml .= $this->urlTag($cat->sitemap_url, $cat->updated_at);
        }

        foreach ($virtualCategories as $vcat) {
            $xml .= $this->urlTag($vcat->sitemap_url, $vcat->updated_at);
        }

        $xml .= '</urlset>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }


    public function otherSitemap(): Response
    {
        $xml = $this->xmlHeader('urlset');

        foreach (array_merge(self::$allLandingPages, self::$allToolsPages, self::$aiToolsPages) as $page) {
            $xml .= '<url><loc>' . $this->escapeXml($this->domain . $page['path']) . '</loc>';

            // Array માંથી priority અને frequency લો, નહીં હોય તો default values
            $priority = $page['priority'] ?? 0.7;
            $frequency = $page['frequency'] ?? 'weekly';

            try {
                $xml .= $this->extraModification(date('Y-m-d H:i:s'), $priority, $frequency);
            } catch (\Exception $e) {
            }
            $xml .= '</url>' . PHP_EOL;
        }

        $xml .= '</urlset>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function caricatureSitemap(): Response
    {
        $xml = $this->xmlHeader('urlset');

        $categories = CaricatureCategory::where('status', 1)->where('no_index', 0)->get();

        foreach ($categories as $cat) {
            $xml .= $this->urlTag($cat->sitemap_url, $cat->updated_at);
        }

        $xml .= '</urlset>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function caricatureTemplateSitemap(): Response
    {
        $xml = $this->xmlHeader('urlset', 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"');

        $attires = Attire::select('id_name', 'post_name', 'meta_description', 'thumbnail_url', 'updated_at', 'priority', 'frequency')
            ->where('status', 1)->where('no_index', 0)->get();

        foreach ($attires as $attire) {
            $xml .= "<url><loc>{$this->escapeXml($this->domain . $attire->sitemap_url)}</loc>";
            $xml .= "<image:image>
                    <image:loc>{$this->escapeXml($attire->thumbnail_url)}</image:loc>
                    <image:title>{$this->escapeXml($attire->post_name ?? '')}</image:title>
                    <image:caption>{$this->escapeXml($attire->meta_description ?? '')}</image:caption>
                </image:image>";
            try {
                $xml .= $this->extraModification(
                    $attire->updated_at,
                    $attire->priority ?? 0.90,
                    $attire->frequency ?? 'daily'
                );
            } catch (\Exception $e) {
            }
            $xml .= "</url>" . PHP_EOL;
        }

        $xml .= '</urlset>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }


    public function orphanPageSitemap(): Response|Application|ResponseFactory
    {
        $xml = $this->xmlHeader('urlset', 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"');

        $specialPages = SpecialPage::select('page_slug', 'updated_at', 'priority', 'frequency')
            ->where('status', 1)->where('no_index', 0)->get();
        foreach ($specialPages as $page) {
            $xml .= '<url><loc>' . $this->escapeXml($this->domain . $page->sitemap_url) . '</loc>';
            try {
                $xml .= $this->extraModification(
                    $page->updated_at,
                    $page->priority ?? 0.90,
                    $page->frequency ?? 'daily'
                );
            } catch (\Exception $e) {
            }
            $xml .= '</url>' . PHP_EOL;
        }

        $keywords = SpecialKeyword::select('name', 'updated_at', 'priority', 'frequency')
            ->where('status', 1)->where('no_index', 0)->get();
        foreach ($keywords as $keyword) {
            $xml .= '<url><loc>' . $this->escapeXml($this->domain . $keyword->sitemap_url) . '</loc>';
            try {
                $xml .= $this->extraModification(
                    $keyword->updated_at,
                    $keyword->priority ?? 0.90,
                    $keyword->frequency ?? 'daily'
                );
            } catch (\Exception $e) {
            }
            $xml .= '</url>' . PHP_EOL;
        }

        $xml .= '</urlset>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function isOrphanPageAvailable(): bool
    {
        $specialPageCount = SpecialPage::where('cat_id', 0)->where('status', 1)->where('no_index', 0)->count();

        $kPageCount = SpecialKeyword::where('cat_id', 0)->where('status', 1)->where('no_index', 0)->count();

        return ($kPageCount + $specialPageCount) != 0;
    }

    private function xmlHeader(string $rootTag, string $extraXmlns = ""): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL .
            "<$rootTag xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xhtml=\"http://www.w3.org/1999/xhtml\" $extraXmlns >" . PHP_EOL;
    }

    private function urlTag(string $path, string $lastMod = ''): string
    {
        $urlTag = '<url><loc>' . $this->escapeXml($this->domain . $path) . '</loc>';
        try {
            if (!empty($lastMod)) {
                $urlTag .= $this->extraModification($lastMod);
            }
        } catch (\Exception $e) {
        }
        $urlTag .= '</url>' . PHP_EOL;
        ;
        return $urlTag;
    }

    private function sitemapTag(string $path, string $lastMod = ''): string
    {
        $tag = '<sitemap>' . PHP_EOL;
        $tag .= '<loc>' . $this->escapeXml($this->domain . $path) . '</loc>' . PHP_EOL;
        // Sitemap index માં lastmod, changefreq, priority નથી જોઈતા
        // માત્ર <loc> tag જ હોવું જોઈએ
        $tag .= '</sitemap>' . PHP_EOL;
        return $tag;
    }

    /**
     * @throws \Exception
     */
    private function extraModification($lastMod, $priority = 0.90, $frequency = 'daily'): string
    {
        $isoTime = (new \DateTime($lastMod))->format('Y-m-d\TH:i:s.v\Z');
        return '<lastmod>' . $isoTime . '</lastmod>' . PHP_EOL .
            '<changefreq>' . $frequency . '</changefreq>' . PHP_EOL .
            '<priority>' . number_format($priority, 2) . '</priority>';
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
