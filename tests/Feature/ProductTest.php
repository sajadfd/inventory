<?php

use App\Enums\UserType;
use App\Models\Category;
use App\Models\InitialStore;
use App\Models\Product;
use App\Models\SaleList;
use Database\Factories\BrandFactory;
use Database\Factories\UserFactory;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\PurchaseListSeeder;
use Database\Seeders\SaleListSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = UserFactory::new(['type' => UserType::INVENTORY_ADMIN])->create();
    Sanctum::actingAs($user, [], 'api');
});

test('products index does not need auth', function () {
    get('/api/products')->assertStatus(200);
    auth()->logout();
    get('/api/products')->assertStatus(200);
});

test('products index', function () {
    $this->seed([CategorySeeder::class, ProductSeeder::class]);
    $response = $this->getJson('/api/products');
    $response->assertStatus(200);
    expect($response->json('data.total'))->toEqual(Product::count());
    foreach ($response->json('data.data') as $productArray) {
        $imageResponse = Http::withoutVerifying()->get($productArray['image']);
        expect($imageResponse->status())->toEqual(200);
        $thumbnailResponse = Http::withoutVerifying()->get($productArray['thumbnail']);
        expect($thumbnailResponse->status())->toEqual(200);

        $categoryImageResponse = Http::withoutVerifying()->get($productArray['category']['image']);
        expect($categoryImageResponse->status())->toEqual(200);
        $categoryThumbnailResponse = Http::withoutVerifying()->get($productArray['category']['image']);
        expect($categoryThumbnailResponse->status())->toEqual(200);

    }
});
test('index with filters', function () {
    seed([ProductSeeder::class]);
    get('api/products?filter[name]=Ahmed&sort=-name')->assertStatus(200);
});

test('product can be created', function () {
    $category = Category::factory()->create();
    $image = UploadedFile::fake()->image('avatar.jpg');
    $res = $this->post('/api/products', [
        'name' => 'Name1',
        'category_id' => $category->id,
        'sale_price' => 1,
        'image' => $image,
    ]);
    $res->assertStatus(200);

    $product = Product::query()->first();
    expect(Product::count())->toEqual(1)
        ->and($category->products()->count())->toEqual(1)
        ->and(public_path($product->image))->toBeFile();
});

test('product can be created full', function () {
    $category = Category::factory()->create();
    $brand = BrandFactory::new()->create();
    $image = UploadedFile::fake()->image('avatar.jpg');
    $this->post('/api/products', [
        'name' => 'Name1',
        'category_id' => $category->id,
        'sale_price' => 1,
        'initial_purchase_price' => 1,
        'store' => 4,
        'depletion_alert_at' => 1,
        'brand_id' => $brand->id,
        'barcode' => 12345,
        'notes' => 'Notes',
        'is_visible_in_store' => true,
        'image' => $image,
        'source' => 'inside',
    ])->assertStatus(200);
});

test('product can be created with initial store', function () {
    $category = Category::factory()->create();
    $image = UploadedFile::fake()->image('avatar.jpg');
    $res = $this->post('/api/products', [
        'name' => 'Name1',
        'category_id' => $category->id,
        'sale_price' => 1,
        'image' => $image,
        'store' => 5,
        'initial_purchase_price' => 5,
    ]);
    $res->assertStatus(200);

    $product = Product::query()->first();
    expect(Product::count())->toEqual(1)
        ->and(InitialStore::count())->toEqual(1)
        ->and($product->store)->toEqual(5)
        ->and(InitialStore::first()->count)->toEqual(5);
});

test('product can be edited', function () {
    Product::factory()->createOne(['store' => 5]);
    SaleList::factory()->setWithItems(true)->createOne();
    $category = Category::query()->whereHas('products')->first();
    $product = $category->products()->first();
    $category_products_count = $category->products()->count();
    $product_image = $product->image;

    $res = put('/api/products/' . $product->id, [
        'name' => 'Name1',
        'initial_purchase_price' => $product->initialStore?->price,
        'sale_price' => 4,
    ]);
    $res->assertStatus(200);
    expect($category->products()->count())->toEqual($category_products_count)
        ->and($product->refresh()->image)->toEqual($product_image);
});

test('product can be edited with image string or file', function () {
    $this->seed([CategorySeeder::class, ProductSeeder::class]);
    $product = Product::query()->inRandomOrder()->first();
    $res = $this->put('/api/products/' . $product->id, [
        'image' => $product->image
    ]);
    $res->assertStatus(200);

    $product = Product::query()->inRandomOrder()->first();
    $image = UploadedFile::fake()->image('avatar.jpg');
    $res = $this->put('/api/products/' . $product->id, [
        'image' => $image
    ]);
    $res->assertStatus(200);

    $product = Product::query()->inRandomOrder()->first();
    $res = $this->put('/api/products/' . $product->id, [
        'image' => 'malformed/url/file'
    ]);
    $res->assertStatus(422);
});

test('product can be deleted', function () {
    $this->seed([CategorySeeder::class, ProductSeeder::class]);
    $category = Category::query()->whereHas('products')->first();
    $product = $category->products()->first();
    $category_products_count = $category->products()->count();
    $res = $this->delete('/api/products/' . $product->id);
    $res->assertStatus(200);
    expect($category->products()->count())->toEqual($category_products_count - 1);
});

test('product cannot be deleted', function () {
    $this->seed([CategorySeeder::class, ProductSeeder::class, PurchaseListSeeder::class]);
    $product = Product::query()->whereHas('purchaseItems')->inRandomOrder()->first();
    $res = $this->delete('/api/products/' . $product->id);
    $res->assertStatus(422);
});

test('product seeder store', function () {
    $this->seed([CategorySeeder::class, ProductSeeder::class]);

    $initialStores = 0;
    Product::query()->each(function (Product $product) use (&$initialStores) {
        if (!$product->store) {
            expect($product->initialStore()->exists())->toBeFalse();
        } else {
            $initialStores++;
            expect($product->initialStore->count)->toEqual($product->store);
        }
    });

    expect(InitialStore::count())->toEqual($initialStores);
});
