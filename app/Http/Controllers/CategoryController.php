<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\PaginatorService;
use App\Services\UploadImageService;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\SecurityScheme;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Category::class, 'category');
    }

    #[Get('/categories', tags: ['categories'], responses: [new Response(response: 200, description: 'OK')])]
    public function index()
    {
        return ApiResponse::success(PaginatorService::from(Category::query(), CategoryResource::class));
    }

    #[Get('/show/{category}', tags: ['categories'], parameters: [new Parameter('category', 'category', 'Category ID', 'path', true, example: 1)], responses: [new Response(response: 200, description: 'OK')])]
    public function show(Category $category)
    {
        return ApiResponse::success(CategoryResource::make($category));
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();
        (new UploadImageService)->saveAuto($data);

        $category = Category::query()->create($data);
        $category->refresh();
        return ApiResponse::success(CategoryResource::make($category));
    }

    public function update(StoreCategoryRequest $request, Category $category)
    {
        $data = $request->validated();
        (new UploadImageService)->deleteAndSaveAuto($data);
        $category->update($data);
        return ApiResponse::success(CategoryResource::make($category));
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            throw ValidationException::withMessages([__('This category has products, can not be deleted')]);
        }
        return ApiResponse::success($category->delete());
    }
}
