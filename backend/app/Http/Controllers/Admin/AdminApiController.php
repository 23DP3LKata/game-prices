<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItadSyncLog;
use App\Models\User;
use App\Services\ItadSyncStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class AdminApiController extends Controller
{
    public function __construct(private readonly ItadSyncStatsService $statsService)
    {
    }

    public function users(): JsonResponse
    {
        $users = User::query()
            ->orderByDesc('created_at')
            ->get(['id', 'nickname', 'email', 'role', 'account_status', 'email_verified_at', 'created_at', 'updated_at']);

        return response()->json([
            'users' => $users,
            'sync_overview' => $this->syncOverview(),
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

    public function blockUser(Request $request, User $user): JsonResponse
    {
        if ((int) $request->user()->id === (int) $user->id) {
            return response()->json([
                'message' => 'You cannot block your own account.',
            ], 422);
        }

        if ($user->account_status === 'inactive') {
            return response()->json([
                'message' => 'User is already blocked.',
                'user' => [
                    'id' => $user->id,
                    'account_status' => $user->account_status,
                ],
            ]);
        }

        $user->account_status = 'inactive';
        $user->setRememberToken(null);
        $user->save();

        DB::table(config('session.table', 'sessions'))
            ->where('user_id', $user->id)
            ->delete();

        return response()->json([
            'message' => 'User blocked successfully.',
            'user' => [
                'id' => $user->id,
                'account_status' => $user->account_status,
            ],
            'users' => User::query()
                ->orderByDesc('created_at')
                ->get(['id', 'nickname', 'email', 'role', 'account_status', 'email_verified_at', 'created_at', 'updated_at']),
            'sync_overview' => $this->syncOverview(),
        ]);
    }

    private function runSyncCommand(string $command, string $successMessage): JsonResponse
    {
        $exitCode = Artisan::call($command);
        $output = trim(Artisan::output());

        if ($exitCode === 0) {
            return response()->json([
                'message' => $successMessage,
                'output' => $output,
                'sync_overview' => $this->syncOverview(),
            ]);
        }

        return response()->json([
            'message' => 'Command failed: '.$command,
            'output' => $output,
            'sync_overview' => $this->syncOverview(),
        ], 500);
    }

    /**
     * @return array<string, mixed>
     */
    private function syncOverview(): array
    {
        $latestPrices = ItadSyncLog::query()
            ->where('sync_type', 'prices')
            ->latest('created_at')
            ->first();

        $latestListings = ItadSyncLog::query()
            ->where('sync_type', 'listings')
            ->latest('created_at')
            ->first();

        $logs = ItadSyncLog::query()
            ->latest('created_at')
            ->limit(20)
            ->get([
                'id',
                'sync_type',
                'status',
                'command',
                'stores_total',
                'games_total',
                'output',
                'started_at',
                'finished_at',
                'created_at',
            ]);

        return [
            'stores_total' => $this->statsService->enabledStoresCount(),
            'games_total' => $this->statsService->activeGamesCount(),
            'latest_prices_at' => $latestPrices?->finished_at,
            'latest_listings_at' => $latestListings?->finished_at,
            'logs' => $logs,
        ];
    }
}
