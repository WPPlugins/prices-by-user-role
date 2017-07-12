<?php
require_once( WP_PLUGIN_DIR . '/woocommerce/includes/admin/settings/class-wc-settings-page.php' );
class Eh_Pricing_Discount_Settings  extends WC_Settings_Page{

	public function __construct() {
		global $user_adjustment_price;
		$this->init();
		$this->id = 'eh_pricing_discount';
	}

    public function init() {
		$this->user_adjustment_price = get_option('eh_pricing_discount_price_adjustment_options');
        add_filter( 'woocommerce_settings_tabs_array', array($this,'add_settings_tab'), 50 );
		add_filter('eh_pricing_discount_general_settings',array($this,'add_settings'),30);
		add_action('woocommerce_admin_field_priceadjustmenttable',array( $this, 'pricing_admin_field_priceadjustmenttable'));
		add_action('woocommerce_admin_field_pricing_discount_manage_user_role',array( $this, 'pricing_admin_field_pricing_discount_manage_user_role'));
        add_action( 'woocommerce_update_options_eh_pricing_discount', array( $this, 'update_settings') );
		add_filter( 'woocommerce_product_data_tabs', array( $this,'add_product_tab'));
		add_action( 'woocommerce_product_data_panels', array($this,'add_price_adjustment_data_fields') );
		add_action( 'woocommerce_product_after_variable_attributes', array($this,'variation_settings_fields'), 10, 3 );
		add_action( 'woocommerce_product_after_variable_attributes_js', array($this,'variation_settings_fields'), 10, 3 );
		add_action( 'woocommerce_process_product_meta', array($this,'woo_add_custom_general_fields_save') );
		add_action( 'woocommerce_product_options_general_product_data', array($this,'add_price_extra_fields') );
		add_action( 'woocommerce_save_product_variation', array($this,'save_variable_fields'), 10, 1 );
		add_filter( 'woocommerce_sections_eh_pricing_discount',      array( $this, 'output_sections' ));
		add_filter( 'woocommerce_settings_eh_pricing_discount',      array( $this, 'output_settings' ));
    }
	
	 public function get_sections() {
        $sections = array(
            ''						=> __( 'Role Based Pricing','eh-woocommerce-pricing-discount'),
        );
        $sections = apply_filters('eh_pricing_discount_sections', $sections );
        return apply_filters( 'woocommerce_get_sections_eh_pricing_discount', $sections );
    }
	
    public static function add_settings_tab( $settings_tabs ) {
        $settings_tabs[ 'eh_pricing_discount' ] = __( 'Pricing and Discount', 'eh-woocommerce-pricing-discount' );
        return $settings_tabs;
    }

    public function output_settings() {
		global $current_section; 
		if( $current_section == '' ) {
			?>
		<div class="eh-banner updated below-h2">
  			<p class="main">
			<ul>
				<li style='color:red;'><strong>Your Business is precious. Go Premium!</li></strong>
                <li><strong>- Ability to apply the discount on both Sale and Regular price</strong></li>
				<li><strong>- Support both Simple and Variable Products. ( Basic version supports only Simple Products ).</strong></li>
				<li><strong>- Setup Price Adjustment on individual products level.</strong ></li>
				<li><strong>- Option for not to show regular price instead of striking it out.</strong ></li>
				<li><strong>- Hide Add to Cart button at individual products level.</strong ></li>
				<li><strong>- Supports both Markups and Discounts.</strong ></li>
				<li><strong>- Add custom user roles like "premium user", "whole seller", etc.</strong ></li>
				<li><strong>- Option to enable role based Tax Inclusion & Price Suffix.</strong ></li>
				<li><strong>- Premium Support:</strong> Faster and time bound response for support requests.</li>
			</ul>
			</p>
			<p><a href="http://www.xadapter.com/product/prices-by-user-role-for-woocommerce/" target="_blank" class="button button-primary">Upgrade to Premium Version</a> <a href="http://pricingbyroledemo.extensionhawk.com/wp-admin/admin.php?page=wc-settings&tab=eh_pricing_discount" target="_blank" class="button">Live Demo</a></p>
		</div>
		<style>
		.eh-banner img {
			float: right;
			margin-left: 1em;
			padding: 15px 0
		}
		</style>
		<?php
			$settings = $this->get_settings( $current_section );
			WC_Admin_Settings::output_fields( $settings );
			
		}
    }
	
	//function to generate price adjustment table
	public function pricing_admin_field_pricing_discount_manage_user_role($settings) {
		include( 'html-eh-price-adjustment-manage-user-role.php' );
	}
	
    public function update_settings( $current_section ) {
		global $current_section; 
		if( $current_section == '') {
			$options = $this->get_settings();
			woocommerce_update_options( $options );
			$this->user_adjustment_price = get_option('eh_pricing_discount_price_adjustment_options');
		}
    }

	//function to add settings
	public function add_settings($settings)
	{
		$settings['price_adjustment_options'] = array(
			'type'            	=> 'priceadjustmenttable',
			'id'				=> 'eh_pricing_discount_price_adjustment_options',
		);
		return $settings;
	}
	
    public function get_settings() {
		global $current_section;
		global $wp_roles;
		$pricing_options = array(
			'Discount'      		=> __( 'Discount', 'eh-woocommerce-pricing-discount' ),
		);
		$user_roles = $wp_roles->role_names;
        $settings = array(
            'general_settings_section_title'			=> array(
                'name'				=> __( 'Role Based Pricing and Discount (BASIC)', 'eh-woocommerce-pricing-discount' ),
                'type'				=> 'title',
                'desc'				=> '',
                'id'				=> 'eh_pricing_discount_section_title',
            ),
			'general_settings_section_title_end' => array(
				'type'			=> 'sectionend',
				'id'			=> 'eh_pricing_discount_section_title'
            ),
			'eh_pricing_discount_unregistered_title' => array(
				'title' => __('Unregistered User Settings:', 'eh-woocommerce-pricing-discount'),
				'type' => 'title',
				'description' => '',
				'id'=> 'eh_pricing_discount_unregistered'
			),
			'cart_unregistered_user'		=> array(
				'title'				=> __( 'Remove Add to Cart', 'eh-woocommerce-pricing-discount' ),
				'type'				=> 'checkbox',
				'desc'				=> __( 'Enable', 'eh-woocommerce-pricing-discount' ),
				'css'				=> 'width:100%',
				'id'				=> 'eh_pricing_discount_cart_unregistered_user',
				'desc_tip' => __( 'Check this option to remove add to cart option for unregistered users.', 'eh-woocommerce-pricing-discount' ),
			),
			'cart_unregistered_user_text'		=> array(
				'title'				=> __( 'Placeholder Text', 'eh-woocommerce-pricing-discount' ),
				'type'				=> 'text',
				'desc'				=> __( "Enter the text you want to show when add to cart option is removed. Leave it empty if you don't want to show any placeholder text.", 'eh-woocommerce-pricing-discount' ),
				'css'				=> 'width:350px',
				'id'				=> 'eh_pricing_discount_cart_unregistered_user_text',
				'desc_tip' => true
			),
			'price_unregistered_user'		=> array(
				'title'				=> __( 'Hide Price', 'eh-woocommerce-pricing-discount' ),
				'type'				=> 'checkbox',
				'desc'				=> __( 'Enable', 'eh-woocommerce-pricing-discount' ),
				'css'				=> 'width:100%',
				'id'				=> 'eh_pricing_discount_price_unregistered_user',
				'desc_tip' => __( 'Check this option to hide product price for unregistered users.', 'eh-woocommerce-pricing-discount' ),
			),
			'price_unregistered_user_text'		=> array(
				'title'				=> __( 'Placeholder Text', 'eh-woocommerce-pricing-discount' ),
				'type'				=> 'text',
				'desc'				=> __( "Enter the text you want to show when price is removed. Leave it empty if you don't want to show any placeholder text.", 'eh-woocommerce-pricing-discount' ),
				'css'				=> 'width:350px',
				'id'				=> 'eh_pricing_discount_price_unregistered_user_text',
				'desc_tip' => true
			),
			'eh_pricing_discount_unregistered_title_end' => array(
				'type'			=> 'sectionend',
				'id'			=> 'eh_pricing_discount_unregistered'
            ),
			'eh_pricing_discount_user_role_title' => array(
				'title' => __('User Role Specific Settings:', 'eh-woocommerce-pricing-discount'),
				'type' => 'title',
				'description' => '',
				'id'=> 'eh_pricing_discount_user_role'
			),
			'cart_user_role'		=> array(
				'title'				=> __( 'Hide Add to Cart', 'eh-woocommerce-pricing-discount' ),
				'type'				=> 'multiselect',
				'desc'				=> __( 'Select the user role for which you want to hide add to cart option.', 'eh-woocommerce-pricing-discount' ),
				'class'				=> 'chosen_select',
				'id'				=> 'eh_pricing_discount_cart_user_role',
				'options'         	=> $user_roles,
				'desc_tip' => true
			),
			'cart_user_role_text'		=> array(
				'title'				=> __( 'Placeholder Text', 'eh-woocommerce-pricing-discount' ),
				'type'				=> 'text',
				'desc'				=> __( "Enter the text you want to show when add to cart is removed. Leave it empty if you don't want to show any placeholder text", 'eh-woocommerce-pricing-discount' ),
				'css'				=> 'width:350px',
				'id'				=> 'eh_pricing_discount_cart_user_role_text',
				'desc_tip' => true
			),
			'price_user_role'		=> array(
				'title'				=> __( 'Hide Price', 'eh-woocommerce-pricing-discount' ),
				'type'				=> 'multiselect',
				'desc'				=> __( 'Select the user role for which you want to hide product price.', 'eh-woocommerce-pricing-discount' ),
				'class'				=> 'chosen_select',
				'id'				=> 'eh_pricing_discount_price_user_role',
				'options'         	=> $user_roles,
				'desc_tip' => true
			),
			'price_user_role_text'		=> array(
				'title'				=> __( 'Placeholder Text', 'eh-woocommerce-pricing-discount' ),
				'type'				=> 'text',
				'desc'				=> __( "Enter the text you want to show when price is removed. Leave it empty if you don't want to show any placeholder text", 'eh-woocommerce-pricing-discount' ),
				'css'				=> 'width:350px',
				'id'				=> 'eh_pricing_discount_price_user_role_text',
				'desc_tip' => true
			),
			'product_price_user_role'		=> array(
				'title'				=> __( 'Individual Product Adjustment', 'eh-woocommerce-pricing-discount' ),
				'type'				=> 'multiselect',
				'desc'				=> __( 'Select the user role for which you want to have individual product level price adjustment.', 'eh-woocommerce-pricing-discount' ),
				'class'				=> 'chosen_select',
				'id'				=> 'eh_pricing_discount_product_price_user_role',
				'options'         	=> $user_roles,
				'desc_tip' => true
			),
			'eh_pricing_discount_user_role_title_end' => array(
				'type'			=> 'sectionend',
				'id'			=> 'eh_pricing_discount_user_role'
            ),
			'eh_pricing_discount_adjustment_title' => array(
				'title' => __('Adjustment Settings:', 'eh-woocommerce-pricing-discount'),
				'type' => 'title',
				'description' => '',
				'id'=> 'eh_pricing_discount_adjustment'
			),
			'product_price_markup_discount'		=> array(
				'title'				=> __( ' Adjustment Type ', 'eh-woocommerce-pricing-discount' ),
				'type'				=> 'select',
				'desc'				=> __( 'Select the type of adjustment you want to have. This adjustment is applicable to individual product level price adjustment also.', 'eh-woocommerce-pricing-discount' ),
				'default'       	=> 'Discount',
				'id'				=> 'eh_pricing_product_price_markup_discount',
				'options'         	=> $pricing_options,
				'desc_tip' => true
			),
			'eh_pricing_discount_adjustment_title_end' => array(
				'type'			=> 'sectionend',
				'id'			=> 'eh_pricing_discount_adjustment'
            ),
        );
        return apply_filters( 'eh_pricing_discount_general_settings', $settings );
    }
	
	//function to generate price adjustment table
	public function pricing_admin_field_priceadjustmenttable($settings) {
		include( 'html-eh-price-adjustment.php' );
	}
	
	//function to add a prodcut tab in product page
	public function add_product_tab($product_data_tabs ) {
		global $product;
		$product_data_tabs['product_price_adjustment'] = array(
			'label' => __( 'Role Based Pricing', 'eh-woocommerce-pricing-discount' ),
			'target' => 'product_price_adjustment_data',
			'id'	=> 'eh_pricing_product_role_based_settings',
			'class' => Array('eh_pricing_settings'),
		);
		return $product_data_tabs;
	}
	

	public function add_price_adjustment_data_fields() {
		global $woocommerce, $post;
		?>
		<!-- id below must match target registered in above add_my_custom_product_data_tab function -->
		<div id="product_price_adjustment_data" class="panel woocommerce_options_panel hidden">
			<?php include( 'html-eh-product-price-adjustment.php' ); ?>
		</div>
		<?php
	}
	
	function add_price_extra_fields() {
		global $woocommerce, $post;
		$product = new WC_Product( get_the_ID() );
		$user_roles = get_option('eh_pricing_discount_product_price_user_role');
		if(is_array($user_roles)) {
			echo '<div id="general_role_based_price" style="padding: 3%; >';
			include( 'html-eh-product-role-based-price.php' );
			echo '</div>';
		}else{
			echo '<div class="clearfix"></div>';
		}
	}
	
	public function woo_add_custom_general_fields_save( $post_id )
	{
		//to update product price for unregistered users
		$woocommerce_adjustment_field = (isset($_POST['product_adjustment_hide_price_unregistered']) && ($_POST['product_adjustment_hide_price_unregistered'] == 'on')) ? 'yes' : 'no' ;
		if( !empty( $woocommerce_adjustment_field ) ){
			update_post_meta( $post_id, 'product_adjustment_hide_price_unregistered', $woocommerce_adjustment_field );	
		}
		
		//to update product role based hide price
		$woocommerce_adjustment_field = !empty($_POST['eh_pricing_adjustment_product_price_user_role']) ? $_POST['eh_pricing_adjustment_product_price_user_role'] :'';
		if( !empty( $woocommerce_adjustment_field ) ){
			update_post_meta( $post_id, 'eh_pricing_adjustment_product_price_user_role', $woocommerce_adjustment_field );	
		}
		
		//to update the product role based price
		$woocommerce_price_field = $_POST['product_role_based_price'];
		if( !empty( $woocommerce_price_field ) ){
			update_post_meta( $post_id, 'product_role_based_price', $woocommerce_price_field );
		}
	}
	
	public function variation_settings_fields( $loop, $variation_data,$variation )
	{
		include( 'html-eh-variation-product-role-based-price.php' );
	}
	
	public function save_variable_fields() {
		if (isset( $_POST['variable_sku'] ) ) {
			$variable_sku          = $_POST['variable_sku'];
			$variable_post_id      = $_POST['variable_post_id'];
			$role_based_price = $_POST['product_role_based_price'];
			for ( $i = 0; $i <= (sizeof( $variable_sku )); $i++ ) {
				$variation_id = (int) $variable_post_id[$i];
				if ( isset( $role_based_price[$i] ) ) {
					update_post_meta( $variation_id, 'product_role_based_price', $role_based_price[$i] );
				}
			}
		}
	}
}

new Eh_Pricing_Discount_Settings();
