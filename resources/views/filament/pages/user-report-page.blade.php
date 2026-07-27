<x-filament-panels::page>
    <div class="space-y-6">

        <!-- Top Metrics Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Card 1 -->
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Members</p>
                        <p class="mt-2 text-3xl font-extrabold text-gray-900 dark:text-white">{{ number_format($totalMembers) }}</p>
                    </div>
                    <div class="p-3 bg-emerald-100 dark:bg-emerald-900/40 rounded-lg text-emerald-600 dark:text-emerald-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-xs text-emerald-600 dark:text-emerald-400 font-semibold">
                    ⚡ {{ $activeMembers30Days }} active in past 30 days
                </p>
            </div>

            <!-- Card 2 -->
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Workouts Completed</p>
                        <p class="mt-2 text-3xl font-extrabold text-gray-900 dark:text-white">{{ number_format($totalWorkoutsLogged) }}</p>
                    </div>
                    <div class="p-3 bg-amber-100 dark:bg-amber-900/40 rounded-lg text-amber-600 dark:text-amber-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Total workout sessions logged
                </p>
            </div>

            <!-- Card 3 -->
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Volume Lifted</p>
                        <p class="mt-2 text-3xl font-extrabold text-gray-900 dark:text-white">{{ number_format($totalVolumeLiftedKg) }} <span class="text-base font-medium">kg</span></p>
                    </div>
                    <div class="p-3 bg-purple-100 dark:bg-purple-900/40 rounded-lg text-purple-600 dark:text-purple-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Cumulative member load
                </p>
            </div>

            <!-- Card 4 -->
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Meals Tracked</p>
                        <p class="mt-2 text-3xl font-extrabold text-gray-900 dark:text-white">{{ number_format($totalMealsLogged) }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/40 rounded-lg text-blue-600 dark:text-blue-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Nutrition & macro logs
                </p>
            </div>
        </div>

        <!-- Goals Breakdown & Quick Stats -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-1 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    🎯 Member Fitness Goals
                </h3>
                <div class="space-y-4">
                    @forelse($goalDistribution as $goal => $count)
                        <div>
                            <div class="flex justify-between text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <span>{{ ucfirst(str_replace('_', ' ', $goal)) }}</span>
                                <span class="font-bold">{{ $count }} members</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-emerald-500 h-2.5 rounded-full" style="width: {{ min(100, ($count / max(1, array_sum($goalDistribution))) * 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">No fitness goals recorded yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Top Active Member Leaderboard -->
            <div class="lg:col-span-2 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        🏆 Top Active Members Leaderboard
                    </h3>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Ranked by Workouts</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-3">Rank</th>
                                <th class="px-4 py-3">Member</th>
                                <th class="px-4 py-3 text-center">Workouts</th>
                                <th class="px-4 py-3 text-center">Meals</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topActiveUsers as $index => $user)
                                <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750">
                                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">
                                        @if($index === 0) 🥇 @elseif($index === 1) 🥈 @elseif($index === 2) 🥉 @else #{{ $index + 1 }} @endif
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs">
                                            {{ strtoupper(substr($user['name'], 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold">{{ $user['name'] }}</div>
                                            <div class="text-xs text-gray-400">{{ $user['email'] }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300">
                                            {{ $user['workouts_count'] }} sessions
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-semibold">
                                        {{ $user['meals_count'] }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="/admin/users/{{ $user['id'] }}/edit" class="text-xs font-bold text-emerald-600 hover:underline">
                                            View Member →
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-400">No member data recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
