<tr valign="top" >
	<td class="forminp" colspan="2" style="padding-left:0px">
		<p class="description"> 
			<?php
				_e('Drag and drop to set priority for User Roles. If a single User has multiple User Roles assigned, then the User Role with the highest priority will be chosen.','eh-woocommerce-pricing-discount');
				global $wp_roles;
			?>
		</p>
		<table class="price_adjustment widefat" id="eh_pricing_discount_price_adjustment_options">
			<thead>
				<th class="sort">&nbsp;</th>
				<th><?php _e( 'User Role', 'eh-woocommerce-pricing-discount' ); ?></th>
				<th style="text-align:center;"><?php echo sprintf( __( 'Price Adjustment (%s)', 'eh-woocommerce-pricing-discount' ), get_woocommerce_currency_symbol() ); ?></th>
				<th style="text-align:center;"><?php _e( 'Price Adjustment (%)', 'eh-woocommerce-pricing-discount' ); ?></th>
				<th style="text-align:center;"><?php _e( 'Use Role Based Price', 'eh-woocommerce-pricing-discount' ); ?></th>
			</thead>
			<tbody>
			
				<?php
					$this->price_table = array();
					$i=0;
					$user_adjustment_price = get_option('eh_pricing_discount_price_adjustment_options');
					$wordpress_roles = $wp_roles->role_names;
					if(empty($user_adjustment_price))
					{
						foreach ( $wordpress_roles as $id => $value ) {
							$this->price_table[$i]['id'] = $id;
							$this->price_table[$i]['name'] = $value;
							$this->price_table[$i]['adjustment_price'] = '';
							$this->price_table[$i]['adjustment_percent'] = '';
							$this->price_table[$i]['role_price'] = '';
							$i++;
						}
					} else {
						foreach ( $user_adjustment_price as $id => $value ) {
							if(is_array($wordpress_roles) && key_exists($id,$wordpress_roles))
							{
								$this->price_table[$i]['id'] = $id;
								$this->price_table[$i]['name'] = $wordpress_roles[$id];
								$this->price_table[$i]['adjustment_price'] = $this->user_adjustment_price[$id]['adjustment_price'];
								$this->price_table[$i]['adjustment_percent'] = $this->user_adjustment_price[$id]['adjustment_percent'];
								if(key_exists('role_price',$user_adjustment_price[$id])) {
									$this->price_table[$i]['role_price'] = $user_adjustment_price[$id]['role_price'];
								} else {
									$this->price_table[$i]['role_price'] = '';
								}
							}
							$i++;
							unset($wordpress_roles[$id]);
						}
						if(!empty($wordpress_roles))
						{
							foreach ( $wordpress_roles as $id => $value ) {
								$this->price_table[$i]['id'] = $id;
								$this->price_table[$i]['name'] = $value;
								$this->price_table[$i]['adjustment_price'] = '';
								$this->price_table[$i]['adjustment_percent'] = '';
								$this->price_table[$i]['role_price'] = '';
								$i++;
							}
						}
					}
					foreach ( $this->price_table as $key => $value ) {
						?>
						<tr>
							<td class="sort">
								<input type="hidden" class="order" name="eh_pricing_discount_price_adjustment_options[<?php echo $this->price_table[ $key ]['id'] ?>]" value="<?php echo $this->price_table[ $key ]['id']; ?>" />
							</td>
							<td>
								<label name="eh_pricing_discount_price_adjustment_options[<?php echo $this->price_table[ $key ]['name']; ?>][name]" size="35" ><?php echo isset( $this->price_table[ $key ]['name'] ) ? $this->price_table[ $key ]['name'] : ''; ?></label>
							</td>
							<td style="text-align:center;">
								<?php echo get_woocommerce_currency_symbol(); ?><input type="text" name="eh_pricing_discount_price_adjustment_options[<?php echo $this->price_table[ $key ]['id']; ?>][adjustment_price]" placeholder="N/A" value="<?php echo isset( $this->price_table[$key]['adjustment_price'] ) ? $this->price_table[$key]['adjustment_price'] : ''; ?>" size="4" />
							</td>
							<td style="text-align:center;">
								<input type="text" name="eh_pricing_discount_price_adjustment_options[<?php echo $this->price_table[ $key ]['id']; ?>][adjustment_percent]" placeholder="N/A" value="<?php echo isset( $this->price_table[$key]['adjustment_percent'] ) ? $this->price_table[$key]['adjustment_percent'] : ''; ?>" size="4" />%
							</td>
							<td style="text-align:center;">
								<label>
									<?php $checked = (! empty( $this->price_table[$key]['role_price'] ) )? true : false;?>
									<input type="checkbox" name="eh_pricing_discount_price_adjustment_options[<?php echo $this->price_table[ $key ]['id']; ?>][role_price]" <?php checked($checked , true ); ?> />
								</label>
							</td>
						</tr>
						<?php
					}
				?>
			</tbody>
		</table>
	</td>
</tr>
<script type="text/javascript">

	jQuery(window).load(function(){
		// Ordering
		jQuery('.price_adjustment tbody').sortable({
			items:'tr',
			cursor:'move',
			axis:'y',
			handle: '.sort',
			scrollSensitivity:40,
			forcePlaceholderSize: true,
			helper: 'clone',
			opacity: 0.65,
			placeholder: 'wc-metabox-sortable-placeholder',
			start:function(event,ui){
				ui.item.css('baclbsround-color','#f6f6f6');
			},
			stop:function(event,ui){
				ui.item.removeAttr('style');
				price_adjustment_row_indexes();
			}
		});

		hide_placeholder_text('#eh_pricing_discount_cart_unregistered_user', '#eh_pricing_discount_cart_unregistered_user_text');
		hide_placeholder_text('#eh_pricing_discount_price_unregistered_user', '#eh_pricing_discount_price_unregistered_user_text');
		hide_user_placeholder_text('#eh_pricing_discount_price_user_role', '#eh_pricing_discount_price_user_role_text');
		hide_user_placeholder_text('#eh_pricing_discount_cart_user_role', '#eh_pricing_discount_cart_user_role_text');
		
		jQuery('#eh_pricing_discount_cart_unregistered_user').change(function() {
			hide_placeholder_text('#eh_pricing_discount_cart_unregistered_user', '#eh_pricing_discount_cart_unregistered_user_text');
		});
		
		jQuery('#eh_pricing_discount_price_unregistered_user').change(function() {
			 hide_placeholder_text('#eh_pricing_discount_price_unregistered_user', '#eh_pricing_discount_price_unregistered_user_text');
		});
		
		jQuery('#eh_pricing_discount_cart_user_role').change(function() {
			hide_user_placeholder_text('#eh_pricing_discount_cart_user_role', '#eh_pricing_discount_cart_user_role_text');
		});
		
		jQuery('#eh_pricing_discount_price_user_role').change(function() {
			hide_user_placeholder_text('#eh_pricing_discount_price_user_role', '#eh_pricing_discount_price_user_role_text');
		});

		function price_adjustment_row_indexes() {
			jQuery('.price_adjustment tbody tr').each(function(index, el){
				jQuery('input.order', el).val( parseInt( jQuery(el).index('.price_adjustment tr') ) );
			});
		};
		
		function hide_placeholder_text(check, hide_field){
			if(jQuery(check).is(":checked")) {
				jQuery(hide_field).closest("tr").show();
			}else {
				jQuery(hide_field).closest("tr").hide();
			}
		};
		
		function hide_user_placeholder_text(check, hide_field){
			options = jQuery(check).val();
			if(options != null) {
				jQuery(hide_field).closest("tr").show();
			}else {
				jQuery(hide_field).closest("tr").hide();
			}
		};

	});

</script>

<style type="text/css">
	.price_adjustment th.sort {
		width: 16px;
		padding: 0 16px;
	}
	.price_adjustment td.sort {
		cursor: move;
		width: 16px;
		padding: 0 16px;
		cursor: move;
		background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAICAYAAADED76LAAAAHUlEQVQYV2O8f//+fwY8gJGgAny6QXKETRgEVgAAXxAVsa5Xr3QAAAAASUVORK5CYII=) no-repeat center;					}
</style>