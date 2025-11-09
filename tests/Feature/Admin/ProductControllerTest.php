<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\ProductController;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_store_creates_product_without_image(): void
    {
        $this->actingAs(User::factory()->create());
        app('session')->start();

        if (! Route::has('products.edit')) {
            Route::get('/admin/products/{product}/edit', fn () => null)->name('products.edit');
        }

        $payload = [
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => '19.99',
            'description' => 'A product for testing',
        ];

        $request = Request::create('/admin/products', 'POST', $payload);
        $request->setUserResolver(fn () => auth()->user());
        app()->instance('request', $request);
        request()->merge($payload);

        /** @var RedirectResponse $response */
        $response = app()->call([ProductController::class, 'store']);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('Product created', session('ok'));
        $this->assertDatabaseHas('products', [
            'slug' => 'test-product',
            'name' => 'Test Product',
            'price' => '19.99',
        ]);
    }

    public function test_update_with_image_uses_media_library_mocks(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        app('session')->start();

        $product = Product::factory()->create([
            'name' => 'Original',
            'slug' => 'original-slug',
            'price' => 15.00,
        ]);

        $mock = Mockery::mock(Product::class)->makePartial();
        $mock->setTable($product->getTable());
        $mock->setConnection($product->getConnection());
        $mock->setRawAttributes($product->getAttributes(), true);
        $mock->exists = true;
        $mock->shouldReceive('clearMediaCollection')->once();
        $mock->shouldReceive('addMediaFromRequest')->once()->andReturnSelf();

        $payload = [
            'name' => 'Updated Product',
            'slug' => 'updated-slug',
            'price' => '29.99',
            'description' => 'Updated description',
            'is_active' => false,
        ];

        $request = Request::create('/admin/products/' . $product->id, 'PUT', $payload);
        $request->setUserResolver(fn () => $user);
        $request->files->set('image', UploadedFile::fake()->image('cover.jpg'));
        app()->instance('request', $request);
        request()->merge($payload);
        URL::setPreviousUrl('http://localhost/admin/products/' . $product->id . '/edit');

        /** @var RedirectResponse $response */
        $response = app()->call([ProductController::class, 'update'], ['product' => $mock]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('Saved', session('ok'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'slug' => 'updated-slug',
            'price' => '29.99',
            'is_active' => 0,
        ]);
    }

    public function test_destroy_removes_product_and_sets_flash(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        app('session')->start();

        $product = Product::factory()->create();

        /** @var RedirectResponse $response */
        $response = app()->call([ProductController::class, 'destroy'], ['product' => $product]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('Deleted', session('ok'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
