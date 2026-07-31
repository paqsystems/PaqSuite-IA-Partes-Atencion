<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Menu\MenuAuthorizationService;

final class MenuController extends Controller
{
    public function __construct(
        private readonly MenuAuthorizationService $menuAuthorizationService
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $user = Auth::user();
        $empresaId = (int) ($request->header(config('paqsuite.headers.company', 'X-Company-Id')) ?: 1);

        $items = $this->menuAuthorizationService->authorizedTree((int) $user->id, $empresaId);

        return ApiResponse::success(['items' => $items]);
    }
}
