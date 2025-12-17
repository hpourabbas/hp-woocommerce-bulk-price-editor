<?php

namespace HpWoocommerceBulkPriceEditor\App\Controllers;

// If this file is called directly, abort.
use HpWoocommerceBulkPriceEditor\App\Lib\Request;
use HpWoocommerceBulkPriceEditor\App\Lib\Validation;
use HpWoocommerceBulkPriceEditor\App\Models\Brand;
use HpWoocommerceBulkPriceEditor\App\Models\Category;
use HpWoocommerceBulkPriceEditor\App\Models\Product;
use HpWoocommerceBulkPriceEditor\App\Models\ProductQuery;

if (!defined('HP_EXEC')) {
    die;
}

class ProductController
{

    public function index(Request $request): void
    {
        // validation request
        $data = $this->validate($request);


        // load list
        $category_ids = $data['category_ids'];
        $brand_ids = $data['brand_ids'];
        $products = Product::query()
            // filter category_ids
            ->when(count($category_ids) > 0, function (ProductQuery $query) use ($category_ids) {
                $query->filterCategory($category_ids);
            })

            // filter brand_ids
            ->when(count($brand_ids) > 0, function (ProductQuery $query) use ($brand_ids) {
                $query->filterBrand($brand_ids);
            })
            ->paginate($data['page'], $data['per_page']);


        // prepare list
        foreach ($products['data'] as $product) {
            $wcProduct = wc_get_product($product->ID);
            $product->thumbnail = $wcProduct->get_image('thumbnail');
            $product->admin_link = home_url() . "/wp-admin/post.php?post={$product->ID}&action=edit";
            $product->site_link = $wcProduct->get_permalink();

            $ret = Product::calculateNewPrice($product->regular_price, $data);

            $product->price = $product->price ? round($product->price, 2) : 0;
            $product->sale_price = $product->sale_price ? round($product->sale_price, 2) : 0;
            $product->regular_price = $product->regular_price ? round($product->regular_price, 2) : 0;
            $product->new_price = $ret['new_price'];
            $product->new_regular_price = $ret['new_regular_price'];
            $product->new_sale_price = $ret['new_sale_price'];
        }


        wp_send_json_success(['products' => $products]);
    }

    protected function validate(Request $request): array
    {
        $data = [
            'page' => $request->input('page', 1),
            'per_page' => $request->input('per_page', 5),
            'price_type' => $request->input('price_type'),
            'discount_type' => $request->input('discount_type'),
            'price_value' => (float)$request->input('price_value', 0),
            'discount_value' => abs((float)$request->input('discount_value', 0)),
            'discount_from_date' => $request->input('discount_from_date'),
            'discount_to_date' => $request->input('discount_to_date'),
            'category_ids' => $request->input('category_ids', []),
            'brand_ids' => $request->input('brand_ids', []),
        ];


        $rules = [
            'price_type' => ['single_in:p|v'],
            'discount_type' => ['single_in:p|v|'],
        ];


        if (Validation::validate($data, $rules) !== true) {
            wp_send_json_error(['message' => __('Input data is not valid!')], 422);
        }


        return $data;
    }

    public function categories(Request $request): void
    {
        $categories = Category::tree();
        $categories = Category::toFlat($categories);

        wp_send_json_success(['categories' => $categories]);
    }

    public function brands(Request $request): void
    {
        $brands = Brand::tree();
        $brands = Brand::toFlat($brands);

        wp_send_json_success(['brands' => $brands]);
    }

    public function updateAll(Request $request): void
    {
        // validation request
        $data = $this->validate($request);


        // load list
        $category_ids = $data['category_ids'];
        $brand_ids = $data['brand_ids'];
        $product_ids = Product::query()
            // filter category_ids
            ->when(count($category_ids) > 0, function (ProductQuery $query) use ($category_ids) {
                $query->filterCategory($category_ids);
            })

            // filter brand_ids
            ->when(count($brand_ids) > 0, function (ProductQuery $query) use ($brand_ids) {
                $query->filterBrand($brand_ids);
            })
            ->getIds();


        $count = count($product_ids);
        foreach ($product_ids as $product_id) {
            Product::updateNewPrice($product_id, $data);
        }


        wp_send_json_success(['message' => "{$count} " . __("item(s) successfully updated!")]);
    }

}