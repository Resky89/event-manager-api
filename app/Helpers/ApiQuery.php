<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * ApiQuery: Utility untuk menerapkan search, sort, dan filter berbasis query string
 *
 * Konvensi parameter:
 * - search=kata kunci (LIKE pada field searchable)
 * - sort=field1,-field2 (daftar field sortable; awali '-' untuk desc)
 * - Filter exact: field=value
 * - Filter IN: field_in=a,b,c
 * - Filter NOT EQUAL: field_not=value
 * - Range numeric/datetime/date: field_min=..., field_max=...
 * - String contains: definisikan tipe 'string_like' pada filterable, lalu kirim field=nilai
 */
class ApiQuery
{
    protected Builder $query;
    protected array $searchable = [];
    protected array $sortable = [];
    /**
     * @var array<string,string> field => type (string|string_like|bool|int|numeric|date|datetime)
     */
    protected array $filterable = [];

    private function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public static function for(Builder $query): self
    {
        return new self($query);
    }

    public function searchable(array $fields): self
    {
        $this->searchable = array_values($fields);
        return $this;
    }

    public function sortable(array $fields): self
    {
        $this->sortable = array_values($fields);
        return $this;
    }

    /**
     * @param array<string,string> $definitions field => type
     */
    public function filterable(array $definitions): self
    {
        $this->filterable = $definitions;
        return $this;
    }

    /**
     * Terapkan semua aturan ke query.
     * @param array<string,mixed> $params biasanya $request->query()
     */
    public function apply(array $params): Builder
    {
        $this->applySearch($params);
        $this->applyFilters($params);
        $this->applySort($params);
        return $this->query;
    }

    /**
     * @param array<string,mixed> $params
     */
    protected function applySearch(array $params): void
    {
        $term = isset($params['search']) ? trim((string)$params['search']) : '';
        if ($term === '' || empty($this->searchable)) {
            return;
        }

        $this->query->where(function (Builder $q) use ($term) {
            foreach ($this->searchable as $field) {
                $q->orWhere($field, 'LIKE', '%' . str_replace(['%', '_'], ['\\%', '\\_'], $term) . '%');
            }
        });
    }

    /**
     * @param array<string,mixed> $params
     */
    protected function applySort(array $params): void
    {
        $sort = isset($params['sort']) ? (string)$params['sort'] : '';
        if ($sort === '') {
            return;
        }

        $tokens = array_filter(array_map('trim', explode(',', $sort)));
        foreach ($tokens as $token) {
            $direction = 'asc';
            if (Str::startsWith($token, '-')) {
                $direction = 'desc';
                $token = ltrim($token, '-');
            }
            if (in_array($token, $this->sortable, true)) {
                $this->query->orderBy($token, $direction);
            }
        }
    }

    /**
     * @param array<string,mixed> $params
     */
    protected function applyFilters(array $params): void
    {
        foreach ($this->filterable as $field => $type) {
            // IN list
            $inKey = $field . '_in';
            if (isset($params[$inKey])) {
                $values = array_values(array_filter(array_map('trim', explode(',', (string)$params[$inKey]))));
                if (!empty($values)) {
                    $this->query->whereIn($field, array_map(fn ($v) => $this->cast($v, $type), $values));
                }
            }

            // NOT equal
            $neqKey = $field . '_not';
            if (array_key_exists($neqKey, $params) && $params[$neqKey] !== '') {
                $this->query->where($field, '!=', $this->cast($params[$neqKey], $type));
            }

            // Range
            $minKey = $field . '_min';
            if (array_key_exists($minKey, $params) && $params[$minKey] !== '') {
                $value = $this->cast($params[$minKey], $type);
                $this->query->where($field, '>=', $value);
            }
            $maxKey = $field . '_max';
            if (array_key_exists($maxKey, $params) && $params[$maxKey] !== '') {
                $value = $this->cast($params[$maxKey], $type);
                $this->query->where($field, '<=', $value);
            }

            // Exact or contains
            if (array_key_exists($field, $params) && $params[$field] !== '') {
                $value = $params[$field];
                if ($type === 'string_like') {
                    $this->query->where($field, 'LIKE', '%' . str_replace(['%', '_'], ['\\%', '\\_'], (string)$value) . '%');
                } elseif ($type === 'date') {
                    // exact date
                    $this->query->whereDate($field, '=', (string)$value);
                } else {
                    $this->query->where($field, '=', $this->cast($value, $type));
                }
            }
        }
    }

    /**
     * @param mixed $value
     */
    protected function cast($value, string $type)
    {
        switch ($type) {
            case 'bool':
                if (is_bool($value)) return $value;
                $v = strtolower((string)$value);
                return in_array($v, ['1', 'true', 'yes', 'on'], true);
            case 'int':
                return (int)$value;
            case 'numeric':
                return is_numeric($value) ? 0 + $value : 0;
            case 'date':
            case 'datetime':
            case 'string':
            case 'string_like':
            default:
                return $value;
        }
    }
}
