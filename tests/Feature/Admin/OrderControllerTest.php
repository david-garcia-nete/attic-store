<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\OrderController;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_orders_view(): void
    {
        $this->actingAs(User::factory()->create());

        $variants = ProductVariant::factory()->count(3)->create();

        foreach ($variants as $variant) {
            $order = Order::factory()->create();
            $order->items()->create([
                'product_variant_id' => $variant->id,
                'quantity' => 2,
                'unit_price' => 19.99,
                'line_total' => 39.98,
            ]);
            $order->addresses()->create([
                'type' => 'shipping',
                'name' => 'Test Recipient',
                'line1' => '123 Test St',
                'city' => 'Testville',
                'state' => 'CA',
                'postal_code' => '90001',
                'country' => 'US',
            ]);
            $order->payments()->create([
                'provider' => 'stripe',
                'status' => 'captured',
                'amount' => 39.98,
            ]);
            $order->shipments()->create([
                'carrier' => 'ups',
                'service' => 'ground',
            ]);
        }

        $view = app()->call([OrderController::class, 'index']);

        $this->assertSame('admin/orders_index', $view->name());

        $data = $view->getData();

        $this->assertArrayHasKey('orders', $data);
        $this->assertInstanceOf(LengthAwarePaginator::class, $data['orders']);
        $this->assertGreaterThanOrEqual(3, $data['orders']->total());
    }

    public function test_show_returns_order_with_eager_loaded_relations(): void
    {
        $this->actingAs(User::factory()->create());

        $variant = ProductVariant::factory()->create();
        $order = Order::factory()->create();

        $order->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 25.00,
            'line_total' => 25.00,
        ]);
        $order->addresses()->create([
            'type' => 'billing',
            'name' => 'Billing Person',
            'line1' => '456 Billing Rd',
            'city' => 'Billtown',
            'state' => 'WA',
            'postal_code' => '98052',
            'country' => 'US',
        ]);
        $order->payments()->create([
            'provider' => 'paypal',
            'status' => 'captured',
            'amount' => 25.00,
        ]);
        $order->shipments()->create([
            'carrier' => 'usps',
            'service' => 'priority',
        ]);

        $view = app()->call([OrderController::class, 'show'], ['order' => $order]);

        $this->assertSame('admin/orders_show', $view->name());

        $orderFromView = $view->getData()['order'];

        $this->assertTrue($orderFromView->relationLoaded('items'));
        $this->assertTrue($orderFromView->relationLoaded('addresses'));
        $this->assertTrue($orderFromView->relationLoaded('payments'));
        $this->assertTrue($orderFromView->relationLoaded('shipments'));

        $this->assertGreaterThan(0, $orderFromView->items->count());
        $this->assertNotNull($orderFromView->items->first()->variant);
    }

    public function test_update_changes_status_and_sets_flash_message(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Order::factory()->create(['status' => 'pending']);

        $request = Request::create('/admin/orders/' . $order->id, 'PUT', ['status' => 'shipped']);
        $request->setUserResolver(fn () => $user);
        app()->instance('request', $request);
        request()->merge(['status' => 'shipped']);
        URL::setPreviousUrl('http://localhost/admin/orders');

        /** @var RedirectResponse $response */
        $response = app()->call([OrderController::class, 'update'], ['order' => $order]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('shipped', $order->fresh()->status);
        $this->assertSame('Updated', session('ok'));
    }
}
