<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class AdminApiController extends Controller
{
    public function users(): JsonResponse
    {
        $users = User::query()
            ->orderByDesc('created_at')
            ->get(['id', 'nickname', 'email', 'role', 'email_verified_at', 'created_at', 'updated_at']);

        return response()->json([
            'users' => $users,
        ]);
    }

    public function syncPrices(): JsonResponse
    {
        return $this->runSyncCommand('itad:sync-prices', 'Price sync completed.');
    }

    public function syncListings(): JsonResponse
    {
        return $this->runSyncCommand('itad:sync-listings', 'Listings sync completed.');
    }

    private function runSyncCommand(string $command, string $successMessage): JsonResponse
    {
        $exitCode = Artisan::call($command);
        $output = trim(Artisan::output());

        if ($exitCode === 0) {
            return response()->json([
                'message' => $successMessage,
                'output' => $output,
            ]);
        }

        return response()->json([
            'message' => 'Command failed: '.$command,
            'output' => $output,
        ], 500);
    }
}
