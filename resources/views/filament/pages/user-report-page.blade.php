@php
    $metrics = [
        [
            'label' => 'Members',
            'value' => number_format($totalMembers),
            'note' => $activeMembers30Days.' active in 30 days',
        ],
        [
            'label' => 'Workouts',
            'value' => number_format($totalWorkoutsLogged),
            'note' => 'Sessions logged',
        ],
        [
            'label' => 'Volume',
            'value' => number_format($totalVolumeLiftedKg),
            'suffix' => 'kg',
            'note' => 'Cumulative load',
        ],
        [
            'label' => 'Meals',
            'value' => number_format($totalMealsLogged),
            'note' => 'Nutrition logs',
        ],
        [
            'label' => 'Avg form',
            'value' => $averageFormScore === null ? '—' : round($averageFormScore).'%',
            'note' => $scoredSetCount.' camera-scored sets',
        ],
    ];

    $goalTotal = max(1, array_sum($goalDistribution));
@endphp

<x-filament-panels::page>
    <div class="space-y-4">

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
            @foreach ($metrics as $metric)
                <div class="rounded-md border border-gray-200 p-4 dark:border-white/10">
                    <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        {{ $metric['label'] }}
                    </p>
                    <p class="mt-1 text-2xl font-semibold tabular-nums text-gray-950 dark:text-white">
                        {{ $metric['value'] }}
                        @isset($metric['suffix'])
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">{{ $metric['suffix'] }}</span>
                        @endisset
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $metric['note'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">

            <div class="rounded-md border border-gray-200 p-4 dark:border-white/10">
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Fitness goals</h3>

                <div class="mt-3 space-y-3">
                    @forelse ($goalDistribution as $goal => $count)
                        <div>
                            <div class="flex justify-between text-xs text-gray-600 dark:text-gray-300">
                                <span>{{ ucfirst(str_replace('_', ' ', $goal)) }}</span>
                                <span class="tabular-nums">{{ $count }}</span>
                            </div>
                            <div class="mt-1 h-1.5 w-full rounded-full bg-gray-100 dark:bg-white/10">
                                <div
                                    class="h-1.5 rounded-full bg-primary-400"
                                    style="width: {{ min(100, $count / $goalTotal * 100) }}%"
                                ></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500 dark:text-gray-400">No goals recorded yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-md border border-gray-200 p-4 lg:col-span-2 dark:border-white/10">
                <div class="flex items-baseline justify-between">
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Most active members</h3>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400">By workouts</span>
                </div>

                <div class="mt-2 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-[11px] uppercase tracking-wide text-gray-500 dark:border-white/10 dark:text-gray-400">
                                <th class="py-2 pr-2 font-medium">#</th>
                                <th class="py-2 pr-2 font-medium">Member</th>
                                <th class="py-2 pr-2 text-right font-medium">Workouts</th>
                                <th class="py-2 pr-2 text-right font-medium">Meals</th>
                                <th class="py-2 text-right font-medium"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topActiveUsers as $index => $user)
                                <tr class="border-b border-gray-100 last:border-0 dark:border-white/5">
                                    <td class="py-2 pr-2 tabular-nums text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                                    <td class="py-2 pr-2">
                                        <div class="font-medium text-gray-950 dark:text-white">{{ $user['name'] }}</div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400">{{ $user['email'] }}</div>
                                    </td>
                                    <td class="py-2 pr-2 text-right tabular-nums text-gray-950 dark:text-white">{{ $user['workouts_count'] }}</td>
                                    <td class="py-2 pr-2 text-right tabular-nums text-gray-600 dark:text-gray-300">{{ $user['meals_count'] }}</td>
                                    <td class="py-2 text-right">
                                        <a
                                            href="/admin/users/{{ $user['id'] }}/edit"
                                            class="text-xs font-medium text-primary-600 hover:underline dark:text-primary-400"
                                        >
                                            Open
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-xs text-gray-500 dark:text-gray-400">
                                        No member data yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
