<?php

namespace Insider;

class Insider
{
    public $object = [];

    public function set($key, $data){
        $this->object[$key] = $data;
        return $this->object;
    }

    public function run() {
        add_action('wp_head', function (){
            $this->set('page', $this->_page());

            $this->set('user', $this->_user());

            if ("Product" == $this->get_page_type())
                $this->set('product', $this->_product());

            if ("Category" == $this->get_page_type())
                $this->set('listing', $this->_listing());

            if ("Basket" == $this->get_page_type())
                $this->set('basket', $this->_basket());

            if ("Checkout" == $this->get_page_type())
                $this->set('checkout', $this->_checkout());

            if ("Confirmation" == $this->get_page_type())
                $this->set('confirmation', $this->_confirmation());

            //@dd($this->object);
        });

        add_action('wp_footer', [$this, 'script']);
    }

    public function _confirmation() {
        global $wp;

        $order_id = absint($wp->query_vars['order-received']);
        $order    = wc_get_order( $order_id );

        foreach ($order->get_items() as $item)
            $products[] = [
                'product' => $this->_product($item->get_product_id()),
                'subtotal' => $item->get_subtotal(),
                'quantity' => $item->get_quantity()
            ];

        return [
            'order_id' => $order_id,
            'currency' => 'TRY',
            'total' => $order->get_total(),
            'shipping_cost' => $order->get_shipping_total(),
            'delivery' => [
                'country' => $order->get_shipping_country(),
                'city' => $order->get_shipping_city(),
                'district' => $order->get_shipping_address_1(),
            ],
            'payment_type' => $order->get_payment_method_title(),
            'line_items' => [
                $products
            ],
        ];
    }

    public function _checkout() {
        return $this->_basket();
    }

    public function _basket() {
        $cart = WC()->cart;

        foreach ($cart->get_cart() as $item)
            $products[] = [
                'product' => $this->_product($item['product_id']),
                'subtotal' => $item['line_subtotal'],
                'quantity' => $item['quantity']
            ];

        return [
            'currency' => 'TRY',
            'total' => $cart->total,
            'shipping_cost' => $cart->shipping_total,
            'quantity' => $cart->get_cart_contents_count(),
            'subtotal' => $cart->subtotal,
            'line_items' => $products ?? []
        ];
    }

    public function _user() {
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
            'gdpr_optin' => true,
            'email_optin' => false,
            'sms_optin' => false,
            'language' => 'tr-tr',
            'returning' => true,
            'list_id' => [],
            'pagetype' => $this->get_page_type()
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

        return $data;
    }

    public function _listing() {
        woocommerce_product_loop_start();
            $items['items'][] = $this->_product();
        woocommerce_product_loop_end();

        return $items;
    }

    public function _product($id = null) {
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
            "id" => $id,
            "taxonomy" => array_merge($categories ?? [], $tags ?? []) ?? [],
            "currency" => "TRY",
            "unit_price" => $product->get_regular_price(),
            "unit_sale_price" => $product->get_sale_price(),
            "url" => get_permalink($id),
            "stock" => $product->get_stock_quantity(),
            "color" => "",
            "size" => "",
            "product_image_url" => get_the_post_thumbnail_url($id),
            "custom" => [],
        ];
    }

    public function _page() {
        return [
            'type' => $this->get_page_type()
        ];
    }

    public function get_page_type($type = 8) {
        if (is_home() || "/" == $_SERVER['REQUEST_URI'])
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

    static function json(array $data) : string {
        return json_encode($data);
    }

    public function script() {
        $object = self::json($this->object);

        echo <<<HTML
            <script>
                window.insider_object = window.insider_object || {};
                window.insider_object = $object
            </script>
        HTML;
    }
}