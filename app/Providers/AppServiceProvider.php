<?php

namespace App\Providers;

use App\Models\User;
use App\Services\ClinicSettingsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading($this->app->environment('local'));

        Blade::directive('arabicTime', function (string $expression): string {
            return "<?php echo \\App\\Support\\TimeFormat::arabic($expression); ?>";
        });

        View::composer(['layouts.booking'], function (ViewInstance $view): void {
            $view->with([
                'medicalCenterName' => config('clinic.medical_center.name'),
            ]);
        });

        View::composer('layouts.doctor', function (ViewInstance $view): void {
            /** @var User|null $doctor */
            $doctor = Auth::user();

            if ($doctor === null) {
                return;
            }

            $settings = app(ClinicSettingsService::class)->get($doctor);

            $view->with([
                'clinicBrand' => $settings->clinic_name,
                'clinicDoctorName' => $doctor->name,
                'clinicSpecialty' => $settings->specialty,
            ]);
        });
    }
}
