<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Models\Caricature\Attire;
use App\Models\Caricature\CaricatureCategory;
use App\Models\Category;
use App\Models\Design;
use App\Models\NewCategory;
use App\Models\SpecialKeyword;
use App\Models\SpecialPage;
use App\Models\VirtualCategory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\ResponseFactory;

class SitemapController extends ApiController
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

        // ['name' => 'Jpg to Pdf', 'path' => 'tools/jpg-to-pdf'],
        // ['name' => 'Pdf Editor', 'path' => 'tools/pdf-editor'],
        // ['name' => 'Png to Jpg', 'path' => 'tools/png-to-jpg'],
        // ['name' => 'Pdf to Jpg', 'path' => 'tools/pdf-to-jpg'],
        // ['name' => 'Pdf Merger', 'path' => 'tools/pdf-merger'],
        // ['name' => 'Reverse Image', 'path' => 'tools/reverse-image'],
    ];

    private static array $aiToolsPages = [
        ['name' => 'Ghibli Style Image Generator', 'path' => 'ai-tools/ghibli-style-image-generator', 'priority' => 1.0, 'frequency' => 'daily'],
        ['name' => 'Image to Cartoon Generator', 'path' => 'ai-tools/image-to-cartoon', 'priority' => 1.0, 'frequency' => 'daily'],
        ['name' => 'Background Remover', 'path' => 'background-remover', 'priority' => 1.0, 'frequency' => 'daily'],
    ];

    protected string $domain = "https://www.craftyartapp.com/";

    protected string $fieldSitemap = 'sitemap/';
    protected string $fieldNewCategories;
    protected string $fieldCaricatureCategories;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->fieldNewCategories = $this->fieldSitemap . 'categories';
        $this->fieldCaricatureCategories = $this->fieldSitemap . 'caricatures';
    }

    function keywords(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $domainName = "www.craftyartapp.com/";

        $keywordPages = SpecialKeyword::select(['title', 'name'])->where("status", 1)->where('no_index', 0)->whereNull('canonical_link')->orderBy('created_at', 'DESC')->get();
        $specialPages = SpecialPage::select(['title', 'page_slug'])->where("status", 1)->where('no_index', 0)->whereNull('canonical_link')->orderBy('created_at', 'DESC')->get();
        $catData = Category::select(['id', 'id_name', 'category_name'])->where("status", 1)->where('no_index', 0)->whereNull('canonical_link')->orderBy('created_at', 'DESC')->get();

        $newCats = NewCategory::getAllCatsWithChilds(
            select: ['id', 'id_name', 'parent_category_id', 'category_name', 'cat_link'],
            filters: ['status' => 1, 'no_index' => 0, 'canonical_link' => ['null' => true]]);

        $cariCats = CaricatureCategory::getAllCatsWithChilds(
            select: ['id', 'id_name', 'parent_category_id', 'category_name', 'cat_link'],
            filters: ['status' => 1, 'no_index' => 0, 'canonical_link' => ['null' => true]]);

        $kPages = array();
        $sPages = array();
        $oldCatDatas = array();
        $catDatas = array();
        $lPages = array();
        $tPages = array();
        $aiPages = array();
        $caricatures = array();

        foreach ($keywordPages as $temp) {
            $kPages[] = array(
                "title" => $temp->title,
                "link" => "https://" . $domainName . "k/" . $temp->name
            );
        }

        foreach ($specialPages as $page) {
            $sPages[] = array(
                "title" => $page->title,
                "link" => "https://" . $domainName . $page->page_slug
            );
        }

        foreach ($catData as $cat) {
            if (Design::where('category_id', $cat->id)->where("status", 1)->exists()) {
                $oldCatDatas[] = array(
                    "title" => $cat->category_name,
                    "link" => "https://" . $domainName . "templates/" . $cat->id_name
                );
            }
        }

        foreach ($newCats as $newCat) {
            $childs = [];
            foreach ($newCat->subcategories ?? [] as $sub) {
                $childs[] = [
                    "title" => $sub->category_name,
                    "link" => $sub->cat_link,
                ];
            }
            $catDatas[] = [
                "title" => $newCat->category_name,
                "link" => $newCat->cat_link,
                "childs" => $childs,
            ];
        }

        foreach ($cariCats as $cariCat) {
            $childs = [];
            foreach ($cariCat->subcategories ?? [] as $sub) {
                $childs[] = [
                    "title" => $sub->category_name . " Caricature",
                    "link" => $sub->cat_link,
                ];
            }
            $caricatures[] = [
                "title" => $cariCat->category_name . " Caricature",
                "link" => $cariCat->cat_link,
                "childs" => $childs,
            ];
        }

        foreach (self::$allLandingPages as $page) {
            $lPages[] = array(
                "title" => $page['name'],
                "link" => "https://" . $domainName . $page['path']
            );
        }

        foreach (self::$allToolsPages as $page) {
            $tPages[] = array(
                "title" => $page['name'],
                "link" => "https://" . $domainName . $page['path']
            );
        }

        foreach (self::$aiToolsPages as $page) {
            $aiPages[] = array(
                "title" => $page['name'],
                "link" => "https://" . $domainName . $page['path']
            );
        }

        $res['success'] = true;
        $res['kPages'] = $kPages;
        $res['sPages'] = $sPages;
        $res['oldCatDatas'] = $oldCatDatas;
        $res['catDatas'] = $catDatas;
        $res['lPages'] = $lPages;
        $res['tPages'] = $tPages;
        $res['caricatures'] = $caricatures;
        $res['aiPages'] = $aiPages;

        return $this->successed(datas: $res);
    }

    function sitemap(Request $request): string
    {

        $domainName = "www.craftyartapp.com/";
        $mediaUrl = HelperController::$mediaUrl;

        $specialPages = SpecialPage::select(['page_slug'])->where("status", 1)->where('no_index', 0)->whereNull('canonical_link')->orderBy('created_at', 'DESC')->get();
//        $catData = Category::select(['id', 'id_name'])->where("status", 1)->where('no_index', 0)->whereNull('canonical_link')->orderBy('created_at', 'DESC')->get();
        $newCats = NewCategory::select(['id', 'id_name', 'parent_category_id', 'category_name'])->where("status", 1)->where('no_index', 0)->whereNull('canonical_link')->orderBy('created_at', 'DESC')->get();
        $tempData = Design::select(['id_name', 'thumb_array', 'post_name', 'meta_description'])->where("status", 1)->where('no_index', 0)->whereNull('canonical_link')->orderBy('created_at', 'DESC')->get();
        $specialData = SpecialKeyword::select(['name'])->where("status", 1)->where('no_index', 0)->whereNull('canonical_link')->orderBy('created_at', 'DESC')->get();

        $linkArray = array();

        foreach ($specialPages as $page) {
            $linkArray[] = array(
                "id_name" => "https://" . $domainName . $page->page_slug
            );
        }

//        foreach ($catData as $cat) {
//            if (Design::where('category_id', $cat->id)->where("status", 1)->exists()) {
//                $linkArray[] = array(
//                    "id_name" => "https://" . $domainName . "templates/" . $cat->id_name
//                );
//            }
//        }

        foreach ($newCats as $newCat) {
            if (Design::where('new_category_id', $newCat->id)->where("status", 1)->exists()) {
                $linkArray[] = array(
                    "id_name" => $this->getParentCat($domainName, $newCat)["link"]
                );
            }
        }

        foreach ($specialData as $temp) {
            $linkArray[] = array(
                "id_name" => "https://" . $domainName . "k/" . $temp->name
            );
        }

        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $sitemap .= '<urlset xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . PHP_EOL;

        foreach (self::$allLandingPages as $page) {
            $sitemap .= '<url>' . PHP_EOL;
            $sitemap .= '<loc>' . htmlspecialchars("https://" . $domainName . $page['path']) . '</loc>' . PHP_EOL;
            $sitemap .= '</url>' . PHP_EOL;
        }

        foreach (self::$aiToolsPages as $page) {
            $sitemap .= '<url>' . PHP_EOL;
            $sitemap .= '<loc>' . htmlspecialchars("https://" . $domainName . $page['path']) . '</loc>' . PHP_EOL;
            $sitemap .= '</url>' . PHP_EOL;
        }

        foreach (self::$allToolsPages as $page) {
            $sitemap .= '<url>' . PHP_EOL;
            $sitemap .= '<loc>' . htmlspecialchars("https://" . $domainName . $page['path']) . '</loc>' . PHP_EOL;
            $sitemap .= '</url>' . PHP_EOL;
        }

        foreach ($linkArray as $url) {
            $sitemap .= '<url>' . PHP_EOL;
            $sitemap .= '<loc>' . htmlspecialchars($url['id_name']) . '</loc>' . PHP_EOL;
            $sitemap .= '</url>' . PHP_EOL;
        }

        foreach ($tempData as $temp) {
            $sitemap .= '<url>' . PHP_EOL;
            $sitemap .= '<loc>' . htmlspecialchars("https://" . $domainName . "templates/p/" . $temp->id_name) . '</loc>' . PHP_EOL;

            $thumbArray = json_decode($temp->thumb_array);
            if (is_array($thumbArray)) {
                foreach ($thumbArray as $thumb) {
                    $sitemap .= '<image:image>' . PHP_EOL;
                    $sitemap .= '<image:loc>' . htmlspecialchars($mediaUrl . $thumb) . '</image:loc>' . PHP_EOL;
                    $sitemap .= '<image:title>' . htmlspecialchars($temp->post_name) . '</image:title>' . PHP_EOL;
                    $sitemap .= '<image:caption>' . htmlspecialchars($temp->meta_description) . '</image:caption>' . PHP_EOL;
                    $sitemap .= '</image:image>' . PHP_EOL;
                }
            }

            $sitemap .= '</url>' . PHP_EOL;
        }

        $sitemap .= '</urlset>';

        return $sitemap;
    }

    private function getParentCat($domainName, $newCat): array
    {
        $idName = $newCat->id_name;
        if ($newCat->parent_category_id != 0) {
            $res = NewCategory::select('id', 'id_name', 'parent_category_id', 'category_name')->where("id", $newCat->parent_category_id)->where("status", 1)->first();
            if ($res) {
                $idName = $res->id_name . '/' . $newCat->id_name;
            }
        }

        return array(
            "title" => $newCat->category_name,
            "link" => "https://" . $domainName . "templates/" . $idName
        );
    }

    public function sitemapIndex(): Response
    {
        $xml = $this->xmlHeader('sitemapindex');
        $xml .= $this->sitemapTag($this->fieldNewCategories . '.xml');
        if($this->isOrphanPageAvailable()) {
            $xml .= $this->sitemapTag($this->fieldSitemap . 'pages.xml');
        }
        $xml .= $this->sitemapTag($this->fieldSitemap . 'caricatures.xml');
        $xml .= $this->sitemapTag($this->fieldSitemap . 'others.xml');
        $xml .= '</sitemapindex>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function parentSitemap($parent): Response
    {
        $xml = $this->xmlHeader('sitemapindex');

        $parentCategory = $this->getParentCategoryByIdName($parent);

        $xml .= $this->sitemapTag("$this->fieldNewCategories/{$parentCategory->id_name}/pages.xml");

        $children = $this->getValidChildren($parentCategory->id);

        foreach ($children as $child) {
            $xml .= $this->sitemapTag("$this->fieldNewCategories/{$parentCategory->id_name}/{$child->id_name}.xml");
        }

        $xml .= '</sitemapindex>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function childSitemap($parent, $child): Response
    {
        $xml = $this->xmlHeader('urlset', 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"');

        $parentCategory = $this->getParentCategoryByIdName($parent);

        if ($child === "pages") {
            if($parentCategory) {
                $xml .= '<url><loc>' . $this->escapeXml($this->domain . "templates/".$parent) . '</loc>';
                try {
                    $xml .= $this->extraModification(
                        $parentCategory->updated_at,
                        $parentCategory->priority ?? 0.90,
                        $parentCategory->frequency ?? 'daily'
                    );
                } catch (\Exception $e) {
                }
                $xml .= '</url>' . PHP_EOL;
            }
            $xml .= $this->specialPagesAndKeywords($parentCategory->id);

            $xml .= $this->getTemplatePagesByCat($parentCategory->id);

            $xml .= '</urlset>';
            return response($xml, 200)->header('Content-Type', 'application/xml');
        }

        $childCategory = $this->getChildCategoryByIdName($child, $parentCategory->id);

        $xml .= '<url><loc>' . $this->escapeXml($this->domain . "templates/".$parent."/".$child) . '</loc>';
        try {
            $xml .= $this->extraModification(
                $childCategory->updated_at,
                $childCategory->priority ?? 0.90,
                $childCategory->frequency ?? 'daily'
            );
        } catch (\Exception $e) {
        }
        $xml .= '</url>' . PHP_EOL;

        $xml .= $this->specialPagesAndKeywords($childCategory->id);

        $xml .= $this->getTemplatePagesByCat($childCategory->id);

        $xml .= '</urlset>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function getTemplatePagesByCat($catId)
    {
        $mediaUrl = HelperController::$mediaUrl;
        $xml = "";
        $designs = Design::select('id_name', 'post_name', 'meta_description', 'thumb_array', 'updated_at', 'priority', 'frequency')
            ->where('new_category_id', $catId)
            ->where('status', 1)->where('no_index', 0)->get();
        foreach ($designs as $design) {
            $xml .= "<url><loc>{$this->escapeXml("{$this->domain}templates/p/{$design->id_name}")}</loc>";
            foreach ((array)json_decode($design->thumb_array) as $thumb) {
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
        return $xml;
    }

    public function newCategoriesSitemap(): Response
    {
        $xml = $this->xmlHeader('sitemapindex');

        $parents = $this->getValidParentCategories();

        foreach ($parents as $parent) {
            $xml .= $this->sitemapTag("{$this->fieldNewCategories}/{$parent->id_name}.xml");
        }

        $xml .= '</sitemapindex>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function categoriesSitemap(): Response
    {
        $xml = $this->xmlHeader('urlset');

        foreach (Category::where('status', 1)->where('no_index', 0)->get() as $cat) {
            $xml .= $this->urlTag('templates/' . $cat->id_name, $cat->updated_at);
        }

        foreach (VirtualCategory::where('status', 1)->where('no_index', 0)->get() as $cat) {
            $xml .= $this->urlTag('templates/' . $cat->id_name, $cat->updated_at);
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

    public function caricatureSitemap(){
        $xml = $this->xmlHeader('sitemapindex');

        $xml .= $this->sitemapTag("$this->fieldCaricatureCategories/pages.xml");

        $parents = $this->getValidParentCaricatureCategories();

        foreach ($parents as $parent) {
            $xml .= $this->sitemapTag("{$this->fieldCaricatureCategories}/{$parent->id_name}.xml");
        }

        $xml .= '</sitemapindex>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function parentCaricatureSitemap($parent)
    {
        if($parent == "pages"){
            $xml = $this->xmlHeader('urlset', 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"');

            $xml .= $this->urlTag("caricature-maker", $this->getLatestUpdatedAt(CaricatureCategory::where('parent_category_id', 0)->where('no_index',0)->where('status', 1)));

            $xml .= '</urlset>';
            return response($xml, 200)->header('Content-Type', 'application/xml');
        }

        $xml = $this->xmlHeader('sitemapindex');

        $parentCategory = $this->getParentCaricatureCategoryByIdName($parent);

        $xml .= $this->sitemapTag("$this->fieldCaricatureCategories/{$parentCategory->id_name}/pages.xml");

        $children = $this->getValidCaricatureChildren($parentCategory->id);

        foreach ($children as $child) {
            $xml .= $this->sitemapTag("$this->fieldCaricatureCategories/{$parentCategory->id_name}/{$child->id_name}.xml");
        }

        $xml .= '</sitemapindex>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function childCaricatureSitemap($parent, $child)
    {
        $xml = $this->xmlHeader('urlset', 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"');
        $parentCategory = $this->getParentCaricatureCategoryByIdName($parent);

        if ($child === "pages") {
            if($parentCategory) {
                $xml .= '<url><loc>' . $this->escapeXml($this->domain . "caricature/".$parent) . '</loc>';
                try {
                    $xml .= $this->extraModification(
                        $parentCategory->updated_at,
                        $parentCategory->priority ?? 0.90,
                        $parentCategory->frequency ?? 'daily'
                    );
                } catch (\Exception $e) {
                }
                $xml .= '</url>' . PHP_EOL;
            }

            $xml .= self::getCaricaturePages($parentCategory->id);

            $xml .= '</urlset>';
            return response($xml, 200)->header('Content-Type', 'application/xml');
        }
        $childCategory = $this->getChildCaricatureCategoryByIdName($child, $parentCategory->id);

        $xml .= '<url><loc>' . $this->escapeXml($this->domain . "caricature/".$parent."/".$child) . '</loc>';
        try {
            $xml .= $this->extraModification(
                $childCategory->updated_at,
                $childCategory->priority ?? 0.90,
                $childCategory->frequency ?? 'daily'
            );
        } catch (\Exception $e) {
        }
        $xml .= '</url>' . PHP_EOL;

        $xml .= self::getCaricaturePages($childCategory->id);

        $xml .= '</urlset>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function getCaricaturePages($categoryId): string
    {
        $mediaUrl = HelperController::$mediaUrl;
        $attires = Attire::select('id_name', 'post_name', 'meta_description', 'thumbnail_url', 'updated_at', 'priority', 'frequency')
            ->whereCategoryId($categoryId)
            ->whereStatus(1)->whereNoIndex(0)->get();
        $xml = "";
        foreach ($attires as $attire) {
            $xml .= "<url><loc>{$this->escapeXml("{$this->domain}caricature/p/{$attire->id_name}")}</loc>";
            $xml .= "<image:image>
                    <image:loc>{$this->escapeXml($mediaUrl . $attire->thumbnail_url)}</image:loc>
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
        return $xml;
    }

    public function orphanPageSitemap(): Response|\Illuminate\Foundation\Application|\Illuminate\Routing\ResponseFactory
    {
        $xml = $this->xmlHeader('urlset', 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"');

        $specialPages = SpecialPage::select('page_slug', 'updated_at', 'priority', 'frequency')
            ->where('cat_id', 0)->where('status', 1)->where('no_index', 0)->get();
        foreach ($specialPages as $page) {
            $xml .= '<url><loc>' . $this->escapeXml($this->domain . $page->page_slug) . '</loc>';
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
            ->where('cat_id', 0)->where('status', 1)->where('no_index', 0)->get();
        foreach ($keywords as $keyword) {
            $xml .= '<url><loc>' . $this->escapeXml($this->domain . 'k/' . $keyword->name) . '</loc>';
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

        $xml .= $this->getTemplatePagesByCat(0);

        $xml .= '</urlset>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function isOrphanPageAvailable(): bool
    {
        $specialPageCount = SpecialPage::where('cat_id', 0)->where('status', 1)->where('no_index', 0)->count();

        $kPageCount = SpecialKeyword::where('cat_id', 0)->where('status', 1)->where('no_index', 0)->count();

        return ($kPageCount + $specialPageCount) != 0;
    }

    private function getOrphanSitemapLastMod()
    {
        $sPageLastUpdate = SpecialPage::where('cat_id', 0)->where('status', 1)->where('no_index', 0)
            ->orderByDesc('updated_at')
            ->first();

        $kPageLastUpdate = SpecialKeyword::where('cat_id', 0)->where('status', 1)->where('no_index', 0)
            ->orderByDesc('updated_at')
            ->first();

        $dates = array_filter([
            $sPageLastUpdate?->updated_at,
            $kPageLastUpdate?->updated_at,
        ]);

        return !empty($dates) ? max($dates) : null;
    }

    private function getParentCategoryByIdName($idName)
    {
        return NewCategory::select('id', 'id_name', 'updated_at', 'priority', 'frequency')
            ->where('id_name', $idName)
            ->where('status', 1)
            ->where('parent_category_id', 0)
            ->firstOrFail();
    }

    private function getParentCaricatureCategoryByIdName($idName)
    {
        return CaricatureCategory::select('id', 'id_name', 'updated_at', 'priority', 'frequency')
            ->where('id_name', $idName)
            ->where('status', 1)
            ->where('parent_category_id', 0)
            ->firstOrFail();
    }

    private function getChildCategoryByIdName($idName, $parentId)
    {
        return NewCategory::select('id', 'id_name', 'updated_at', 'priority', 'frequency')
            ->where('id_name', $idName)
            ->where('status', 1)
            ->where('no_index', 0)
            ->where('parent_category_id', $parentId)
            ->firstOrFail();
    }

    private function getChildCaricatureCategoryByIdName($idName, $parentId)
    {
        return CaricatureCategory::select('id', 'id_name', 'updated_at', 'priority', 'frequency')
            ->where('id_name', $idName)
            ->where('status', 1)
            ->where('no_index', 0)
            ->where('parent_category_id', $parentId)
            ->firstOrFail();
    }

    private function getValidChildren($parentId)
    {
        return NewCategory::where('status', 1)
            ->where('no_index', 0)
            ->where('parent_category_id', $parentId)
            ->get();
    }

    private function getValidCaricatureChildren($parentId)
    {
        return CaricatureCategory::where('status', 1)
            ->where('no_index', 0)
            ->where('parent_category_id', $parentId)
            ->get();
    }

    private function getValidParentCategories()
    {
        return NewCategory::where('status', 1)
            ->where('no_index', 0)
            ->where('parent_category_id', 0)
            ->get();
    }

    private function getValidParentCaricatureCategories()
    {
        return CaricatureCategory::where('status', 1)
            ->where('no_index', 0)
            ->where('parent_category_id', 0)
            ->get();
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
        $urlTag .= '</url>' . PHP_EOL;;
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

    private function isTemplateAvail($id)
    {
        return Design::where('new_category_id', $id)
            ->where('status', 1)
            ->where('no_index', 0)
            ->exists();
    }

    private function isSpecialPageAndKeywordAvail(int $catId): bool
    {
        return SpecialPage::where('cat_id', $catId)
                ->where('status', 1)
                ->where('no_index', 0)
                ->exists()
            ||
            SpecialKeyword::where('cat_id', $catId)
                ->where('status', 1)
                ->where('no_index', 0)
                ->exists();
    }


    private function specialPagesAndKeywords(int $catId): string
    {
        $xml = '';
        $specialPages = SpecialPage::select('page_slug', 'updated_at', 'priority', 'frequency')
            ->where('cat_id', $catId)->where('status', 1)->where('no_index', 0)->get();
        foreach ($specialPages as $page) {
            $xml .= '<url><loc>' . $this->escapeXml($this->domain . $page->page_slug) . '</loc>';
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
            ->where('cat_id', $catId)->where('status', 1)->where('no_index', 0)->get();
        foreach ($keywords as $keyword) {
            $xml .= '<url><loc>' . $this->escapeXml($this->domain . 'k/' . $keyword->name) . '</loc>';
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
        return $xml;
    }

    private function getLatestUpdatedAt($query)
    {
        return optional($query->orderByDesc('updated_at')->first())->updated_at;
    }

    private function getSAndKLastMod(int $catId): string
    {
        $category = NewCategory::find($catId);

        if (!$category) {
            return '';
        }

        $updatedAt = $category->updated_at;
        $childUpdatedAt = $category->child_updated_at;

        $latest = $childUpdatedAt && $childUpdatedAt > $updatedAt ? $childUpdatedAt : $updatedAt;

        return $latest ? $latest->toDateTimeString() : '';
    }

    private function getParentCaricaturePagesLastMod(int $catId)
    {
        $category = CaricatureCategory::find($catId);

        if (!$category) {
            return '';
        }
        $updatedAt = $category->updated_at;
        $childUpdatedAt = $category->child_updated_at;
        $latest = $childUpdatedAt && $childUpdatedAt > $updatedAt ? $childUpdatedAt : $updatedAt;

        return $latest ? $latest->toDateTimeString() : '';
    }

    private function getCategoryLastMod(int $catId, $fallbackDate): ?string
    {
        $dates = array_filter([
            $this->getLatestUpdatedAt(SpecialPage::where('cat_id', $catId)->where('status', 1)->where('no_index', 0)),
            $this->getLatestUpdatedAt(SpecialKeyword::where('cat_id', $catId)->where('status', 1)->where('no_index', 0)),
            $this->getLatestUpdatedAt(Design::where('new_category_id', $catId)->where('status', 1)),
            $fallbackDate
        ]);

        return $dates ? max($dates) : null;
    }

    private function getCaricatureCategoryLastMod(int $catId, $fallbackDate): ?string
    {
        $dates = array_filter([
            $this->getLatestUpdatedAt(Attire::where('category_id', $catId)->where('status', 1)),
            $fallbackDate
        ]);

        return $dates ? max($dates) : null;
    }

    private function getNewCategoriesSitemapLastMod($parent = 0): ?string
    {
        $categoryData = NewCategory::where('status', 1)
            ->where('no_index', 0)
            ->where('parent_category_id', $parent)
            ->orderByDesc('updated_at')
            ->first();

        $childUpdatedData = NewCategory::where('status', 1)
            ->where('no_index', 0)
            ->where('parent_category_id', $parent)
            ->orderByDesc('child_updated_at')
            ->first();

        $dates = array_filter([
            $categoryData?->updated_at,
            $childUpdatedData?->child_updated_at,
        ]);

        return !empty($dates) ? max($dates) : null;
    }

    public function getCaricatureCategoriesSitemapLastMod($parent = 0):?string {
        $categoryData = CaricatureCategory::where('status', 1)
            ->where('no_index', 0)
            ->where('parent_category_id', $parent)
            ->orderByDesc('updated_at')
            ->first();

        $childUpdatedData = CaricatureCategory::where('status', 1)
            ->where('no_index', 0)
            ->where('parent_category_id', $parent)
            ->orderByDesc('child_updated_at')
            ->first();

        $dates = array_filter([
            $categoryData?->updated_at,
            $childUpdatedData?->child_updated_at,
        ]);

        return !empty($dates) ? max($dates) : null;
    }

    private function getCategoriesSitemapLastMod(): ?string
    {
        $categoryData = Category::where('status', 1)
            ->where('no_index', 0)
            ->orderByDesc('updated_at')
            ->first();

        $virtualData = VirtualCategory::where('status', 1)
            ->where('no_index', 0)
            ->orderByDesc('updated_at')
            ->first();

        $dates = array_filter([
            $categoryData?->updated_at,
            $virtualData?->updated_at,
        ]);

        return !empty($dates) ? max($dates) : null;
    }

}
