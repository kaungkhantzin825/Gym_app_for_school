<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\MealChartWidget;
use App\Filament\Widgets\RecentMembersWidget;
use App\Filament\Widgets\RecentWorkoutsWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\TopExercisesWidget;
use App\Filament\Widgets\TrainingVolumeWidget;
use App\Filament\Widgets\WorkoutChartWidget;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('INTI Gym')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->font('Inter')
            ->defaultThemeMode(ThemeMode::Dark)
            ->maxContentWidth(Width::Full)
            ->sidebarCollapsibleOnDesktop()
            ->spa()
            ->colors([
                // The mobile app's lime, #C6F24E, is the only accent here too.
                'primary' => [
                    50 => '#f7ffe0',
                    100 => '#ecffb8',
                    200 => '#dcff85',
                    300 => '#c6f24e',
                    400 => '#b0e02f',
                    500 => '#93c21c',
                    600 => '#749b12',
                    700 => '#57750d',
                    800 => '#3d5209',
                    900 => '#253305',
                    950 => '#131a02',
                ],
                'gray' => Color::Zinc,
            ])
            ->navigationGroups([
                NavigationGroup::make('Training')
                    ->collapsible(false),
                NavigationGroup::make('Nutrition')
                    ->collapsible(false),
                NavigationGroup::make('Content')
                    ->collapsible(),
                NavigationGroup::make('Members')
                    ->collapsible(),
                NavigationGroup::make('Support')
                    ->collapsible(),
                NavigationGroup::make('System')
                    ->collapsible(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                StatsOverviewWidget::class,
                WorkoutChartWidget::class,
                TrainingVolumeWidget::class,
                MealChartWidget::class,
                TopExercisesWidget::class,
                RecentWorkoutsWidget::class,
                RecentMembersWidget::class,
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
