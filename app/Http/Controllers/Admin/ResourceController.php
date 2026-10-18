<?php

namespace App\Http\Controllers\Admin;

use App\Admin\AdminResources;
use App\Admin\RowPresenter;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * One controller for every admin screen.
 *
 * The resource key comes from the route, so /admin/users and /admin/meals run
 * the same list/create/edit/update/destroy code against different definitions
 * in AdminResources.
 */
class ResourceController extends Controller
{
    public function index(Request $request, string $resource): Response
    {
        $definition = AdminResources::get($resource);
        $model = new $definition['model'];

        $query = $model->newQuery()->with($definition['with'] ?? []);

        $search = trim((string) $request->query('search', ''));

        if ($search !== '' && ! empty($definition['search'])) {
            $query->where(function ($query) use ($definition, $search): void {
                foreach ($definition['search'] as $column) {
                    $query->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        [$sortColumn, $sortDirection] = $definition['sort'] ?? ['id', 'desc'];

        $sortable = array_merge(array_column($definition['columns'], 'key'), [$model->getKeyName()]);

        if (in_array($sortColumn, $sortable, true)) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $paginator = $query->paginate(15)->withQueryString();

        return Inertia::render('Resource/Index', [
            'resource' => $this->describe($resource, $definition),
            'columns' => RowPresenter::columns($definition),
            'rows' => collect($paginator->items())
                ->map(fn (Model $row): array => RowPresenter::row($definition, $row))
                ->all(),
            'pagination' => [
                'page' => $paginator->currentPage(),
                'pages' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'filters' => ['search' => $search],
        ]);
    }

    public function create(Request $request, string $resource): Response
    {
        $definition = AdminResources::get($resource);

        return Inertia::render('Resource/Form', [
            'resource' => $this->describe($resource, $definition),
            'fields' => $this->fields($definition, null, $this->defaults($request, $definition)),
            'record' => null,
        ]);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $definition = AdminResources::get($resource);

        $validated = $request->validate($this->rules($definition, null));

        $model = new $definition['model'];

        if (! empty($definition['owner'])) {
            $validated[$definition['owner']] = $request->user()->getKey();
        }

        $model->newQuery()->create($validated);

        return redirect()
            ->route('admin.resource.index', $resource)
            ->with('success', $definition['singular'].' created.');
    }

    public function edit(string $resource, int $id): Response
    {
        $definition = AdminResources::get($resource);

        $record = $this->find($definition, $id);

        return Inertia::render('Resource/Form', [
            'resource' => $this->describe($resource, $definition),
            'fields' => $this->fields($definition, $record),
            'record' => ['id' => $record->getKey()],
        ]);
    }

    public function update(Request $request, string $resource, int $id): RedirectResponse
    {
        $definition = AdminResources::get($resource);

        $record = $this->find($definition, $id);

        $validated = $request->validate($this->rules($definition, $record));

        // A blank password field on the edit form means "leave it alone".
        if (array_key_exists('password', $validated) && blank($validated['password'])) {
            unset($validated['password']);
        }

        $record->update($validated);

        return redirect()
            ->route('admin.resource.index', $resource)
            ->with('success', $definition['singular'].' updated.');
    }

    public function destroy(string $resource, int $id): RedirectResponse
    {
        $definition = AdminResources::get($resource);

        $this->find($definition, $id)->delete();

        return redirect()
            ->route('admin.resource.index', $resource)
            ->with('success', $definition['singular'].' deleted.');
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function find(array $definition, int $id): Model
    {
        $model = new $definition['model'];

        return $model->newQuery()->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    private function describe(string $resource, array $definition): array
    {
        return [
            'key' => $resource,
            'label' => $definition['label'],
            'singular' => $definition['singular'],
            'group' => $definition['group'],
        ];
    }

    /**
     * Field metadata for the form, with the current values filled in.
     *
     * @param  array<string, mixed>  $definition
     * @param  array<string, string>  $defaults
     * @return array<int, array<string, mixed>>
     */
    private function fields(array $definition, ?Model $record, array $defaults = []): array
    {
        return array_map(function (array $field) use ($record, $defaults): array {
            if ($record !== null && ! empty($field['createOnly'])) {
                return [...$field, 'hidden' => true];
            }

            $value = $record === null
                ? ($defaults[$field['key']] ?? null)
                : data_get($record, $field['key']);

            if ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d\TH:i');
            }

            return [
                ...$field,
                'value' => $value === null ? '' : (string) $value,
                'hidden' => false,
            ];
        }, $definition['fields']);
    }

    /**
     * Pre-fill the timestamp when the form is opened from a calendar day.
     *
     * @param  array<string, mixed>  $definition
     * @return array<string, string>
     */
    private function defaults(Request $request, array $definition): array
    {
        $field = $definition['timeField'] ?? null;
        $date = (string) $request->query('date', '');

        if ($field === null || preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1) {
            return [];
        }

        $time = $date === now()->toDateString() ? now()->format('H:i') : '09:00';

        return [$field => "{$date}T{$time}"];
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, array<int, mixed>>
     */
    private function rules(array $definition, ?Model $record): array
    {
        $rules = [];

        foreach ($definition['fields'] as $field) {
            if ($record !== null && ! empty($field['createOnly'])) {
                continue;
            }

            $rules[$field['key']] = array_map(
                fn (string $rule): mixed => $this->rule($rule, $record),
                $field['rules'] ?? [],
            );
        }

        return $rules;
    }

    private function rule(string $rule, ?Model $record): mixed
    {
        // `unique:users,email,{id}` — the placeholder means "ignore this row".
        if (preg_match('/^unique:([^,]+),([^,]+)/', $rule, $matches) === 1) {
            return Rule::unique($matches[1], $matches[2])->ignore($record?->getKey());
        }

        return $rule;
    }
}
