<div><h3 style="text-align: center;">
<?php 
	_e( 'Role Based Discount and Price Adjustment', 'eh-woocommerce-pricing-discount' ); 
	global $wp_roles;
?>
</h3>
<!-- Option to hide add to cart for unregistered user-->
<h4 style="padding-left: 3%;">Unregistered User Settings:</h4>
<!-- Option to hide price for unregistered user-->
<div style="padding-left: 3%;height: 60px;">
	<label style="margin-left: 0px;width: 40%;float: left;"><?php _e( 'Hide Price', 'eh-woocommerce-pricing-discount' ); ?></label>
	<?php $checked = (( get_post_meta( $post->ID, 'product_adjustment_hide_price_unregistered', true ) ) == 'yes' )? true : false; ?>
	<input type="checkbox" style="float: left;margin-left: 0px;" name="product_adjustment_hide_price_unregistered" id="product_adjustment_hide_price_unregistered" <?php checked($checked , true ); ?> />
	<label style="float: left;margin-left:5px;"><?php _e( 'Enable', 'eh-woocommerce-pricing-discount' ); ?></label>
	<span class="description" style="width: 60%;float: right;margin-top: 6px;">
	<?php _e( 'Check to hide price for unregistered users.', 'eh-woocommerce-pricing-discount' );?></span>
</div>
<h4 style="padding-left: 3%;">User Role Specific Settings:</h4>

<!-- Option to hide price for user role-->
<div style="padding-left: 3%;overflow : auto;">
	<label for="eh_pricing_adjustment_product_price_user_role" style="margin-left: 0px;width: 40%;float: left;"><?php _e( ' Hide Price', 'eh-woocommerce-pricing-discount' );?></label>
	<select class="wc-enhanced-select" name="eh_pricing_adjustment_product_price_user_role[]" id="eh_pricing_adjustment_product_price_user_role" multiple="multiple" style="width: 50%;float: left;">
		<?php
			$hide_price_role = get_post_meta( $post->ID, 'eh_pricing_adjustment_product_price_user_role', false ) ;
			$user_roles = $wp_roles->role_names;
			foreach($user_roles as $id=>$name) {
				if( is_array(current($hide_price_role)) && in_array($id,current($hide_price_role)) ) {
					echo '<option value="'.$id.'" selected="selected">'.$name.'</option>';
				} else {
					echo '<option value="'.$id.'">'.$name.'</option>';
				}
			}
		?>
	</select>
	<span class="description" style="float: right;text-align: left;width: 60%;height: 50px;"><?php _e( ' Select the user role for which you want to hide price.', 'eh-woocommerce-pricing-discount' );?></span>
</div>
</div>
<script>
jQuery(document).ready(function(){
	jQuery('#product-type').change(function(){
		eh_pricing_discount_product_role_based_price_adjustment();
	});
	eh_pricing_discount_product_role_based_price_adjustment();
});

function eh_pricing_discount_product_role_based_price_adjustment(){
	product_type	=	jQuery('#product-type').val();
	if(product_type == 'simple'){
		jQuery('.product_price_adjustment_tab').show();
	} else {
		jQuery('.product_price_adjustment_tab').hide();
	}	
}
</script>