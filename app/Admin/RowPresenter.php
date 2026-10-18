<?php

namespace App\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Turns a model into the flat row the Vue table renders.
 *
 * Doing the formatting server-side keeps the front end free of date libraries
 * and per-column conditionals: the browser receives strings plus a type.
 */
class RowPresenter
{
    /**
     * @param  array<string, mixed>  $resource
     * @return array<int, array<string, mixed>>
     */
    public static function columns(array $resource): array
    {
        return array_map(fn (array $column): array => [
            'key' => $column['key'],
            'label' => $column['label'],
            'type' => $column['type'],
            'align' => in_array($column['type'], ['number'], true) ? 'right' : 'left',
        ], $resource['columns']);
    }

    /**
     * @param  array<string, mixed>  $resource
     * @return array<string, mixed>
     */
    public static function row(array $resource, Model $model): array
    {
        $cells = [];

        foreach ($resource['columns'] as $column) {
            $raw = data_get($model, $column['key']);

            $cells[$column['key']] = [
                'display' => static::display($raw, $column),
                'tone' => $column['type'] === 'badge'
                    ? ($column['tones'][(string) $raw] ?? 'slate')
                    : null,
            ];
        }

        return [
            'id' => $model->getKey(),
            'cells' => $cells,
        ];
    }

    /**
     * @param  array<string, mixed>  $column
     */
    private static function display(mixed $raw, array $column): string
    {
        if ($raw === null || $raw === '') {
            return '—';
        }

        return match ($column['type']) {
            'number' => static::number($raw).($column['suffix'] ?? ''),
            'date' => $raw instanceof \DateTimeInterface
                ? $raw->format('d M Y')
                : (string) $raw,
            'datetime' => $raw instanceof \DateTimeInterface
                ? $raw->format('d M Y, H:i')
                : (string) $raw,
            'long' => Str::limit((string) $raw, 60),
            default => (string) $raw,
        };
    }

    private static function number(mixed $raw): string
    {
        $value = (float) $raw;

        return floor($value) === $value
            ? number_format($value)
            : number_format($value, 2);
    }
}
