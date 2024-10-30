<?php

class Moceansms_Multivendor_Setting {
	public function __construct() {
		add_filter( 'moceansms_setting_section', array( $this, 'set_multivendor_setting_section' ) );
		add_filter( 'moceansms_setting_fields', array( $this, 'set_multivendor_setting_field' ) );
	}

	public function set_multivendor_setting_section( $sections ) {
		$sections[] = array(
			'id'    => 'multivendor_setting',
			'title' => __( 'Multivendor Settings', 'moceansms-woocoommerce' )
		);

		return $sections;
	}

	public function set_multivendor_setting_field( $setting_fields ) {
		$setting_fields['multivendor_setting'] = array(
			array(
				'name'    => 'moceansms_multivendor_vendor_send_sms',
				'label'   => __( 'Enable Vendor SMS Notifications', 'moceansms-woocoommerce' ),
				'desc'    => ' ' . __( 'If checked then enable sms notification to vendor for new order', 'moceansms-woocoommerce' ),
				'type'    => 'checkbox',
				'default' => 'on',
			),
			array(
				'name'    => 'moceansms_multivendor_vendor_sms_template',
				'label'   => __( 'Vendor SMS Message', 'moceansms-woocoommerce' ),
				'desc'    => __( 'Customize your SMS with these tags: [shop_name], [vendor_shop_name], [order_id], [order_currency], [order_amount], [order_status], [order_product], [payment_method], [billing_first_name], [billing_last_name], [billing_phone], [billing_email], [billing_company], [billing_address], [billing_country], [billing_city], [billing_state], [billing_postcode].', 'moceansms-woocoommerce' ),
				'type'    => 'textarea',
				'rows'    => '8',
				'cols'    => '500',
				'css'     => 'min-width:350px;',
				'default' => __( '[shop_name] : You have a new order with order ID [order_id] and order amount [order_currency] [order_amount]. The order is now [order_status].', 'moceansms-woocoommerce' )
			)
		);

		return $setting_fields;
	}
}

?>
