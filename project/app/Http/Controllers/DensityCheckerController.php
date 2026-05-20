<?php

namespace App\Http\Controllers;
use App\Models\NewCategory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class DensityCheckerController extends AppBaseController
{
    public function checkFromSlug(Request $request): View|Factory|Response|Application|ResponseFactory
    {
        $request->validate([
            'slug' => 'required|string',
            'type' => 'required|integer'
        ]);

        $slug = $request->input('slug');
        $type = $request->input('type');

        $url = self::getUrl($type,$slug);

//        dd($url);
        try {
            $html = file_get_contents($url);
        } catch (\Exception $e) {
            return response('<div class="text-danger">Failed to fetch content from URL: ' . e($url) . '</div>', 500);
        }

        $text = $this->extractText($html);

        preg_match_all('/[\p{L}\p{M}\p{N}\p{Sc}]+/u', mb_strtolower($text, 'UTF-8'), $matches);
        $words = $matches[0];

        $stopWords = ['the', 'is', 'and', 'a', 'an', 'in', 'on', 'at', 'to', 'for', 'our', 'by', 'of', 'with', 'this', 'that', 'it', 'as', 'be', 'are', 'was', 'from', 'or', 'but', 'we', 'you', 'your', 'they', 'their', 'has', 'have', 'not'];
        $words = array_values(array_filter($words, function ($word) use ($stopWords) {
            return !in_array($word, $stopWords) && mb_strlen($word) >= 3;
        }));

        $totalWords = count($words);
        $densities = [];

        for ($n = 1; $n <= 5; $n++) {
            $ngrams = $this->generateNGrams($words, $n);
            $frequencies = array_count_values($ngrams);

            $density = array_map(function ($count) use ($totalWords, $n) {
                return round(($count / ($totalWords - $n + 1)) * 100, 2);
            }, $frequencies);

            arsort($density);
            $densities[$n] = array_slice($density, 0, 10, true);
        }

        return view('density-checker.result-modal', compact('densities', 'totalWords', 'url'));
    }

    private function generateNGrams(array $words, int $n): array
    {
        $ngrams = [];
        $count = count($words);

        for ($i = 0; $i <= $count - $n; $i++) {
            $ngram = implode(' ', array_slice($words, $i, $n));
            $ngrams[] = $ngram;
        }

        return $ngrams;
    }

    private function extractText(string $html): string
    {
        $doc = $this->getDoc($html);
        $xpath = new \DOMXPath($doc);

        foreach ($xpath->query('//head | //*[@aria-hidden="true"] | //*[@style[contains(translate(., "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "display:none")]]') as $node) {
            $node->parentNode->removeChild($node);
        }

        $normalizedText = strtolower(html_entity_decode($doc->textContent));
        $normalizedText = preg_replace('/[\s]+/', ' ', $normalizedText);
        return trim($normalizedText);
    }

    private static function getUrl($type,$slug)
    {
        if($type == 4){
            $parentId = NewCategory::where('id_name', $slug)->value('parent_category_id');
            if($parentId != 0){
                $idName = NewCategory::where('id', $parentId)->value('id_name') ?? '';
                if(isset($idName)) $slug = $idName."/".$slug;
            }
        }
        return HelperController::getFrontendPageUrl($type, $slug);
    }

    public function checkPrimaryKeyword(Request $request): JsonResponse
    {
        $request->validate([
            'slug' => 'required|string',
            'type' => 'required|integer',
            'keyword' => 'required|string',
        ]);

        $slug = $request->slug;
        $type = $request->type;
        $keyword = strtolower(trim($request->keyword));

        $url = self::getUrl($type,$slug);
        if (empty($url)) {
            return response()->json(['html' => "<p class='text-danger'>Invalid page URL.</p>"]);
        }

        $urlLower = strtolower($url);
        $countInUrl = substr_count($urlLower, $keyword);

        try {
            $html = file_get_contents($url);
        } catch (\Exception $e) {
            return response()->json(['html' => "<p class='text-danger'>Unable to fetch content from the page.</p>"]);
        }

        $htmlData = $this->extractPrimaryKeywordData($html);

        $countTitle = $this->countExactWordMatches($htmlData['title'], $keyword);
        $countMetaTitle = $this->countExactWordMatches($htmlData['meta_title'], $keyword);
        $countDescription = $this->countExactWordMatches($htmlData['meta_description'], $keyword);
        $countContent = $this->countExactWordMatches($htmlData['content'], $keyword);
        $countH1 = $this->countExactWordMatches($htmlData['h1'], $keyword);
        $countH2 = $this->countExactWordMatches($htmlData['h2'], $keyword);
        $countH3 = $this->countExactWordMatches($htmlData['h3'], $keyword);
        $countH4 = $this->countExactWordMatches($htmlData['h4'], $keyword);
        $countH5 = $this->countExactWordMatches($htmlData['h5'], $keyword);
        $countH6 = $this->countExactWordMatches($htmlData['h6'], $keyword);

        $checks = [
            ['label' => 'Url', 'count' => $countInUrl],
            ['label' => 'Title', 'count' => $countTitle],
            ['label' => 'Meta Title', 'count' => $countMetaTitle],
            ['label' => 'Description', 'count' => $countDescription],
            ['label' => 'Content', 'count' => $countContent],
            ['label' => 'H1', 'count' => $countH1],
            ['label' => 'H2', 'count' => $countH2],
            ['label' => 'H3', 'count' => $countH3],
            ['label' => 'H4', 'count' => $countH4],
            ['label' => 'H5', 'count' => $countH5],
            ['label' => 'H6', 'count' => $countH6],
        ];

        $html = view('density-checker.primary_keyword_result', compact('keyword', 'checks'))->render();
//        dd($html);
        return response()->json(['html' => $html]);
    }

    private function countExactWordMatches(string $text, string $keyword): int
    {
        $phrase = preg_quote(trim($keyword), '/');

        // Match the exact phrase using Unicode-aware boundaries
        $pattern = '/(?<!\p{L})' . $phrase . '(?!\p{L})/iu';

        preg_match_all($pattern, $text, $matches);
        return count($matches[0]);
    }

    private function getDoc(string $html): \DOMDocument
    {
        $html = preg_replace('#<(script|style|noscript)[^>]*>.*?</\1>#is', '', $html);
        $html = preg_replace('/>(\s*)</', '> <', $html); // Normalize spacing

        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        return $doc;
    }

    private function extractPrimaryKeywordData(string $html): array
    {
        $doc = $this->getDoc($html);

        // Title
        $data['title'] = strtolower($doc->getElementsByTagName("title")->item(0)?->textContent ?? '');

        // Meta tags
        foreach ($doc->getElementsByTagName('meta') as $meta) {
            $name = strtolower($meta->getAttribute('name'));
            $property = strtolower($meta->getAttribute('property'));
            $content = strtolower($meta->getAttribute('content'));

            if ($name === 'description' || $property === 'og:description') {
                $data['meta_description'] = $content;
            }
            if ($name === 'title' || $property === 'og:title') {
                $data['meta_title'] = $content;
            }
        }

        foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $tag) {
            $text = '';
            foreach ($doc->getElementsByTagName($tag) as $node) {
                $text .= ' ' . $node->textContent;
            }
            $data[$tag] = strtolower($text);
        }

        // Body content
        $bodyText = $doc->getElementsByTagName("body")->item(0)?->textContent ?? '';
        $data['content'] = strtolower(trim(preg_replace('/\s+/', ' ', html_entity_decode($bodyText))));

        return $data;
    }



}
