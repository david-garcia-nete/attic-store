<?php

namespace Tests\Unit\Models;

use App\Models\{Address, CartItem, OrderItem, Payment, Product, ProductCategory, Shipment};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelationshipMethodsTest extends TestCase
{
    use RefreshDatabase;

    public function test_relationship_methods_are_invocable(): void
    {
        $this->assertInstanceOf(MorphTo::class, (new Address())->addressable());
        $this->assertInstanceOf(BelongsTo::class, (new Payment())->order());
        $this->assertInstanceOf(BelongsTo::class, (new Shipment())->order());
        $this->assertInstanceOf(BelongsTo::class, (new CartItem())->cart());
        $this->assertInstanceOf(BelongsTo::class, (new OrderItem())->order());
        $this->assertInstanceOf(HasMany::class, (new Product())->variants());
        $this->assertInstanceOf(BelongsToMany::class, (new Product())->categories());
        $this->assertInstanceOf(BelongsToMany::class, (new ProductCategory())->products());
    }
}
