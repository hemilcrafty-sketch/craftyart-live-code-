<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ContentManager;
use App\Models\CareerPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Traits\HandlesMediaCleanup;

class CareerPageController extends AppBaseController
{
    use HandlesMediaCleanup;
    /** One DB row per key; matches career API section keys (plus tags/stats). @var list<string> */
    private const SECTION_KEYS = ['hero', 'stats', 'tags', 'our_culture', 'why_join', 'hiring_steps', 'open_roles', 'hr_details', 'general_cta'];

    public function edit(): View
    {
        $sectionsByKey = CareerPage::query()->get()->keyBy('section_key');
        return view('career_pages.edit', [
            'sectionKeys' => self::SECTION_KEYS,
            'sectionsByKey' => $sectionsByKey,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        try {
            $existing = CareerPage::query()->get()->keyBy('section_key');

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'sections' => 'present|array',
                'sections.hero.title' => 'required_if:sections.hero.is_active,1|nullable|string|max:255',
                'sections.hero.subtitle' => 'nullable|string|max:500',
                'sections.tags.items' => 'required_if:sections.tags.is_active,1|array|min:1',
                'sections.tags.items.*.label' => 'nullable|string|max:100',
                'sections.stats.items' => 'required_if:sections.stats.is_active,1|array|min:1',
                'sections.stats.items.*.label' => 'nullable|string|max:255',
                'sections.stats.items.*.value' => 'nullable|string|max:255',
                'sections.our_culture.title' => 'required_if:sections.our_culture.is_active,1|nullable|string|max:255',
                'sections.our_culture.intro' => 'nullable|string|max:10000',
                'sections.our_culture.highlights.*.title' => 'nullable|string|max:255',
                'sections.our_culture.media.type' => 'nullable|in:image,video',
                'sections.our_culture.media.file' => 'nullable|file|max:51200|mimes:jpeg,jpg,png,gif,webp,mp4,webm,svg',
                'sections.why_join.title' => 'required_if:sections.why_join.is_active,1|nullable|string|max:255',
                'sections.why_join.items' => 'required_if:sections.why_join.is_active,1|array|min:1',
                'sections.why_join.items.*.title' => 'nullable|string|max:255',
                'sections.hiring_steps.steps' => 'required_if:sections.hiring_steps.is_active,1|array|min:1',
                'sections.hiring_steps.steps.*.title' => 'nullable|string|max:255',
                'sections.hr_details.phone' => 'nullable|string|max:20',
                'sections.hr_details.email' => 'nullable|email|max:100',
            ], [
                'sections.hero.title.required_if' => 'Add a title when the hero section is enabled.',
                'sections.tags.items.required_if' => 'Add at least one tag if section is enabled.',
                'sections.stats.items.required_if' => 'Add at least one stat if section is enabled.',
                'sections.our_culture.title.required_if' => 'Add a title for the culture section.',
                'sections.why_join.items.required_if' => 'Add at least one reason if section is enabled.',
                'sections.hiring_steps.steps.required_if' => 'Add at least one hiring step if section is enabled.',
                'sections.our_culture.media.file.max' => 'Media file must be 50 MB or smaller.',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            foreach (self::SECTION_KEYS as $key) {
                $block = $request->input("sections.$key", []);
                $isActive = !empty($block['is_active']);
                $previous = ($existing->get($key) && is_array($existing->get($key)->value)) ? $existing->get($key)->value : [];

                if (!$isActive) {
                    $this->cleanupFiles($previous, []);
                    CareerPage::updateOrCreate(['section_key' => $key], ['value' => [], 'is_active' => false]);
                    continue;
                }

                $value = [];
                switch ($key) {
                    case 'hero':
                        $heroImg = ContentManager::saveImageToPath($block['image'] ?? null, 'uploadedFiles/career/' . bin2hex(random_bytes(16)) . '_' . time());
                        if (is_null($heroImg) && isset($previous['image'])) {
                            $heroImg = $previous['image'];
                        }

                        $value = [
                            'title' => trim($block['title'] ?? ''),
                            'subtitle' => trim($block['subtitle'] ?? ''),
                            'image' => $heroImg,
                        ];
                        break;

                    case 'stats':
                    case 'tags':
                        $items = [];
                        foreach (($block['items'] ?? []) as $row) {
                            $cleaned = array_map(fn($v) => trim((string)$v), $row);
                            if (array_filter($cleaned)) $items[] = $cleaned;
                        }
                        $value = ['items' => $items];
                        break;

                    case 'our_culture':
                        $highlights = [];
                        foreach (($block['highlights'] ?? []) as $i => $row) {
                            $icon = ContentManager::saveImageToPath($row['icon'] ?? null, 'uploadedFiles/career/' . bin2hex(random_bytes(16)) . '_' . time());
                            if (is_null($icon) && isset($previous['highlights'][$i]['icon'])) {
                                $icon = $previous['highlights'][$i]['icon'];
                            }

                            if (trim($row['title'] ?? '') || trim($row['description'] ?? '') || $icon) {
                                $highlights[] = ['icon' => $icon, 'title' => trim($row['title'] ?? ''), 'description' => trim($row['description'] ?? '')];
                            }
                        }
                        $mediaIn = $block['media'] ?? [];
                        $hashtags = array_filter(array_map('trim', (array)($mediaIn['hashtags'] ?? [])));

                        // Handle culture media file (Standard binary upload)
                        $cultureFile = $previous['media']['file'] ?? null;
                        if ($request->hasFile('sections.our_culture.media.file')) {
                            $file = $request->file('sections.our_culture.media.file');
                            $name = bin2hex(random_bytes(16)) . '_' . time() . '.' . $file->getClientOriginalExtension();
                            $file->storeAs('uploadedFiles/career', $name, 'public');
                            $cultureFile = 'uploadedFiles/career/' . $name;
                        }

                        $poster = ContentManager::saveImageToPath($mediaIn['poster'] ?? null, 'uploadedFiles/career/' . bin2hex(random_bytes(16)) . '_' . time());
                        if (is_null($poster) && isset($previous['media']['poster'])) {
                            $poster = $previous['media']['poster'];
                        }

                        $value = [
                            'title' => trim($block['title'] ?? ''),
                            'intro' => trim($block['intro'] ?? ''),
                            'highlights' => $highlights,
                            'media' => [
                                'type' => $mediaIn['type'] ?? ($previous['media']['type'] ?? 'image'),
                                'file' => $cultureFile,
                                'poster' => $poster,
                                'caption' => trim($mediaIn['caption'] ?? ($previous['media']['caption'] ?? '')),
                                'hashtags' => array_values($hashtags),
                            ],
                        ];
                        break;

                    case 'why_join':
                        $items = [];
                        foreach (($block['items'] ?? []) as $i => $row) {
                            $icon = ContentManager::saveImageToPath($row['icon'] ?? null, 'uploadedFiles/career/' . bin2hex(random_bytes(16)) . '_' . time());
                            if (is_null($icon) && isset($previous['items'][$i]['icon'])) {
                                $icon = $previous['items'][$i]['icon'];
                            }

                            if (trim($row['title'] ?? '') || trim($row['description'] ?? '') || $icon) {
                                $items[] = ['icon' => $icon, 'title' => trim($row['title'] ?? ''), 'description' => trim($row['description'] ?? '')];
                            }
                        }
                        $value = ['title' => trim($block['title'] ?? ''), 'subtitle' => trim($block['subtitle'] ?? ''), 'items' => $items];
                        break;

                    case 'hiring_steps':
                        $steps = [];
                        foreach (($block['steps'] ?? []) as $row) {
                            if (trim($row['title'] ?? '') || trim($row['description'] ?? '')) {
                                $steps[] = ['step_number' => count($steps) + 1, 'title' => trim($row['title'] ?? ''), 'description' => trim($row['description'] ?? '')];
                            }
                        }
                        $value = ['title' => trim($block['title'] ?? ''), 'description' => trim($block['description'] ?? ''), 'steps' => $steps];
                        break;

                    case 'open_roles':
                        $value = [
                            'title' => trim($block['title'] ?? ''),
                            'subtitle' => trim($block['subtitle'] ?? ''),
                        ];
                        break;

                    case 'hr_details':
                        $value = [
                            'phone' => trim($block['phone'] ?? ''),
                            'email' => trim($block['email'] ?? ''),
                        ];
                        break;

                    case 'general_cta':
                        $value = [
                            'title' => trim($block['title'] ?? ''),
                            'subtitle' => trim($block['subtitle'] ?? ''),
                        ];
                        break;
                }

                $this->cleanupFiles($previous, $value);
                CareerPage::updateOrCreate(['section_key' => $key], ['value' => $value, 'is_active' => true]);
            }

            return redirect()->route('career_page.edit')->with('success', 'Career page sections saved.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage())->withInput();
        }
    }
}