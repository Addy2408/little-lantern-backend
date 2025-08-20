<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\ApiResponse;
use App\Models\Product;

class ProductController extends Controller
{
    use ApiResponse;

    public function getAllProducts(Request $request)
    {
        try {
            Log::info("Fetching all products:");

            $products = Product::all();

            if ($products->isEmpty()) {
                Log::warning("No products found.");
                return $this->sendError(404, 'No products found.', ['success' => false]);
            }

            Log::info("Products fetched successfully.");

            return $this->sendResponse(
                200,
                'Products fetched successfully.',
                [
                    'success' => true,
                    'products' => $products,
                ]
            );
        } catch (Exception $e) {
            Log::error("Error fetching products. {$e->getMessage()}");
            return $this->sendError(
                500,
                'Something went wrong while fetching products.',
                [
                    'success' => false,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    public function getProductById($id)
    {
        try {
            Log::info("Fetching the product with id: {$id}");

            $product = Product::find($id);

            if (!$product) {
                Log::warning("No product found with id: {$id}.");
                return $this->sendError(404, 'No products found', ['success' => false]);
            }

            Log::info("Product fetched successfully with id {$id}.");

            return $this->sendResponse(
                200,
                'Product fetched successfully.',
                [
                    'success' => true,
                    'product_id' => $id,
                    'product_details' => $product,
                ]
            );
        } catch (Exception $e) {
            Log::error("Error fetching product by id. {$e->getMessage()}");
            return $this->sendError(
                500,
                'Something went wrong while fetching product by id.',
                [
                    'success' => false,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    public function createProduct(Request $request)
    {
        try {
            Log::info("Creating a new product: {$request->input('common_name')}");

            $request->validate([
                'common_name' => 'required|string|max:255',
                'scientific_name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
            ]);

            $product = Product::create([
                'common_name' => $request->input('common_name'),
                'scientific_name' => $request->input('scientific_name'),
                'description' => $request->input('description'),
                'price' => $request->input('price'),
            ]);

            Log::info("Product created successfully: {$product->id}");

            return $this->sendResponse(
                201,
                'Product created successfully.',
                [
                    'success' => true,
                    'product' => $product,
                ]
            );
        } catch (Exception $e) {
            Log::error("Error while creating a product: {$e->getMessage()}");
            return $this->sendError(
                500,
                "Something went wrong while creating product.",
                [
                    'success' => false,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    public function updateProduct(Request $request, $id)
    {
        try {
            Log::info("Updating product with id: {$id}");

            $validatedData = $request->validate([
                'common_name'     => 'sometimes|string|max:255',
                'scientific_name' => 'sometimes|string|max:255|nullable',
                'description'     => 'sometimes|string|nullable',
                'price'           => 'sometimes|numeric|min:0',
            ]);

            $product = Product::find($id);

            if (!$product) {
                Log::warning("No product found with id: {$id}");
                return $this->sendError(404, 'No product found.', ['success' => false]);
            }

            $product->update($validatedData);
            $product->refresh();

            Log::info("Product updated successfully for id: {$id}");

            return $this->sendResponse(
                200,
                'Product updated successfully.',
                [
                    'success' => true,
                    'product' => $product,
                ]
            );
        } catch (Exception $e) {
            Log::error("Error while updating a product: {$e->getMessage()}");
            return $this->sendError(
                500,
                "Something went wrong while updating product.",
                [
                    'success' => false,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    public function deleteProduct($id)
    {
        try {
            Log::info("Deleting product with id: {$id}");

            $product = Product::where('id', $id)->delete();

            if (!$product) {
                Log::warning("No product found with id: {$id}.");
                return $this->sendError(404, 'No product found', ['success' => false]);
            }

            Log::info("Product deleted successfully.");

            return $this->sendResponse(
                200,
                'Product deleted successfully.',
                [
                    'success' => true,
                ]
            );
        } catch (Exception $e) {
            Log::error("Error while deleting a product: {$e->getMessage()}");
            return $this->sendError(
                500,
                "Something went wrong while deleting product.",
                [
                    'success' => false,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }
}
