<?php

namespace HpWoocommerceBulkPriceEditor\App\Models;

// If this file is called directly, abort.
if (!defined('HP_EXEC')) {
    die;
}

class Product
{

    public static function query(): ProductQuery
    {
        return new ProductQuery();
    }

    public static function calculateNewPrice($regular_price, array $data = []): array
    {
        $priceType = $data['price_type'] ?? null;
        $discountType = $data['discount_type'] ?? null;
        $priceValue = $data['price_value'] ?? null;
        $discountValue = $data['discount_value'] ?? null;
        $discountFromDate = $data['discount_from_date'] ?? null;
        $discountToDate = $data['discount_to_date'] ?? null;

        $new_regular_price = $regular_price;

        switch ($priceType) {
            case 'v':
                $new_regular_price = $new_regular_price + $priceValue;
                break;
            case 'p':
                $new_regular_price = $new_regular_price + round(($priceValue * $new_regular_price) / 100, 2);
                break;
        }

        $new_sale_price = $new_regular_price;

        switch ($discountType) {
            case 'v':
                $new_sale_price = $new_sale_price - $discountValue;
                break;
            case 'p':
                $new_sale_price = $new_sale_price - round(($discountValue * $new_sale_price) / 100, 2);
                break;
        }


        $now = wp_date('Y/m/d');
        $now_ts = strtotime($now);
        $from_ts = null;
        $to_ts = null;
        if ($discountFromDate) {
            $from_ts = strtotime($discountFromDate);
        }
        if ($discountToDate) {
            $to_ts = strtotime($discountToDate);
        }


        if (($from_ts && $now_ts < $from_ts) || ($to_ts && $now_ts > $to_ts)) {
            $new_price = $new_regular_price;
        } else {
            $new_price = $new_sale_price;
        }

        return [
            'new_price' => $new_price < 0 ? 0 : $new_price,
            'new_regular_price' => $new_regular_price < 0 ? 0 : $new_regular_price,
            'new_sale_price' => $new_sale_price < 0 ? 0 : $new_sale_price,
        ];
    }

    public static function updateNewPrice($product_id, array $data = []): void
    {
        // get woocommerce product
        $product = wc_get_product($product_id);

        if (!$product) {
            return;
        }


        // calculate new price
        if (!$regular_price = $product->get_regular_price()) {
            $regular_price = 0;
        }
        $ret = self::calculateNewPrice($regular_price, $data);


        $new_regular_price = $ret['new_regular_price'];
        $new_sale_price = $ret['new_sale_price'];
        $sale_start_date = $data['discount_from_date'] ?? null;
        $sale_end_date = $data['discount_to_date'] ?? null;


        // set regular price
        $product->set_regular_price($new_regular_price);

        // set sale price
        $product->set_sale_price($new_sale_price);

        // set from date
        if ($sale_start_date) {
            $product->set_date_on_sale_from(strtotime($sale_start_date));
        } else {
            $product->set_date_on_sale_from('');
        }

        // set to date
        if ($sale_end_date) {
            $product->set_date_on_sale_to(strtotime($sale_end_date));
        } else {
            $product->set_date_on_sale_to('');
        }


        // save changes
        $product->save();
    }

}
