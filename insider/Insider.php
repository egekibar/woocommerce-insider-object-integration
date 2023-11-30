<?php

namespace Insider;

class Insider
{
    public function __construct() {
        $this->page = new Page();
    }

    public $object = [];

    public function set($key, $data){
        $this->object[$key] = $data;
        return $this->object;
    }

    public function run() {
//        $this->check_plugin_update();

        if ($this->check_plugin_requirements())
            return;

        $this->opt_fields();

        add_action('wp_head', function (){
            $this->set('page', $this->page->page());

            $this->set('user', $this->page->user());

            if ("Product" == $this->page->get_type())
                $this->set('product', $this->page->product());

            if ("Category" == $this->page->get_type())
                $this->set('listing', $this->page->listing());

            if ("Basket" == $this->page->get_type())
                $this->set('basket', $this->page->basket());

            if ("Checkout" == $this->page->get_type())
                $this->set('checkout', $this->page->checkout());

            if ("Confirmation" == $this->page->get_type()){
                $this->set('confirmation', $this->page->confirmation());
                $this->set('transaction', $this->page->confirmation());
            }

            if ("Search" == $this->page->get_type())
                $this->set('search', $this->page->listing());

            //@dd($this->object);

            $this->script();
        });
    }

    public function check_plugin_requirements(){
        if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))))
            return add_action( 'admin_notices', function () {
                ?>
                <div class="error">
                    <p><b>Insider Object: </b> WooCommerce bulunamadı. <a href="/wp-admin/plugin-install.php?tab=plugin-information&plugin=woocommerce" target="_blank">Buradan</a> kurulumunu gerçekleştiriniz.</p>
                </div>
                <?php
            } );
    }

    public function check_plugin_update($transient){
        if (empty($transient->checked)) return $transient;

        $update = new \Insider\Update();

        $plugin_file = 'woocommerce-insider-object-plugin/insider-object.php';

        if ($config = $update->config())
            $transient->response[$plugin_file] = $config;

        return $transient;
    }

    public function opt_fields($account = true, $register = true, $dashboard = true){
        if ($account):
            add_action('woocommerce_edit_account_form', function () {
                $user_id = get_current_user_id();
                $email_optin = get_user_meta($user_id, 'email_optin', true);
                $sms_optin = get_user_meta($user_id, 'sms_optin', true);
                ?>
                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="email_optin">
                        <input type="checkbox" name="email_optin" id="email_optin" <?php checked($email_optin, 'true'); ?> />
                        Kişiselleştirilmiş içerikler ve güncellemeler almak için e-posta yoluyla bilgilendirilmek istiyorum.
                    </label>
                </p>

                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="sms_optin">
                        <input type="checkbox" name="sms_optin" id="sms_optin" <?php checked($sms_optin, 'true'); ?> />
                        Kişiselleştirilmiş içerikler ve güncellemeler almak için SMS yoluyla bilgilendirilmek istiyorum.
                    </label>
                </p>
                <?php
            });

            add_action('woocommerce_save_account_details', function ($user_id) {
                $email_optin_value = isset($_POST['email_optin']) ? 'true' : 'false';
                $sms_optin_value = isset($_POST['sms_optin']) ? 'true' : 'false';

                update_user_meta($user_id, 'email_optin', $email_optin_value);
                update_user_meta($user_id, 'sms_optin', $sms_optin_value);
            });
        endif;

        if ($register):
            add_action('woocommerce_register_form', function () {
                ?>
                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="email_optin_registration">
                        <input type="checkbox" name="email_optin_registration" id="email_optin_registration" <?php checked(isset($_POST['email_optin_registration']) && $_POST['email_optin_registration'] === 'true'); ?> />
                        Kişiselleştirilmiş içerikler ve güncellemeler almak için e-posta yoluyla bilgilendirilmek istiyorum.
                    </label>
                </p>

                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="sms_optin_registration">
                        <input type="checkbox" name="sms_optin_registration" id="sms_optin_registration" <?php checked(isset($_POST['sms_optin_registration']) && $_POST['sms_optin_registration'] === 'true'); ?> />
                        Kişiselleştirilmiş içerikler ve güncellemeler almak için SMS yoluyla bilgilendirilmek istiyorum.
                    </label>
                </p>
                <?php
            });

            add_action('woocommerce_created_customer', function ($customer_id) {
                $email_optin_registration = isset($_POST['email_optin_registration']) ? 'true' : 'false';
                $sms_optin_registration = isset($_POST['sms_optin_registration']) ? 'true' : 'false';

                update_user_meta($customer_id, 'email_optin', $email_optin_registration);
                update_user_meta($customer_id, 'sms_optin', $sms_optin_registration);
            });
        endif;

        if ($dashboard)
            add_action('woocommerce_account_dashboard', function () {
                $user_id = get_current_user_id();
                $email_optin = get_user_meta($user_id, 'email_optin', true);
                $sms_optin = get_user_meta($user_id, 'sms_optin', true);
                ?>
                <p>E-posta ile içerikler ve güncellemeler almaya <b><?= ($email_optin === 'true') ? 'izin verdiniz' : 'izin vermediniz'; ?></b>.</p>
                <p>SMS ile içerikler ve güncellemeler almaya <b><?= ($sms_optin === 'true') ? 'izin verdiniz' : 'izin vermediniz'; ?></b>.</p>
                <?php
            });
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