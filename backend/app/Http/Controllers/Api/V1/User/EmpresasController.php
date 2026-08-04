<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Security\UserEmpresasQueryRepository;

final class EmpresasController extends Controller
{
    public function __construct(
        private readonly UserEmpresasQueryRepository $userEmpresasQueryRepository
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $userId = (int) Auth::id();

        return ApiResponse::success([
            'items' => $this->userEmpresasQueryRepository->listForUser($userId),
        ]);
    }
}
