<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\DashboardController;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_index_returns_expected_metrics(): void
    {
        $this->actingAs(User::factory()->create());

        $now = Carbon::parse('2024-08-10 12:30:00');
        Carbon::setTestNow($now);

        $todayStart = $now->copy()->startOfDay();

        Order::factory()->create([
            'created_at' => $todayStart->copy()->addHours(2),
            'grand_total' => 150.25,
            'status' => 'pending',
        ]);
        Order::factory()->create([
            'created_at' => $todayStart->copy()->addHours(4),
            'grand_total' => 99.75,
            'status' => 'paid',
        ]);
        Order::factory()->create([
            'created_at' => $todayStart->copy()->subDay(),
            'grand_total' => 200.00,
            'status' => 'shipped',
        ]);
        Order::factory()->create([
            'created_at' => $todayStart->copy()->addHours(1),
            'grand_total' => 45.50,
            'status' => 'fulfilled',
        ]);
        Order::factory()->create([
            'created_at' => $todayStart->copy()->subDays(2),
            'grand_total' => 80.00,
            'status' => 'pending',
        ]);

        $lowVariantOne = ProductVariant::factory()->create();
        $lowVariantTwo = ProductVariant::factory()->create();
        $healthyVariant = ProductVariant::factory()->create();

        Inventory::factory()->for($lowVariantOne, 'variant')->create([
            'qty_on_hand' => 1,
            'qty_reserved' => 0,
        ]);
        Inventory::factory()->for($lowVariantTwo, 'variant')->create([
            'qty_on_hand' => 2,
            'qty_reserved' => 0,
        ]);
        Inventory::factory()->for($healthyVariant, 'variant')->create([
            'qty_on_hand' => 5,
            'qty_reserved' => 1,
        ]);

        $view = app()->call([DashboardController::class, 'index']);

        Carbon::setTestNow();

        $this->assertSame('admin/dashboard', $view->name());

        $data = $view->getData();

        $this->assertSame(3, $data['todaysOrders']);
        $this->assertEquals(295.5, (float) $data['revenue']);
        $this->assertSame(3, $data['unfulfilled']);

        $this->assertCount(2, $data['lowStock']);
        $this->assertTrue($data['lowStock']->pluck('id')->contains($lowVariantOne->id));
        $this->assertTrue($data['lowStock']->pluck('id')->contains($lowVariantTwo->id));
        $this->assertFalse($data['lowStock']->pluck('id')->contains($healthyVariant->id));
    }
}
