<?php

namespace App\Http\Middleware;

use App\Admin\AdminResources;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root Blade template.
     */
    protected $rootView = 'app';

    /**
     * Data shared with every page component.
     *
     * The sidebar tree lives here rather than in each controller so adding a
     * resource to AdminResources shows up on every screen at once.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user === null ? null : [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ],
            'navigation' => fn (): array => AdminResources::navigation(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}
