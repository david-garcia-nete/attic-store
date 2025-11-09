<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\DashboardController;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Register a minimal route for the dashboard for testing
        Route::middleware('web')->group(function () {
            Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');
        });
    }

    public function test_dashboard_displays_key_metrics(): void
    {
        // Orders: create 3 today (2 unfulfilled), 1 past
        $today = now();
        Order::factory()->create(['grand_total' => 50.25, 'status' => 'paid', 'created_at' => $today]);
        Order::factory()->create(['grand_total' => 20.00, 'status' => 'pending', 'created_at' => $today]);
        Order::factory()->create(['grand_total' => 15.75, 'status' => 'fulfilled', 'created_at' => $today]);
        Order::factory()->create(['grand_total' => 99.99, 'status' => 'paid', 'created_at' => $today->copy()->subDay()]);

        // Low stock variants
        $p = Product::factory()->create();
        $low1 = ProductVariant::factory()->for($p)->create();
        Inventory::factory()->for($low1, 'variant')->create(['qty_on_hand' => 1, 'qty_reserved' => 0]);
        $low2 = ProductVariant::factory()->for($p)->create();
        Inventory::factory()->for($low2, 'variant')->create(['qty_on_hand' => 2, 'qty_reserved' => 0]);
        // Not low stock
        $ok = ProductVariant::factory()->for($p)->create();
        Inventory::factory()->for($ok, 'variant')->create(['qty_on_hand' => 10, 'qty_reserved' => 1]);

        $resp = $this->get(route('admin.dashboard'));
        $resp->assertOk();

        // Expectations based on controller logic
        $resp->assertSee("Today's Orders: 3", false);
        $resp->assertSee("Today's Revenue: $" . number_format(50.25 + 20.00 + 15.75, 2), false);
        // Unfulfilled counts paid + pending across all time (2 from today + 1 past)
        $resp->assertSeeText('Unfulfilled: 3');
        $resp->assertSeeText('Low Stock: 2');
    }
}
