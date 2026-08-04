<?php

namespace App\Providers;

use App\Http\Middleware\EnsureFirstLoginCompletedMiddleware;
use App\Http\Middleware\EnsurePartesFunctionalProfile;
use App\Http\Middleware\EnsurePartesNotCliente;
use App\Http\Middleware\EnsureSeguridadAdminMiddleware;
use App\Repositories\Sp\SpAccesoTotalChecker;
use App\Repositories\Sp\SpLlmCredentialRepository;
use App\Services\ChatAssistant\ManifestChatCorpusProvider;
use App\Services\Llm\HostHttpLlmChatCompletionClient;
use App\Services\Partes\SmartCapture\LlmPartesSmartCaptureProposal;
use App\Services\Partes\SmartCapture\PartesSmartCaptureProposalPort;
use App\Services\Partes\SmartCapture\PartesTareaSmartCaptureCatalogResolver;
use App\Services\Partes\SmartCapture\PartesTareaSmartCaptureTurnService;
use PaqSuite\LaravelCore\ChatAssistant\ChatAssistantTurnService;
use PaqSuite\LaravelCore\SmartCapture\SmartCaptureTurnGuard;
use PaqSuite\LaravelCore\ChatAssistant\ChatAssistantTurnValidator;
use PaqSuite\LaravelCore\ChatAssistant\Contracts\ChatCorpusProvider;
use PaqSuite\LaravelCore\ChatAssistant\Contracts\LlmChatCompletionClient;
use PaqSuite\LaravelCore\I18n\LocaleNormalizer;
use PaqSuite\LaravelCore\Llm\Contracts\ActiveLlmCredentialPreferenceRepository;
use PaqSuite\LaravelCore\Llm\Contracts\LlmCredentialRepository;
use PaqSuite\LaravelCore\Llm\Contracts\SecretCipher;
use PaqSuite\LaravelCore\Llm\LaravelCryptSecretCipher;
use PaqSuite\LaravelCore\Llm\LlmCredentialResolver;
use PaqSuite\LaravelCore\Llm\LlmCredentialService;
use App\Repositories\Sp\SpCaller;
use App\Repositories\Sp\SpCompanyAllowedChecker;
use App\Repositories\Sp\SpEmpresaAdminRepository;
use App\Repositories\Sp\SpMenuQueryRepository;
use App\Repositories\Sp\SpParametroRepository;
use App\Repositories\Sp\SpPermisoAdminRepository;
use App\Repositories\Sp\SpRolAdminRepository;
use App\Repositories\Sp\SpRolAtributosRepository;
use App\Repositories\Sp\SpUserAdminRepository;
use App\Repositories\Sp\SpUserEmpresasQueryRepository;
use App\Repositories\Sp\SpUserPreferencesRepository;
use App\Services\Auth\PartesPostLoginBusinessGate;
use App\Services\Auth\PostLoginBusinessGate;
use App\Services\Auth\SpUserEmpresasResolver;
use App\Services\Auth\UserEmpresasResolver;
use App\Tenancy\HostMenuProcedimientoChecker;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use PaqSuite\LaravelCore\Auth\DictionaryAuthParametroStore;
use PaqSuite\LaravelCore\Auth\ParametroStore;
use App\Repositories\Sp\SpUserEffectiveMenuPermissions;
use PaqSuite\LaravelCore\Menu\MenuAuthorizationService;
use PaqSuite\LaravelCore\Menu\UserEffectiveMenuPermissions;
use PaqSuite\LaravelCore\Menu\MenuQueryRepository;
use PaqSuite\LaravelCore\Parametros\Contracts\ParametroRepository;
use PaqSuite\LaravelCore\Providers\PaqSuiteCoreServiceProvider;
use PaqSuite\LaravelCore\Security\AccesoTotalChecker;
use PaqSuite\LaravelCore\Security\CompanyAllowedChecker;
use PaqSuite\LaravelCore\Security\EmpresaAdminRepository;
use PaqSuite\LaravelCore\Security\PermisoAdminRepository;
use PaqSuite\LaravelCore\Security\RolAdminRepository;
use PaqSuite\LaravelCore\Security\RolAtributosRepository;
use PaqSuite\LaravelCore\Security\UserAdminRepository;
use PaqSuite\LaravelCore\Security\UserEmpresasQueryRepository;
use PaqSuite\LaravelCore\Security\UserPreferencesRepository;
use PaqSuite\LaravelCore\Tenancy\MenuProcedimientoChecker;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SpCaller::class);

        $this->app->singleton(ParametroStore::class, function ($app) {
            return new DictionaryAuthParametroStore($app->make(ParametroRepository::class));
        });

        $this->app->singleton(PostLoginBusinessGate::class, PartesPostLoginBusinessGate::class);
        $this->app->singleton(UserEmpresasResolver::class, SpUserEmpresasResolver::class);

        $this->app->singleton(ParametroRepository::class, SpParametroRepository::class);
        $this->app->singleton(AccesoTotalChecker::class, SpAccesoTotalChecker::class);
        $this->app->singleton(UserAdminRepository::class, SpUserAdminRepository::class);
        $this->app->singleton(EmpresaAdminRepository::class, SpEmpresaAdminRepository::class);
        $this->app->singleton(RolAdminRepository::class, SpRolAdminRepository::class);
        $this->app->singleton(RolAtributosRepository::class, SpRolAtributosRepository::class);
        $this->app->singleton(PermisoAdminRepository::class, SpPermisoAdminRepository::class);
        $this->app->singleton(MenuQueryRepository::class, SpMenuQueryRepository::class);
        $this->app->singleton(UserEffectiveMenuPermissions::class, SpUserEffectiveMenuPermissions::class);
        $this->app->singleton(MenuAuthorizationService::class, function ($app) {
            return new MenuAuthorizationService(
                $app->make(MenuQueryRepository::class),
                $app->make(AccesoTotalChecker::class),
                $app->make(PermisoAdminRepository::class),
                $app->make(UserEffectiveMenuPermissions::class)
            );
        });
        $this->app->singleton(MenuProcedimientoChecker::class, HostMenuProcedimientoChecker::class);
        $this->app->singleton(UserPreferencesRepository::class, SpUserPreferencesRepository::class);
        $this->app->singleton(UserEmpresasQueryRepository::class, SpUserEmpresasQueryRepository::class);
        $this->app->singleton(CompanyAllowedChecker::class, SpCompanyAllowedChecker::class);

        $this->app->singleton(SpLlmCredentialRepository::class);
        $this->app->singleton(LlmCredentialRepository::class, SpLlmCredentialRepository::class);
        $this->app->singleton(ActiveLlmCredentialPreferenceRepository::class, SpLlmCredentialRepository::class);
        $this->app->singleton(SecretCipher::class, LaravelCryptSecretCipher::class);
        $this->app->singleton(LlmCredentialService::class, function ($app) {
            $repo = $app->make(SpLlmCredentialRepository::class);

            return new LlmCredentialService(
                $repo,
                $repo,
                $app->make(SecretCipher::class),
            );
        });
        $this->app->singleton(LlmCredentialResolver::class, function ($app) {
            $repo = $app->make(SpLlmCredentialRepository::class);

            return new LlmCredentialResolver(
                $repo,
                $repo,
                $app->make(SecretCipher::class),
            );
        });
        $this->app->singleton(ChatCorpusProvider::class, ManifestChatCorpusProvider::class);
        $this->app->singleton(LlmChatCompletionClient::class, HostHttpLlmChatCompletionClient::class);
        $this->app->singleton(ChatAssistantTurnService::class, function ($app) {
            return new ChatAssistantTurnService(
                new ChatAssistantTurnValidator(),
                $app->make(LlmCredentialResolver::class),
                $app->make(ChatCorpusProvider::class),
                $app->make(LlmChatCompletionClient::class),
                $app->make(UserPreferencesRepository::class),
                new LocaleNormalizer(config('paqsuite.supported_locales', ['es', 'en', 'pt', 'fr', 'it'])),
            );
        });

        // GEN-03 Smart Capture (TR-010)
        $this->app->singleton(SmartCaptureTurnGuard::class);
        $this->app->singleton(PartesTareaSmartCaptureCatalogResolver::class);
        $this->app->singleton(PartesSmartCaptureProposalPort::class, LlmPartesSmartCaptureProposal::class);
        $this->app->singleton(PartesTareaSmartCaptureTurnService::class);

        // GEN-14 Excel import (TR-009)
        $this->app->singleton(\App\Repositories\Sp\ExcelImport\SpExcelImportRepository::class);
        $this->app->singleton(
            \PaqSuite\LaravelCore\ExcelImport\Contracts\ExcelImportRepository::class,
            \App\Repositories\Sp\ExcelImport\SpExcelImportRepository::class
        );
        $this->app->singleton(
            \PaqSuite\LaravelCore\ExcelImport\Contracts\ExcelWorkbookParser::class,
            \PaqSuite\LaravelCore\ExcelImport\ZipXmlExcelWorkbookParser::class
        );
        $this->app->singleton(
            \PaqSuite\LaravelCore\ExcelImport\Contracts\ExcelImportBinaryExporter::class,
            \PaqSuite\LaravelCore\ExcelImport\MinimalXlsxExcelImportBinaryExporter::class
        );
        $this->app->singleton(
            \PaqSuite\LaravelCore\ExcelImport\Contracts\ExcelImportAuditPort::class,
            \App\Services\ExcelImport\NullExcelImportAuditPort::class
        );
        $this->app->singleton(
            \PaqSuite\LaravelCore\ExcelImport\Contracts\ExcelImportNotificationPort::class,
            \App\Services\ExcelImport\NullExcelImportNotificationPort::class
        );
        $this->app->singleton(
            \PaqSuite\LaravelCore\ExcelImport\Contracts\ExcelImportTaskDispatcher::class,
            \App\Services\ExcelImport\SyncNoopExcelImportTaskDispatcher::class
        );
        $this->app->singleton(\PaqSuite\LaravelCore\ExcelImport\ExcelImportSettings::class);
        $this->app->singleton(\PaqSuite\LaravelCore\ExcelImport\ExcelImportCapabilityGuard::class);
        $this->app->singleton(\PaqSuite\LaravelCore\ExcelImport\ExcelImportAsyncThreshold::class);
        $this->app->singleton(\App\Services\ExcelImport\PartesTareasImportHandler::class);
        $this->app->singleton(\PaqSuite\LaravelCore\ExcelImport\ExcelImportHandlerRegistry::class, function ($app) {
            $registry = new \PaqSuite\LaravelCore\ExcelImport\ExcelImportHandlerRegistry();
            $registry->register(
                'partes.tareas.import',
                $app->make(\App\Services\ExcelImport\PartesTareasImportHandler::class)
            );

            return $registry;
        });
        $this->app->singleton(\PaqSuite\LaravelCore\ExcelImport\ExcelImportValidationOrchestrator::class);
        $this->app->singleton(\PaqSuite\LaravelCore\ExcelImport\ExcelImportProcessOrchestrator::class);

        $this->app->bind(
            \App\Domain\Repositories\SystemStatusRepositoryInterface::class,
            \App\Infrastructure\Repositories\ConfigSystemStatusRepository::class
        );
    }

    public function boot(Router $router): void
    {
        Sanctum::usePersonalAccessTokenModel(\App\Models\PersonalAccessToken::class);

        foreach (PaqSuiteCoreServiceProvider::tenancyMiddlewareAliases() as $alias => $class) {
            $router->aliasMiddleware($alias, $class);
        }

        $router->aliasMiddleware('paqsuite.firstLogin', EnsureFirstLoginCompletedMiddleware::class);
        $router->aliasMiddleware('partes.profile', EnsurePartesFunctionalProfile::class);
        $router->aliasMiddleware('partes.notCliente', EnsurePartesNotCliente::class);
        $router->aliasMiddleware('paqsuite.seguridadAdmin', EnsureSeguridadAdminMiddleware::class);

        // SQL Server con locale dmy: forzar interpretación ymd en cada conexión.
        $this->app->resolving('db', function () {
            try {
                if (DB::connection()->getDriverName() === 'sqlsrv') {
                    DB::statement('SET DATEFORMAT ymd');
                }
            } catch (\Throwable) {
                // ignore during early boot
            }
        });
    }
}
