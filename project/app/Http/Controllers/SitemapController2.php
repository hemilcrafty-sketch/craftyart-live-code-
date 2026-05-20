<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Models\Category;
use App\Models\Design;
use App\Models\NewCategory;
use App\Models\SpecialKeyword;
use App\Models\SpecialPage;
use App\Models\VirtualCategory;
use Illuminate\Http\Request;

class SitemapController2 extends ApiController
{

    private static array $allLandingPages = [
        ['name' => 'Invitation', 'path' => 'invitation'],
        ['name' => 'Marketing', 'path' => 'marketing'],
        ['name' => 'Business', 'path' => 'business'],
        ['name' => 'Quotes', 'path' => 'quotes'],
        ['name' => 'Resume', 'path' => 'resume'],
        ['name' => 'Flyer', 'path' => 'flyer'],
        ['name' => 'Calendar', 'path' => 'calendar'],
        ['name' => 'Bridal Shower', 'path' => 'bridal-shower'],
        ['name' => 'Logo', 'path' => 'logos'],
        ['name' => 'Wedding', 'path' => 'wedding'],
        ['name' => 'Birthday', 'path' => 'birthday-invitation'],
        ['name' => 'Baby Shower', 'path' => 'baby-shower-invitation'],
        ['name' => 'Party', 'path' => 'party-invitation'],
        ['name' => 'Brochure', 'path' => 'brochure-design'],
        ['name' => 'Poster', 'path' => 'poster'],
        ['name' => 'Indian', 'path' => 'indian'],
        ['name' => 'Baptism', 'path' => 'baptism'],
        ['name' => 'Gujarati', 'path' => 'gujarati'],
        ['name' => 'English', 'path' => 'english'],
        ['name' => 'Hindu', 'path' => 'hindu'],
        ['name' => 'Muslim', 'path' => 'muslim'],
        ['name' => 'Punjabi', 'path' => 'punjabi'],
        ['name' => 'Christian', 'path' => 'christian'],
        ['name' => 'Tamil', 'path' => 'tamil'],
        ['name' => 'Pregnancy Announcement', 'path' => 'pregnancy-announcement'],
        ['name' => 'Birth Announcement', 'path' => 'birth-announcement'],
        ['name' => 'Christening', 'path' => 'christening'],
        ['name' => 'Engagement', 'path' => 'engagement'],
        ['name' => 'Engagement Party', 'path' => 'engagement-party'],
        ['name' => 'Party Invitation', 'path' => 'party-invitation'],
        ['name' => 'Haldi Ceremony', 'path' => 'haldi-ceremony'],
        ['name' => 'Rehearsal Dinner', 'path' => 'rehearsal-dinner'],
        ['name' => 'Save The Date', 'path' => 'save-the-date'],
        ['name' => 'South Indian', 'path' => 'south-indian'],
        ['name' => 'Biodata', 'path' => 'biodata'],
        ['name' => 'Cards', 'path' => 'cards'],
    ];

    private static array $allToolsPages = [
        ['name' => 'Background Remover', 'path' => 'background-remover'],
        ['name' => 'Brand kit', 'path' => 'brand-kit'],
        ['name' => 'Resize', 'path' => 'resize'],
        ['name' => 'Caricature', 'path' => 'caricature'],
        ['name' => 'Style kit', 'path' => 'style-kit'],
        ['name' => 'Customize Invitation', 'path' => 'customize-invitation'],
        ['name' => 'Qr Code Generator', 'path' => 'qr-code-generator'],
        // ['name' => 'Image Compressor', 'path' => 'tools/image-compressor'],
        // ['name' => 'Image Enhancer', 'path' => 'tools/image-enhancer'],
        // ['name' => 'Image Resizer', 'path' => 'tools/image-resizer'],
        // ['name' => 'Jpg to Pdf', 'path' => 'tools/jpg-to-pdf'],
        // ['name' => 'Pdf Editor', 'path' => 'tools/pdf-editor'],
        // ['name' => 'Png to Jpg', 'path' => 'tools/png-to-jpg'],
        // ['name' => 'Pdf to Jpg', 'path' => 'tools/pdf-to-jpg'],
        // ['name' => 'Pdf Merger', 'path' => 'tools/pdf-merger'],
        // ['name' => 'Reverse Image', 'path' => 'tools/reverse-image'],
    ];

    protected string $domain = "https://www.craftyartapp.com/";

    protected $feildSitemap = 'new-sitemap/';
    protected $feildnewCategories;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->feildnewCategories = $this->feildSitemap . 'categoriesV2';
    }

    function sitemap(Request $request): string
    {

        $domainName = "www.craftyartapp.com/";
        $mediaUrl = HelperController::$mediaUrl;

        $specialPages = SpecialPage::select('page_slug')->where("status", 1)->where('no_index', 0)->whereNull('canonical_link')->orderBy('created_at', 'DESC')->get();
        $catData = Category::select('id', 'id_name')->where("status", 1)->where('no_index', 0)->whereNull('canonical_link')->orderBy('created_at', 'DESC')->get();
        $newCats = NewCategory::select('id', 'id_name', 'parent_category_id', 'category_name')->where("status", 1)->where('no_index', 0)->whereNull('canonical_link')->orderBy('created_at', 'DESC')->get();
        $tempData = Design::select('id_name', 'thumb_array', 'post_name', 'meta_description')->where("status", 1)->where('no_index', 0)->whereNull('canonical_link')->orderBy('created_at', 'DESC')->get();
        $specialData = SpecialKeyword::select('name')->where("status", 1)->where('no_index', 0)->whereNull('canonical_link')->orderBy('created_at', 'DESC')->get();

        $linkArray = array();

        foreach ($specialPages as $page) {
            $linkArray[] = array(
                "id_name" => "https://" . $domainName . $page->page_slug
            );
        }

        foreach ($catData as $cat) {
            if (Design::where('category_id', $cat->id)->where("status", 1)->exists()) {
                $linkArray[] = array(
                    "id_name" => "https://" . $domainName . "templates/" . $cat->id_name
                );
            }
        }

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

    public function sitemapIndex()
    {
        $xml = $this->xmlHeader('sitemapindex');
        $xml .= $this->sitemapTag($this->feildnewCategories . '.xml', $this->getNewCategoriesSitemapLastMod());
        $xml .= $this->sitemapTag($this->feildSitemap . 'categoriesV1.xml', $this->getCategoriesSitemapLastMod());
        $xml .= $this->sitemapTag($this->feildSitemap . 'others.xml');
        $xml .= '</sitemapindex>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function parentSitemap($parent)
    {
        $xml = $this->xmlHeader('sitemapindex');

        $parentCategory = $this->getParentCategoryByIdName($parent);

        if ($this->isSpecialPageAndKeywordAvail($parentCategory->id)) {
            $xml .= $this->sitemapTag(
                "$this->feildnewCategories/{$parentCategory->id_name}/pages.xml",
                $this->getSAndKLastMod($parentCategory->id)
            );
        }

        $children = $this->getValidChildren($parentCategory->id);

        foreach ($children as $child) {
            $lastMod = $this->getCategoryLastMod($child->id, $child->updated_at);
            $xml .= $this->sitemapTag("$this->feildnewCategories/{$parentCategory->id_name}/{$child->id_name}.xml", $lastMod);
        }

        $xml .= '</sitemapindex>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function childSitemap($parent, $child)
    {
        $xml = $this->xmlHeader('urlset', 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"');
        $mediaUrl = HelperController::$mediaUrl;
        $parentCategory = $this->getParentCategoryByIdName($parent);

        if ($child === "pages") {
            $xml .= $this->specialPagesAndKeywords($parentCategory->id);
            $xml .= '</urlset>';
            return response($xml, 200)->header('Content-Type', 'application/xml');
        }

        $childCategory = $this->getChildCategoryByIdName($child, $parentCategory->id);
        $xml .= $this->specialPagesAndKeywords($childCategory->id);

        $designs = Design::where('new_category_id', $childCategory->id)
            ->where('status', 1)->where('no_index', 0)->get();

        foreach ($designs as $design) {
            $xml .= "<url><loc>{$this->escapeXml("https://{$this->domain}templates/p/{$design->id_name}")}</loc>";
            foreach ((array)json_decode($design->thumb_array) as $thumb) {
                $xml .= "<image:image>
                        <image:loc>{$this->escapeXml($mediaUrl . $thumb)}</image:loc>
                        <image:title>{$this->escapeXml($design->post_name ?? '')}</image:title>
                        <image:caption>{$this->escapeXml($design->meta_description ?? '')}</image:caption>
                    </image:image>";
            }
            try {
                $xml .= $this->extraModification($design->updated_at);
            } catch (\Exception $e) {
            }
            $xml .= "</url>" . PHP_EOL;
        }

        $xml .= '</urlset>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function newCategoriesSitemap()
    {
        $xml = $this->xmlHeader('sitemapindex');

        $parents = $this->getValidParentCategories();

        foreach ($parents as $parent) {
            $xml .= $this->sitemapTag("{$this->feildnewCategories}/{$parent->id_name}.xml", $this->getParentCategoryLastMod($parent));
        }

        $xml .= '</sitemapindex>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function categoriesSitemap()
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

    public function otherSitemap()
    {
        $xml = $this->xmlHeader('urlset');

        foreach (array_merge(self::$allLandingPages, self::$allToolsPages) as $page) {
            $xml .= $this->urlTag($page['path']);
        }

        foreach (SpecialPage::where('cat_id', 0)->where('status', 1)->where('no_index', 0)->get() as $page) {
            $xml .= $this->urlTag($page->page_slug, $page->updated_at);
        }

        foreach (SpecialKeyword::where('cat_id', 0)->where('status', 1)->where('no_index', 0)->get() as $keyword) {
            $xml .= $this->urlTag('k/' . $keyword->name, $keyword->updated_at);
        }

        $xml .= '</urlset>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    private function getParentCategoryByIdName($idName)
    {
        return NewCategory::where('id_name', $idName)
            ->where('status', 1)
            ->where('parent_category_id', 0)
            ->firstOrFail();
    }

    private function getChildCategoryByIdName($idName, $parentId)
    {
        return NewCategory::where('id_name', $idName)
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
            ->get()
            ->filter(fn($child) => $this->isSpecialPageAndKeywordAvail($child->id) ||
                $this->isTemplateAvail($child->id)
            );
    }

    private function getValidParentCategories()
    {
        return NewCategory::where('status', 1)
            ->where('no_index', 0)
            ->where('parent_category_id', 0)
            ->get()
            ->filter(fn($parent) => $this->getValidChildren($parent->id)->isNotEmpty() ||
                $this->isSpecialPageAndKeywordAvail($parent->id)
            );
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
        try {
            if (!empty($lastMod)) {
                $tag .= $this->extraModification($lastMod);
            }
        } catch (\Exception $e) {
        }

        $tag .= '</sitemap>' . PHP_EOL;
        return $tag;
    }

    /**
     * @throws \Exception
     */
    private function extraModification($lastMod): string
    {
        $isoTime = (new \DateTime($lastMod))->format('Y-m-d\TH:i:s.v\Z');
        return '<lastmod>' . $isoTime . '</lastmod>' . PHP_EOL . '<changefreq>daily</changefreq>' . PHP_EOL . '<priority>0.9</priority>';
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
        $specialPages = SpecialPage::where('cat_id', $catId)->where('status', 1)->where('no_index', 0)->get();
        foreach ($specialPages as $page) {
            $xml .= $this->urlTag($page->page_slug, $page->updated_at);
        }

        $keywords = SpecialKeyword::where('cat_id', $catId)->where('status', 1)->where('no_index', 0)->get();
        foreach ($keywords as $keyword) {
            $xml .= $this->urlTag('k/' . $keyword->name, $page->updated_at);
        }
        return $xml;
    }

    private function getLatestUpdatedAt($query)
    {
        return optional($query->orderByDesc('updated_at')->first())->updated_at;
    }

    private function getSAndKLastMod(int $catId): string
    {
        $dates = array_filter([
            $this->getLatestUpdatedAt(SpecialPage::where('cat_id', $catId)->where('status', 1)->where('no_index', 0)),
            $this->getLatestUpdatedAt(SpecialKeyword::where('cat_id', $catId)->where('status', 1)->where('no_index', 0))
        ]);

        return $dates ? max($dates) : '';
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

    private function getNewCategoriesSitemapLastMod(): ?string
    {
        $parents = NewCategory::where('status', 1)
            ->where('no_index', 0)
            ->where('parent_category_id', 0)
            ->get()
            ->filter(fn($parent) => NewCategory::where('parent_category_id', $parent->id)
                    ->where('status', 1)
                    ->where('no_index', 0)
                    ->get()
                    ->filter(fn($child) => $this->isSpecialPageAndKeywordAvail($child->id) || $this->isTemplateAvail($child->id)
                    )->isNotEmpty()
                || $this->isSpecialPageAndKeywordAvail($parent->id)
            );

        $dates = [];

        foreach ($parents as $parent) {
            if ($lastMod = $this->getParentCategoryLastMod($parent)) {
                $dates[] = $lastMod;
            }
        }

        return $dates ? max($dates) : null;
    }

    private function getParentCategoryLastMod($parent): ?string
    {
        $dates = [];

        if ($parent->updated_at) $dates[] = $parent->updated_at;

        $childCats = NewCategory::where('parent_category_id', $parent->id)
            ->where('status', 1)
            ->where('no_index', 0)
            ->get();

        foreach ($childCats as $child) {
            if ($child->updated_at) $dates[] = $child->updated_at;

            $dates[] = $this->getLatestUpdatedAt(SpecialPage::where('cat_id', $child->id)->where('status', 1)->where('no_index', 0));
            $dates[] = $this->getLatestUpdatedAt(SpecialKeyword::where('cat_id', $child->id)->where('status', 1)->where('no_index', 0));
            $dates[] = $this->getLatestUpdatedAt(Design::where('new_category_id', $child->id)->where('status', 1));
        }

        $dates[] = $this->getLatestUpdatedAt(SpecialPage::where('cat_id', $parent->id)->where('status', 1)->where('no_index', 0));
        $dates[] = $this->getLatestUpdatedAt(SpecialKeyword::where('cat_id', $parent->id)->where('status', 1)->where('no_index', 0));

        $dates = array_filter($dates);
        return $dates ? max($dates) : null;
    }

    private function getCategoriesSitemapLastMod(): ?string
    {
        $dates = array_filter([
            Category::where('status', 1)->where('no_index', 0)->orderByDesc('updated_at')->value('updated_at'),
            VirtualCategory::where('status', 1)->where('no_index', 0)->orderByDesc('updated_at')->value('updated_at'),
        ]);

        return $dates ? max($dates) : null;
    }
}
