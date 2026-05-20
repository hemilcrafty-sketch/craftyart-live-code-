<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Utils\RoleManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Highlight\Highlighter; // Only if you’re using a library
use SebastianBergmann\Diff\Differ;

class AppBaseController extends Controller
{

    public $filters = [];

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function sendSuccessResponse($message, $data = null, $code = Response::HTTP_OK)
    {
        return response()->json([
            'status' => true,
            'success' => $message,
            'data' => $data,
        ], $code);
    }

    protected function sendErrorResponse($message)
    {
        return response()->json([
            'status' => false,
            'error' => $message,
        ]);
    }


    protected function applyFilters($query, $filters, $values)
    {
        if ($values !== '') {
            foreach ($filters as $column) {
                if (str_contains($column, '.')) {
                    // Split the column to get the relationship and the column name
                    [$relation, $relationColumn] = explode('.', $column);

                    // Apply the filter to the relation
                    $query->orWhereHas($relation, function ($subQuery) use ($relationColumn, $values) {
                        $subQuery->where($relationColumn, 'like', "%$values%");
                    });
                } else {
                    // Apply the filter to the main query
                    $query->orWhere($column, 'like', "%$values%");
                }
            }
        }
        return $query;
    }

    protected function applyNoIndexFilter(Request $request, Builder $query, string $column = 'no_index'): void
    {
        if (!$request->filled('no_index_filter')) {
            return;
        }
        $v = $request->input('no_index_filter');
        if ($v === '1' || $v === '0') {
            $query->where($column, (int) $v);
        }
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     */
    protected function noIndexListingStats(string $modelClass, ?callable $baseConstraint = null): array
    {
        $base = function () use ($modelClass, $baseConstraint) {
            $q = $modelClass::query();
            if ($baseConstraint !== null) {
                $baseConstraint($q);
            }
            return $q;
        };

        return [
            'enabled' => $base()->where('no_index', 1)->count(),
            'disabled' => $base()->where('no_index', 0)->count(),
        ];
    }

    public function applyFiltersAndPagination(
        Request $request,
        Builder $query,
        array $searchableFields = ['id'],
        array $relationSearchConfig = [],
        $default = "desc"
    ): LengthAwarePaginator {
        if ($request->filled('query')) {
            $searchTerm = $request->input('query');

            // 🔹 Search in main table
            $query->where(function ($q) use ($searchableFields, $searchTerm) {
                foreach ($searchableFields as $field) {
                    $q->orWhere($field['id'], 'like', '%' . $searchTerm . '%');
                }
            });

            // 🔹 Support old style relation search (single relation)
            if (isset($relationSearchConfig['parent_query'])) {
                $parentQuery = $relationSearchConfig['parent_query'];
                $relatedColumn = $relationSearchConfig['related_column'];
                $columnValue = $relationSearchConfig['column_value'];
                $returnField = $relationSearchConfig['return_field'] ?? 'id';

                $data = $parentQuery->where($relatedColumn, 'like', '%' . $searchTerm . '%')->get();

                if ($data->isNotEmpty()) {
                    $ids = $data->pluck($returnField)->toArray();

                    $query->orWhere(function ($subQuery) use ($columnValue, $ids) {
                        foreach ($ids as $id) {
                            $subQuery->orWhere(function ($inner) use ($columnValue, $id) {
                                $inner->orWhere($columnValue, $id);
                                $inner->orWhereRaw("JSON_VALID($columnValue) AND JSON_CONTAINS($columnValue, '\"$id\"')");
                            });
                        }
                    });
                }
            }

            // 🔹 Support new style relation search (multiple models, possibly different DBs)
            elseif (!empty($relationSearchConfig) && isset($relationSearchConfig[0]['model'])) {
                foreach ($relationSearchConfig as $config) {
                    if (!isset($config['model'], $config['match_column'], $config['foreign_key'], $config['fields'])) {
                        continue;
                    }

                    $relatedModel = new $config['model'];
                    $relatedQuery = $relatedModel->newQuery();

                    $relatedQuery->where(function ($relQ) use ($config, $searchTerm) {
                        foreach ($config['fields'] as $field) {
                            $relQ->orWhere($field, 'like', '%' . $searchTerm . '%');
                        }
                    });

                    $ids = $relatedQuery->pluck($config['match_column'])->toArray();

                    if (!empty($ids)) {
                        $query->orWhereIn($config['foreign_key'], $ids);
                    }
                }
            }
        }

        // 🔹 Sorting
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = in_array($request->input('sort_order'), ['asc', 'desc']) ? $request->input('sort_order') : $default;
        $query->orderBy($sortBy, $sortOrder);

        // 🔹 Pagination
        $perPage = $request->input('per_page', 15);
        if ($perPage === 'all') {
            $results = $query->get();
            return new LengthAwarePaginator(
                $results,
                $results->count(),
                $results->count(),
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }

        return $query->paginate((int) $perPage)->appends($request->all());
    }

    public function applyFiltersAndPagination2222(
        Request $request,
        Builder $query,
        array $searchableFields = ['id'],
        array $relationSearchConfig = [],
        $default = "desc"
    ): LengthAwarePaginator {
        if ($request->filled('query')) {
            $searchTerm = $request->input('query');

            // Normal searchable fields
            $query->where(function ($q) use ($searchableFields, $searchTerm) {
                foreach ($searchableFields as $field) {
                    $q->orWhere($field['id'], 'like', '%' . $searchTerm . '%');
                }
            });

            // Relation-based filtering
            if (!empty($relationSearchConfig)) {
                $parentQuery = $relationSearchConfig['parent_query'] ?? null;
                $relatedColumn = $relationSearchConfig['related_column'] ?? null;
                $columnValue = $relationSearchConfig['column_value'] ?? null;
                $returnField = $relationSearchConfig['return_field'] ?? 'id';

                if (isset($parentQuery, $relatedColumn, $columnValue)) {
                    $data = $parentQuery
                        ->where($relatedColumn, 'like', '%' . $searchTerm . '%')
                        ->get();

                    if ($data->isNotEmpty()) {
                        $ids = $data->pluck($returnField)->toArray();

                        $query->orWhere(function ($subQuery) use ($columnValue, $ids) {
                            foreach ($ids as $id) {
                                $subQuery->orWhere(function ($inner) use ($columnValue, $id) {
                                    $inner->orWhere($columnValue, $id);
                                    $inner->orWhereRaw("JSON_VALID($columnValue) AND JSON_CONTAINS($columnValue, '\"$id\"')");
                                });
                            }
                        });
                    }
                }
            }
        }

        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = in_array($request->input('sort_order'), ['asc', 'desc']) ? $request->input('sort_order') : $default;
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 15);
        if ($perPage === 'all') {
            $results = $query->get();
            return new LengthAwarePaginator(
                $results,
                $results->count(),
                $results->count(),
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }

        return $query->paginate((int) $perPage)->appends($request->all());
    }

    protected function isAccessByRole($role, $id = null, $empId = null, $seoEmpIds = null): ?string
    {
        $user = auth()->user();
        $userType = $user->user_type;
        if (str_starts_with($role, "seo")) {
            if (
                !RoleManager::onlySeoAccess($userType)
            ) {
                return 'You have no permission.';
            } else if ($id && $empId && $empId != $user->id && !RoleManager::isAdmin($userType)) {
                $sharedSeoCatalog = ($role === 'seo_all' || ($role === 'seo' && !$seoEmpIds))
                    && (RoleManager::isSeoManager($userType) || RoleManager::isSeoExecutive($userType));
                if ($sharedSeoCatalog) {
                    // Shared SEO catalog (themes, styles, interests, etc.): managers & executives may edit any row
                } elseif ($seoEmpIds) {
                    if (in_array($user->id, $seoEmpIds, false)) {
                        return null;
                    } else {
                        return 'Access denied. You have no assign this page';
                    }
                } else {
                    return 'Access denied. You are not allowed to edit others\' data.';
                }
            } else if (!$id) {
                if (RoleManager::isSeoManager($userType) && $role !== "seo_all") {
                    return 'Manager cannot add page';
                }
            }
        } else {
            if (!RoleManager::onlyDesignerAccess($userType)) {
                return 'You have no permission';
            } else if ($id && $empId && $empId != $user->id && !RoleManager::isAdminOrDesignerManager($userType)) {
                return 'Access denied. You are not allowed to edit others\' data.';
            }
        }
        return null;
    }

    public static function isJson($string)
    {
        if (!is_string($string))
            return false;
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public static function getJsonContent($input)
    {
        if (empty($input))
            return [];

        if (is_string($input) && Storage::exists($input)) {
            $json = Storage::get($input);
        } elseif (is_string($input) && self::isJson($input)) {
            $json = $input;
        } else {
            return [];
        }

        $data = json_decode($json, associative: true);
        return json_last_error() === JSON_ERROR_NONE ? $data : [];
    }

    public static function renderChangeValue($key, $value, $otherValue = null)
    {
        if (empty($value)) {
            return '<span class="text-muted">—</span>';
        }

        $isFaq = str_contains($key, 'faqs');
        $isContent = str_contains($key, 'contents');

        if (($isContent || $isFaq) && is_string($value) && Storage::exists($value)) {
            $json = Storage::get($value);
            $data = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return '<span class="text-danger">Invalid JSON</span>';
            }

            $output = '';

            if ($isFaq) {
                $output .= "<strong>Title : </strong> " . e($data['title'] ?? '') . "<br>";
                foreach ($data['faqs'] ?? [] as $faq) {
                    $output .= "<div><strong>Q : </strong> " . e($faq['question'] ?? '') . "<br>";
                    $output .= "<strong>A : </strong> " . e($faq['answer'] ?? '') . "</div><hr>";
                }
                return $output;
            }

            if ($isContent) {
                foreach ($data as $block) {
                    if (isset($block['value']['content']) && is_array($block['value']['content'])) {
                        foreach ($block['value']['content'] as $inner) {
                            $keyLabel = e($inner['key'] ?? '');
                            $val = $inner['value'] ?? '';
                            $plainValue = strip_tags($val);
                            $plainOld = $otherValue ? strip_tags(self::extractOldContent($key, $otherValue, $inner['key'] ?? '')) : '';
                            $highlighted = $otherValue ? self::diffHighlight($plainOld, $plainValue) : e($plainValue);
                            $output .= "<div><strong>{$keyLabel}:</strong><br>{$highlighted}</div><hr>";
                        }
                    }
                }
                return $output ?: '<span class="text-muted">—</span>';
            }
        }

        if (!is_array($value) && !is_object($value)) {
            $plainValue = strip_tags($value);
            $plainOld = $otherValue ? strip_tags($otherValue) : '';
            $highlighted = $otherValue ? self::diffHighlight($plainOld, $plainValue) : e($plainValue);

            return $highlighted;
        }

        if (is_array($value)) {
            return nl2br(e(json_encode($value, JSON_PRETTY_PRINT)));
        }
        return e($value);
    }

    private static function extractOldContent($key, $oldPath, $innerKey)
    {
        if (!Storage::exists($oldPath))
            return '';

        $json = Storage::get($oldPath);
        $data = json_decode($json, true);

        if (!is_array($data))
            return '';

        foreach ($data as $block) {
            if (isset($block['value']['content'])) {
                foreach ($block['value']['content'] as $inner) {
                    if (($inner['key'] ?? '') === $innerKey) {
                        return $inner['value'] ?? '';
                    }
                }
            }
        }
        return '';
    }

    private static function diffHighlight($old, $new)
    {
        $oldWords = preg_split('/\s+/', trim($old));
        $newWords = preg_split('/\s+/', trim($new));

        $highlighted = '';
        foreach ($newWords as $word) {
            if (!in_array($word, $oldWords)) {
                $highlighted .= "<span style='background-color: #d4edda; padding: 2px 4px; border-radius: 4px;'>" . e($word) . "</span> ";
            } else {
                $highlighted .= e($word) . '';
            }
        }

        return trim($highlighted);
    }

    protected function applyVideoSitemapFieldsFromRequest(Request $request, Model $model, bool $isCreate): void
    {
        if (!RoleManager::isAdminOrSeoManager((int) auth()->user()->user_type)) {
            return;
        }

        $allowedFreq = ['daily', 'weekly', 'monthly'];

        if ($request->has('priority')) {
            $p = $request->input('priority');
            $model->priority = ($p === null || $p === '')
                ? 0.90
                : round(min(1.0, max(0.0, (float) $p)), 2);
        } elseif ($isCreate) {
            $model->priority = 0.90;
        }

        if ($request->has('frequency')) {
            $f = (string) $request->input('frequency');
            $model->frequency = in_array($f, $allowedFreq, true) ? $f : 'daily';
        } elseif ($isCreate) {
            $model->frequency = 'daily';
        }
    }


}