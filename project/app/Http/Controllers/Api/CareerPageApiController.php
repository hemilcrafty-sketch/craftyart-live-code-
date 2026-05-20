<?php

namespace App\Http\Controllers\Api;

use App\Models\CareerPage;
use App\Models\JobOpening;
use App\Http\Controllers\Utils\ContentManager;
use Illuminate\Http\Request;

class CareerPageApiController extends ApiController
{
    private const SECTIONS = [
        'hero'           => 'hero',
        'tags'           => 'tags',
        'stats'          => 'stats',
        'our_culture'    => 'our_culture',
        'why_join'       => 'why_join',
        'hiring_process' => 'hiring_steps',
        'open_roles'     => 'open_roles',
        'hr_details'     => 'hr_details',
        'general_cta'    => 'general_cta',
    ];

    public function show(): array|string
    {
        try {
            $rows = CareerPage::where('is_active', true)->get()->keyBy('section_key');
            $data = [];

            foreach (self::SECTIONS as $jsonKey => $dbKey) {
                $row = $rows->get($dbKey);
                if (!$row) {
                    $data[$jsonKey] = null;
                    continue;
                }

                $val = is_array($row->value) ? $row->value : (json_decode($row->value ?? '', true) ?: []);

                switch ($dbKey) {
                    case 'hero':
                        $img = $val['image'] ?? null;
                        $res = [
                            'title'    => (string) ($val['title'] ?? $val['headline'] ?? ''),
                            'subtitle' => (string) ($val['subtitle'] ?? $val['subheadline'] ?? ''),
                            'image'    => (is_string($img) && $img !== '') ? $img : null,
                        ];
                        if ($res['image']) $res['image_url'] = ContentManager::getStorageLink($res['image']);
                        $data[$jsonKey] = $res;
                        break;

                    case 'tags':
                    case 'stats':
                        $items = [];
                        foreach ((array)($val['items'] ?? []) as $it) {
                            $item = ['label' => (string)($it['label'] ?? '')];
                            if ($dbKey === 'stats') {
                                $item['value'] = (string)($it['value'] ?? '');
                            }
                            $items[] = $item;
                        }
                        $data[$jsonKey] = ['items' => $items];
                        break;

                    case 'our_culture':
                        $highlights = [];
                        foreach ((array)($val['highlights'] ?? []) as $h) {
                            $icon = $h['icon'] ?? null;
                            $hr = [
                                'icon'        => (is_string($icon) && $icon !== '') ? $icon : null,
                                'title'       => (string)($h['title'] ?? ''),
                                'description' => (string)($h['description'] ?? ''),
                            ];
                            if ($hr['icon']) $hr['icon_url'] = ContentManager::getStorageLink($hr['icon']);
                            $highlights[] = $hr;
                        }
                        $m = $val['media'] ?? [];
                        $hashtags = [];
                        foreach ((array)($m['hashtags'] ?? []) as $t) if ($t = trim((string)$t)) $hashtags[] = $t;

                        $media = [
                            'type'     => in_array(strtolower($m['type'] ?? ''), ['video', 'image']) ? strtolower($m['type']) : 'image',
                            'file'     => null,
                            'poster'   => null,
                            'caption'  => (string)($m['caption'] ?? ''),
                            'hashtags' => $hashtags,
                        ];
                        if ($f = ($m['file'] ?? null)) {
                            $media['file'] = $f;
                            $media['file_url'] = ContentManager::getStorageLink($f);
                        }
                        if ($p = ($m['poster'] ?? null)) {
                            $media['poster'] = $p;
                            $media['poster_url'] = ContentManager::getStorageLink($p);
                        }

                        $data[$jsonKey] = [
                            'title'      => (string)($val['title'] ?? ''),
                            'intro'      => (string)($val['intro'] ?? ''),
                            'highlights' => $highlights,
                            'media'      => $media,
                        ];
                        break;

                    case 'why_join':
                        $items = [];
                        foreach ((array)($val['items'] ?? []) as $it) {
                            $icon = $it['icon'] ?? null;
                            $ir = [
                                'icon'        => (is_string($icon) && $icon !== '') ? $icon : null,
                                'title'       => (string)($it['title'] ?? ''),
                                'description' => (string)($it['description'] ?? $it['text'] ?? ''),
                            ];
                            if ($ir['icon']) $ir['icon_url'] = ContentManager::getStorageLink($ir['icon']);
                            $items[] = $ir;
                        }
                        $data[$jsonKey] = [
                            'title'    => (string)($val['title'] ?? ''),
                            'subtitle' => (string)($val['subtitle'] ?? ''),
                            'items'    => $items,
                        ];
                        break;

                    case 'hiring_steps':
                        $steps = []; $n = 1;
                        foreach ((array)($val['steps'] ?? $val['items'] ?? []) as $s) {
                            if (empty($s['title']) && empty($s['description'])) continue;
                            $steps[] = [
                                'step_number' => $n++,
                                'title'       => (string)($s['title'] ?? ''),
                                'description' => (string)($s['description'] ?? ''),
                            ];
                        }
                        $data[$jsonKey] = [
                            'title'       => (string)($val['title'] ?? ''),
                            'description' => (string)($val['description'] ?? ''),
                            'steps'       => $steps,
                        ];
                        break;

                    case 'open_roles':
                        $data[$jsonKey] = [
                            'title'    => (string)($val['title'] ?? ''),
                            'subtitle' => (string)($val['subtitle'] ?? ''),
                        ];
                        break;

                    case 'hr_details':
                        $data[$jsonKey] = [
                            'phone' => (string)($val['phone'] ?? ''),
                            'email' => (string)($val['email'] ?? ''),
                        ];
                        break;

                    case 'general_cta':
                        $data[$jsonKey] = [
                            'title'    => (string)($val['title'] ?? ''),
                            'subtitle' => (string)($val['subtitle'] ?? ''),
                        ];
                        break;
                }
            }

            $data['jobs'] = JobOpening::where('is_active', true)->orderBy('title')
                ->get(['id', 'title', 'slug', 'location', 'type', 'description', 'icon', 'experience'])
                ->map(fn($j) => [
                    'id'          => (int) $j->id,
                    'title'       => (string) $j->title,
                    'slug'        => (string) $j->slug,
                    'icon_url'    => ContentManager::getStorageLink($j->icon),
                    'location'    => (string) $j->location,
                    'type'        => (string) $j->type,
                    'description' => (string) $j->description,
                    'experience'  => (string) $j->experience,
                ])->all();

            return $this->successed(msg: 'Career page loaded', datas: ['data' => $data]);
        } catch (\Throwable $e) {
            report($e);
            return $this->failed(statusCode: 500, msg: 'Unable to load career page.');
        }
    }

}
