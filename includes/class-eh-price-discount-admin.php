<?php
class Class_Eh_Price_Discount_Admin {
	
	public function __construct() {
		add_action( 'woocommerce_after_shop_loop_item_title', array($this,'add_to_cart_text_content') );//function to remove add to cart text content at shop
		add_action('woocommerce_after_single_product_summary', array($this,'add_to_cart_single_product_text_content'));//function to remove add to cart text content at product
		add_action('woocommerce_before_shop_loop_item_title', array($this,'remove_shop_add_to_cart_option'));//function to remove add to cart at shop
		add_action('woocommerce_before_single_product_summary', array($this,'remove_product_add_to_cart_option'));//function to remove add to cart at product
		if(WC()->version < '2.7.0'){
			add_filter( 'woocommerce_get_regular_price', array( $this, 'eh_get_regular_price') , 99, 2 );//function to modify product regular price
			add_filter( 'woocommerce_get_sale_price', array( $this, 'get_selling_price') , 99, 2 );//function to modify product sale price
			add_filter('woocommerce_get_price', array(&$this,'get_price'), 99, 2);//function to modify product price at all level
		}
		else{
			add_filter( 'woocommerce_product_get_regular_price', array( $this, 'eh_get_regular_price') , 99, 2 );//function to modify product regular price
			add_filter( 'woocommerce_product_get_sale_price', array( $this, 'get_selling_price') , 99, 2 );//function to modify product sale price
			add_filter('woocommerce_product_get_price', array(&$this,'get_price'), 99, 2);//function to modify product price at all level
		}
		add_filter( 'woocommerce_get_price_html',array( &$this,'get_price_html' ),1,2);
		$this->init_fields();
	}
	
	public function init_fields()
	{
		$this->hide_regular_price =  get_option('eh_pricing_discount_hide_regular_price') == 'yes' ? false : true;
		$this->role_price_adjustment = get_option('eh_pricing_discount_price_adjustment_options');
		$this->current_user_role = $this->get_priority_user_role(wp_get_current_user()->roles);
		$this->remove_tax_user_role = get_option('eh_pricing_discount_remove_tax_user_role');
	}
	
	//function to modify product price at all level
	public function get_price ($price, $product) 
	{
		$wcrbp_price = $price;
		if(WC()->version < '2.7.0'){
			$temp_data = $product->product_type;
			$temp_regular_price = $product->regular_price;
		}else{
			$temp_data = $product->get_type();
			$temp_regular_price = $product->get_regular_price();
		}
		if($temp_data === 'simple') {
			$sale_price = $product->get_sale_price();
			$regular_price = ( $this->eh_get_regular_price( $price, $product ) != '') ? $this->eh_get_regular_price( $price, $product ) : $temp_regular_price;
			$wcrbp_price = ( $sale_price != '' && $sale_price > 0 )? $sale_price : $regular_price;
			$wcrbp_price = wc_format_decimal($wcrbp_price);
			$wcrbp_price =  $this->modify_shop_product_price($wcrbp_price, $product);
			//to remove tax based on user role
			if(is_array($this->remove_tax_user_role) && in_array($this->current_user_role,$this->remove_tax_user_role)) {
				if(WC()->version < '2.7.0'){
					remove_filter('woocommerce_get_price', array(&$this,'get_price'), 99);
					switch ($temp_data) {
						case 'simple':
						$product->tax_status = 'none';
						if ('yes' === get_option( 'woocommerce_prices_include_tax' )) {
							$wcrbp_price = $this->get_price_excluding_tax( $qty = 1, $wcrbp_price, $product);
						}
						break;
					}
					add_filter('woocommerce_get_price', array(&$this,'get_price'), 99, 2);

				}else{
					remove_filter('woocommerce_product_get_price', array(&$this,'get_price'), 99);
					switch ($temp_data) {
						case 'simple':
						$product->tax_status = 'none';
						if ('yes' === get_option( 'woocommerce_prices_include_tax' )) {
							$wcrbp_price = $this->get_price_excluding_tax( $qty = 1, $wcrbp_price, $product);
						}
						break;
					}
					add_filter('woocommerce_product_get_price', array(&$this,'get_price'), 99, 2);
					
				}
			}
		}
		return $wcrbp_price;
	}
	
	//function to modify product regular price
	public function eh_get_regular_price($price, $product) {

		if(WC()->version < '2.7.0'){
			remove_filter('woocommerce_get_price', array(&$this,'get_price'), 99, 2);
			remove_filter('woocommerce_get_regular_price',array($this,'eh_get_regular_price'),99,2);

			$temp_data = $product->product_type;
			$temp_post_id = $product->post->ID;
		}else{
			remove_filter('woocommerce_product_get_price', array(&$this,'get_price'), 99, 2);
			remove_filter('woocommerce_product_get_regular_price',array($this,'eh_get_regular_price'),99,2);

			$temp_data = $product->get_type();
			$temp_post_id = $product->get_ID();
		}
		if($temp_data === 'simple') {
			$price = wc_format_decimal($this->calculate_regular_price($price, $temp_post_id , $temp_data,$product));
		}
		if( 'Discount' == get_option('eh_pricing_product_price_markup_discount') && ($product->get_sale_price() != '' ) ) {
			$price = $product->get_sale_price();
		}

		if(WC()->version < '2.7.0'){
			add_filter('woocommerce_get_regular_price',array($this,'eh_get_regular_price'),99,2);
			add_filter('woocommerce_get_price', array(&$this,'get_price'), 99, 2);

		}
		else{
			add_filter('woocommerce_product_get_regular_price',array($this,'eh_get_regular_price'),99,2);
			add_filter('woocommerce_product_get_price', array(&$this,'get_price'), 99, 2);
			
		}
		return $price;
	}

	//function to modify product sale price
	public function get_selling_price($price, $product) {
		if(WC()->version < '2.7.0'){
			remove_filter('woocommerce_get_price', array(&$this,'get_price'), 99, 2);
			remove_filter('woocommerce_get_sale_price',array($this,'get_selling_price'),99,2);

			$temp_data = $product->product_type;
			$temp_post_id = $product->post->ID;
			$temp_regular_price = $product->regular_price;
		}else{
			remove_filter('woocommerce_product_get_price', array(&$this,'get_price'), 99, 2);
			remove_filter('woocommerce_product_get_sale_price',array($this,'get_selling_price'),99,2);

			$temp_data = $product->get_type();
			$temp_post_id = $product->get_ID();
			$temp_regular_price = $product->get_regular_price();
		}
		if ($price == '') {
			$price = $temp_regular_price;
		}
		if($temp_data == 'simple') {
			$price = wc_format_decimal($this->add_user_shop_general_price_settings( $price, $temp_post_id ));
		}
		if(WC()->version < '2.7.0'){
			add_filter('woocommerce_get_price', array(&$this,'get_price'), 99, 2);
			add_filter('woocommerce_get_sale_price',array($this,'get_selling_price'),99,2);
		}else{
			add_filter('woocommerce_product_get_sale_price',array($this,'get_selling_price'),99,2);
			add_filter('woocommerce_product_get_price', array(&$this,'get_price'), 99, 2);
			
		}
		return $price;
	}

	//function to calculate regular price
	public function calculate_regular_price($price, $id, $type, $product) {
		global $product;
		if(($type === 'simple' ) && $product) {
			if((is_user_logged_in ())) {
				$settings_price_adjustment = get_option('eh_pricing_discount_product_price_user_role');
				$product_level_price = get_post_meta( $id, 'product_role_based_price', false );
				$product_user_price = empty($product_level_price) ? array() : $product_level_price;
				$new_price = 0;
				if( is_array($this->role_price_adjustment) && in_array($this->current_user_role,$this->role_price_adjustment) &&($this->role_price_adjustment[$this->current_user_role]['role_price'] == 'on')) {
					if( is_array(current($product_user_price)) && key_exists($this->current_user_role ,current($product_user_price)) && key_exists('role_price',current($product_user_price)[$this->current_user_role]) && (current($product_user_price)[$this->current_user_role]['role_price'] !='') ) {
						$price = current($product_user_price)[$this->current_user_role]['role_price'];
					}
				}
			}
		}
		return $price;
	}
	
	//function to remove add to cart option for guest user in shop
	public function remove_shop_add_to_cart_option() {
		global $product;
		if(WC()->version < '2.7.0'){
			$temp_data = $product->product_type;
		}else{
			$temp_data = $product->get_type();
		}
		if($temp_data === 'simple' ) {
			if(('yes' == get_option('eh_pricing_discount_cart_unregistered_user'))) {
				if(!(is_user_logged_in ())) {
					remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
				}
			}
			$this->remove_shop_add_to_cart_user();
		}
	}
	
	//function to replace add to cart with text content for unregistered user and user role in shop
	function add_to_cart_text_content()
	{
		global $product;
		if(WC()->version < '2.7.0'){
			$temp_data = $product->product_type;
			$temp_post_id = $product->post->ID;
		}else{
			$temp_data = $product->get_type();
			$temp_post_id = $product->get_ID();
		}
		if($temp_data === 'simple' ) {
			if(('yes' == get_option('eh_pricing_discount_cart_unregistered_user'))) {
				if(!(is_user_logged_in ())) {
					$unregistered_user_cart_text = get_option('eh_pricing_discount_cart_unregistered_user_text');
					if( $unregistered_user_cart_text != '') {
						_e($unregistered_user_cart_text,'eh-woocommerce-pricing-discount');
					}
					remove_action( 'woocommerce_after_shop_loop_item', 'add_to_cart_text_content', 10 );
				}
			}
			if((is_user_logged_in ())) {
				$remove_settings_cart_roles = get_option('eh_pricing_discount_cart_user_role');
				$product_cart_roles = get_post_meta( $temp_post_id, 'eh_pricing_adjustment_product_addtocart_user_role', false);
				$remove_product_cart_roles = empty($product_cart_roles) ? array() : $product_cart_roles;
				$user_role_cart_text = get_option('eh_pricing_discount_cart_user_role_text');
				if(((is_array( $remove_settings_cart_roles ) && in_array($this->current_user_role, $remove_settings_cart_roles ))) && 
					$user_role_cart_text !='' ) {
					_e($user_role_cart_text,'eh-woocommerce-pricing-discount');
				remove_action( 'woocommerce_after_shop_loop_item', 'add_to_cart_text_content', 10 );
			}
		}
	}
}

function add_to_cart_single_product_text_content()
{
	global $product;
	if(WC()->version < '2.7.0'){
		$temp_data = $product->product_type;
		$temp_post_id = $product->post->ID;
	}else{
		$temp_data = $product->get_type();
		$temp_post_id = $product->get_ID();
	}
	if($temp_data === 'simple') {	
		if(('yes' == get_option('eh_pricing_discount_cart_unregistered_user'))) {
			if(!(is_user_logged_in ())) {
				$unregistered_user_cart_text = get_option('eh_pricing_discount_cart_unregistered_user_text');
				if( $unregistered_user_cart_text != '') {
					_e($unregistered_user_cart_text,'eh-woocommerce-pricing-discount');
				}
				remove_action( 'woocommerce_after_single_product_summary', 'add_to_cart_text_content', 10 );
			}
		}
		if((is_user_logged_in ())) {
			$remove_settings_cart_roles = get_option('eh_pricing_discount_cart_user_role');
			$product_cart_roles = get_post_meta( $temp_post_id, 'eh_pricing_adjustment_product_addtocart_user_role', false );
			$remove_product_cart_roles = empty($product_cart_roles) ? array() : $product_cart_roles;
			$user_role_cart_text = get_option('eh_pricing_discount_cart_user_role_text');
			if(((is_array( $remove_settings_cart_roles ) && in_array($this->current_user_role, $remove_settings_cart_roles ))) && 
				$user_role_cart_text !='' ) {
				_e($user_role_cart_text,'eh-woocommerce-pricing-discount');
			remove_action( 'woocommerce_after_single_product_summary', 'add_to_cart_text_content', 10 );
		}
	}
}
}

	//function to remove add to cart option for selected user roles in shop
public function remove_shop_add_to_cart_user() {
	global $product;
	if(WC()->version < '2.7.0'){
		$temp_data = $product->product_type;
	}else{
		$temp_data = $product->get_type();
	}
	if($temp_data === 'simple') {
		if((is_user_logged_in ())) {
			$remove_cart_roles = get_option('eh_pricing_discount_cart_user_role');
			if((is_array( $remove_cart_roles ) && in_array($this->current_user_role,$remove_cart_roles))) {
				remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
			}
		}
	}
}

	//function to remove add to cart option for guest user in individual product page
public function remove_product_add_to_cart_option() {
	global $product;
	if(WC()->version < '2.7.0'){
		$temp_data = $product->product_type;
	}else{
		$temp_data = $product->get_type();
	}
	if(($temp_data === 'simple')) {
		if(!(is_user_logged_in ())) {
			if('yes' == get_option('eh_pricing_discount_cart_unregistered_user')) {
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
			}
		} else {
			$this->remove_product_add_to_cart_user();
		}
	}
}

	//function to remove add to cart option for selected user roles in individual product page
public function remove_product_add_to_cart_user() {
	global $product;
	if(WC()->version < '2.7.0'){
		$temp_data = $product->product_type;
	}else{
		$temp_data = $product->get_type();
	}
	if(($temp_data === 'simple')) {
		$remove_cart_roles = get_option('eh_pricing_discount_cart_user_role');
		if((is_array($remove_cart_roles) && in_array($this->current_user_role,$remove_cart_roles))) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		}
	}
}

public function modify_shop_product_price($price, $product)	{
	$remove_add_to_cart = false;
	if(WC()->version < '2.7.0'){
		$temp_data = $product->product_type;
		$temp_post_id = $product->post->ID;
	}else{
		$temp_data = $product->get_type();
		$temp_post_id = $product->get_ID();
	}
	$unregistered_user_price_text = get_option('eh_pricing_discount_price_unregistered_user_text');
		//to remove price for guest user based on general or product settings
	if( ( 'yes' == get_option('eh_pricing_discount_price_unregistered_user')) || ( 'yes' == ( get_post_meta( $temp_post_id, 'product_adjustment_hide_price_unregistered', true ) ) ) ){
		if(!(is_user_logged_in ())) {
			if( $unregistered_user_price_text != '') {
				$price = $unregistered_user_price_text;
			} else {
				$price = '';
			}
			$remove_add_to_cart = true;
		}
	}

		//to remove price for specific selected user roles
	if((is_user_logged_in ())) {
		$remove_settings_price_roles = get_option('eh_pricing_discount_price_user_role');
		$remove_price_roles = get_post_meta( $temp_post_id, 'eh_pricing_adjustment_product_price_user_role', false);
		$remove_product_price_roles = empty($remove_price_roles) ? array() : $remove_price_roles;
		$user_role_cart_text = get_option('eh_pricing_discount_price_user_role_text');

		if( is_array( $remove_settings_price_roles ) ) {
			if( ( in_array($this->current_user_role,$remove_settings_price_roles ) ) ) {
				if( $user_role_cart_text != '') {
					$price = $user_role_cart_text;
				} else {
					$price = '';
				}
				$remove_add_to_cart = true;
			}
		}
		if(is_array(current($remove_product_price_roles)) && (in_array($this->current_user_role,current($remove_product_price_roles)))) {
			if( $user_role_cart_text != '') {
				$price = $user_role_cart_text;
			} else {
				$price = '';
			}
			$remove_add_to_cart = true;
		}
	}
	return $price;
}

	//function to add adjustment for price over price from settings
public function add_user_shop_general_price_settings( $price, $id ){
	global $product;
	if((is_user_logged_in ())) {
		$separator = stripslashes( get_option( 'woocommerce_price_decimal_sep' ) );
		$decimal_separator = $separator ? $separator : '.';
		$user_price = get_post_meta($id, 'product_role_based_price', false);
		$product_user_price = empty($user_price) ? array() : $user_price;
		$new_price = 0;
		if(is_array($this->role_price_adjustment) && key_exists($this->current_user_role,$this->role_price_adjustment)) {
			if( is_array(current($product_user_price)) && key_exists('role_price',$this->role_price_adjustment[$this->current_user_role])) {
				if(key_exists($this->current_user_role ,current($product_user_price)) && (current($product_user_price)[$this->current_user_role]['role_price'] !='') ) {
					$price = current($product_user_price)[$this->current_user_role]['role_price'];
				}
			}
			$price = str_replace($decimal_separator, '.', $price);
			if($this->role_price_adjustment[$this->current_user_role]['adjustment_price'] !='') {
				$adjustment_price = $this->role_price_adjustment[$this->current_user_role]['adjustment_price'];
				$adjustment_price =  str_replace($decimal_separator, '.', $adjustment_price);
				$new_price += floatval($adjustment_price);
			}
			$new_price = str_replace($decimal_separator, '.', $new_price);
			if($this->role_price_adjustment[$this->current_user_role]['adjustment_percent'] !='') {
				$adjustment_percent = $this->role_price_adjustment[$this->current_user_role]['adjustment_percent'];
				$adjustment_percent = str_replace($decimal_separator, '.', $adjustment_percent);
				$adjustment_percent_price = round( $price * floatval($adjustment_percent)) / 100;
				$new_price = ( $new_price + $adjustment_percent_price );
			}
			if( 'Discount' != get_option('eh_pricing_product_price_markup_discount')) {
				$price += $new_price;
			} else {
				$price -= $new_price;
			}
		}
	}
	return $price;
}

public function get_price_html( $price = '',$product ) {
	if(WC()->version < '2.7.0'){
		$temp_data = $product->product_type;
		$temp_post_id = $product->post->ID;
	}else{
		$temp_data = $product->get_type();
		$temp_post_id = $product->get_ID();
	}

	if( $temp_data === 'simple' ) {
		if( strip_tags( $price ) == 'Free!' || $price =='' ) {
			if(!(is_user_logged_in ())) {
				$user_role_price_text = get_option('eh_pricing_discount_price_unregistered_user_text');
				if( ('yes' == get_option('eh_pricing_discount_price_unregistered_user') ) || ( 'yes' == ( get_post_meta( $temp_post_id, 'product_adjustment_hide_price_unregistered', true ) ) ) ) {
					$price = '<span class="amount">' . __( $user_role_price_text , 'woocommerce' ) . '</span>';
					remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
					remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
				} else {
					$price = '';
					remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
					remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
				}
			} else {
				$user_role_price_text = get_option('eh_pricing_discount_price_user_role_text');
				$remove_settings_price_roles = ( get_option('eh_pricing_discount_price_user_role') != '') ? get_option('eh_pricing_discount_price_user_role') : '';
				if (is_array($remove_settings_price_roles) && (in_array($this->current_user_role,$remove_settings_price_roles))) {
					$price = '<span class="amount">' . __( $user_role_price_text , 'woocommerce' ) . '</span>';
					remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
					remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
				} else {
					$price = '';
					remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
					remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
				}
			}
		} else {

			if(!(is_user_logged_in ())) {
				if(!('yes' == get_option('eh_pricing_discount_cart_unregistered_user'))) {
					add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
				}
			} else {
				$remove_cart_roles = get_option('eh_pricing_discount_cart_user_role');
				if(is_array( $remove_cart_roles ) && !(in_array($this->current_user_role,$remove_cart_roles))) {
					add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
				}
			}
		}
	} else {
		if( $temp_data == 'grouped') {
			if((strip_tags($price)) == 'Free!') {
				$price = '';
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
				remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
			}
		} else {
			add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
		}
	}
	return $price;
}

	//function to determine the user role to use in case of multiple user roles for one user
public function get_priority_user_role($user_roles)
{
	if(is_user_logged_in ()) {
		if(!empty($this->role_price_adjustment)) {
			foreach ($this->role_price_adjustment as $id => $value) {
				if(in_array($id,$user_roles)) {
					return $id;
				}
			}
		} else {
			return $user_roles[0];
		}
	}
}

	//function to calculate price excluding tax
public function get_price_excluding_tax( $qty = 1, $price = '', $product) {

	if ( $price === '' ) {
		$price = $product->get_price();
	}
	if ('yes' === get_option( 'woocommerce_prices_include_tax' )) {
		$tax_rates  = WC_Tax::get_base_tax_rates( $product->tax_class );
		$taxes      = WC_Tax::calc_tax( $price * $qty, $tax_rates, true );
		$price      = WC_Tax::round( $price * $qty - array_sum( $taxes ) );
	} else {
		$price = $price * $qty;
	}

	return apply_filters( 'woocommerce_get_price_excluding_tax', $price, $qty, $this );
}
}
new Class_Eh_Price_Discount_Admin();

