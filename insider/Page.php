<?php

namespace Insider;

class Page
{
    public function confirmation() {
        global $wp;

        $order_id = absint($wp->query_vars['order-received']);
        $order    = wc_get_order( $order_id );

        foreach ($order->get_items() as $item)
            $products[] = [
                'product' => $this->product($item->get_product_id()),
                'subtotal' => (double) $item->get_subtotal(),
                'quantity' => $item->get_quantity()
            ];

        return [
            'order_id' => (string) $order_id,
            'currency' => 'TRY',
            'total' => $order->get_total(),
            'shipping_cost' => $order->get_shipping_total(),
            'delivery' => [
                'country' => $order->get_shipping_country(),
                'city' => $order->get_shipping_city(),
                'district' => $order->get_shipping_address_1(),
            ],
            'payment_type' => $order->get_payment_method_title(),
            'line_items' => $products
        ];
    }

    public function checkout() {
        return $this->basket();
    }

    public function basket() {
        $cart = WC()->cart;

        foreach ($cart->get_cart() as $item)
            $products[] = [
                'product' => $this->product($item['product_id']),
                'subtotal' => $item['line_subtotal'],
                'quantity' => $item['quantity']
            ];

        return [
            'currency' => 'TRY',
            'total' => (double) $cart->total,
            'shipping_cost' => $cart->shipping_total,
            'quantity' => $cart->get_cart_contents_count(),
            'subtotal' => $cart->subtotal,
            'line_items' => $products ?? []
        ];
    }

    public function user() {
        $data = [
            'uuid' => '',
            'gender' => '',
            'birthday' => '',
            'has_transacted' => false,
            'transaction_count' => '', // 0
            'name' => '',
            'surname' => '',
            'username' => '',
            'email' => '',
            'phone_number' => null,
            'language' => 'tr-tr',
            'returning' => true,
            'list_id' => [],
            'pagetype' => $this->get_type()
        ];

        if (!is_user_logged_in())
            return $data;

        $user_id = get_current_user_id();
        $user = get_userdata($user_id);

        $data["uuid"] = md5($user_id);
        $data["gender"] = $user->gender;
        $data["birthday"] = $user->birthday;
        $data["name"] = $user->first_name;
        $data["surname"] = $user->last_name;
        $data["username"] = $user->display_name;
        $data["email"] = $user->billing_email;
        $data["phone_number"] = $user->billing_phone;

        $data['gdpr_optin'] = true; //get_user_meta(get_current_user_id(), 'gdpr_optin', true) === "true";
        $data['email_optin'] = get_user_meta(get_current_user_id(), 'email_optin', true) === "true";
        $data['sms_optin'] = get_user_meta(get_current_user_id(), 'sms_optin', true) === "true";

        return $data;
    }

    public function listing() {
        woocommerce_product_loop_start();
        $items['items'][] = $this->product();
        woocommerce_product_loop_end();

        return $items;
    }

    public function product($id = null) {
        if (null == $id){
            $id = get_the_ID();
        }

        $product = wc_get_product( $id );

        if (!$product) return;

        foreach ($product->get_category_ids() as $category_id) {
            $category = get_term($category_id, 'product_cat');
            $categories[] = $category->name;
        }

        foreach ($product->get_tag_ids() as $tag_id) {
            $tag = get_term($tag_id, 'product_tag');
            $tags[] = $tag->name;
        }

        return [
            "name" => $product->get_name(),
            "id" => (string) $id,
            "taxonomy" => array_merge($categories ?? [], $tags ?? []) ?? [],
            "currency" => "TRY",
            "unit_price" => (double) $product->get_regular_price(),
            "unit_sale_price" => (double) $product->get_sale_price(),
            "url" => get_permalink($id),
            "stock" => $product->get_stock_quantity(),
            "color" => "",
            "size" => "",
            "product_image_url" => get_the_post_thumbnail_url($id) ?? wc_placeholder_img_src(),
            "custom" => [],
        ];
    }

    public function page() {
        return [
            'type' => $this->get_type()
        ];
    }

    public function get_type($type = 8) {
        if (is_front_page())
            $type = 1;

        if (is_product())
            $type = 2;

        if (is_product_category())
            $type = 3;

        if (is_search())
            $type = 4;

        if (is_cart())
            $type = 5;

        if (is_checkout())
            $type = 6;

        if (is_checkout() && !empty(is_wc_endpoint_url('order-received')))
            $type = 7;

        switch ($type){
            case 1: return "Home";
            case 2: return "Product";
            case 3: return "Category";
            case 4: return "Search";
            case 5: return "Basket";
            case 6: return "Checkout";
            case 7: return "Confirmation";
            case 8: return "Content";
        }
    }
}