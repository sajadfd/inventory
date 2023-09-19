<?php

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Enums\UserType;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Factories\UserFactory;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $user = UserFactory::new(['type' => UserType::INVENTORY_ADMIN])->create();
    Sanctum::actingAs($user);
});

test('categories index', function () {
    $this->seed([CategorySeeder::class, ProductSeeder::class]);
    $res = $this->get('api/categories');
    $res->assertStatus(200);

    foreach ($res->json('data.data') as $categoryAsArray) {
        $imageResponse = Http::withoutVerifying()->get($categoryAsArray['image']);
        expect($imageResponse->status())->toEqual(200);
        $thumbnailResponse = Http::withoutVerifying()->get($categoryAsArray['thumbnail']);
        expect($thumbnailResponse->status())->toEqual(200);
    }
});

test('get categories products', function () {
    $this->seed([CategorySeeder::class, ProductSeeder::class]);
    $category = Category::query()->whereHas('products')->inRandomOrder()->first();
    $res = $this->get('api/products?category_id=' . $category->id);
    $res->assertStatus(200);
    expect($res->json('data.total'))->toEqual($category->products()->count());
});

test('category can be created', function () {
    $res = $this->post("api/categories", []);
    $res->assertStatus(422);

    $res = $this->post("api/categories", ["name" => "Cat1"]);
    $res->assertStatus(200);
    $res = $this->post("api/categories", ["name" => "Cat1"]);
    $res->assertStatus(422);
    expect(Category::count())->toEqual(1);
});

test('category can be created with image and shown', function () {
    $image = UploadedFile::fake()->image('image.png');
    $res = $this->post("api/categories", [
        "name" => "cat1",
        "image" => $image,
    ]);
    $res->assertStatus(200);
    expect(Str::startsWith($res->json('data.image'), config('app.url')))->toBeTrue();
    $category_id = $res->json('data.id');
    $res = $this->get("api/categories/" . $category_id);
    $res->assertStatus(200);

    $imageResponse = Http::withoutVerifying()->get($res->json('data.image'));
    expect($imageResponse->status())->toEqual(200);
});

test('category can be updated', function () {
    $this->seed([CategorySeeder::class, ProductSeeder::class]);
    $category = Category::query()->inRandomOrder()->first();
    $res = $this->put("api/categories/" . $category->id, [
        "name" => "cat1",
    ]);
    $res->assertStatus(200);
    $category->refresh();
    expect($category->name)->toEqual('cat1');
});

test('category can be updated with same image', function () {
    $this->seed([CategorySeeder::class, ProductSeeder::class]);
    $category = Category::query()->inRandomOrder()->first();
    $oldImage = $category->image;
    $oldImageUrl = url($category->image);
    $res = $this->put("api/categories/" . $category->id, [
        "image" => $oldImageUrl,
    ]);
    $res->assertStatus(200);
    $category->refresh();
    expect($category->image)->toEqual($oldImage);
});

test('category can be updated with new image', function () {
    $this->seed([CategorySeeder::class, ProductSeeder::class]);
    $category = Category::query()->inRandomOrder()->first();
    $oldImage = $category->image;

    $image = UploadedFile::fake()->image('image.png');
    $res = $this->put("api/categories/" . $category->id, [
        "image" => $image,
    ]);
    $res->assertStatus(200);
    $category->refresh();
    $this->assertNotEquals($oldImage, $category->image);
});

test('category cannot be deleted if has products', function () {
    $this->seed([CategorySeeder::class, ProductSeeder::class]);
    $category = Category::query()->whereHas('products')->inRandomOrder()->first();
    $oldCount = Category::count();
    $res = $this->delete("api/categories/" . $category->id);
    $res->assertStatus(422);
    expect(Category::count())->toEqual($oldCount);
});

test('category cannot be deleted', function () {
    $this->seed([CategorySeeder::class, ProductSeeder::class]);
    Category::factory()->create();
    $category = Category::query()->whereDoesntHave('products')->inRandomOrder()->first();
    $oldCount = Category::count();

    $res = $this->delete("api/categories/" . $category->id);
    $res->assertStatus(200);
    expect(Category::count())->toEqual($oldCount - 1);
});
