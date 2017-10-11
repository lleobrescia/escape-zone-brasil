<?php

defined('ABSPATH') or die();

/**
 * 
 * Interface to WooCommerce. Handles version differences / backwards compatibility.
 * 
 * @since 2.3.7.2
 */
class WJECF_WC {

    protected $wrappers = array();

    /**
     * Wrap a data object (WC 2.7 introduced WC_Data)
     * @param type $object 
     * @return type
     */
    public function wrap( $object, $use_pool = true ) {
        if ( $use_pool ) {
            //Prevent a huge amount of wrappers to be initiated; one wrapper per object instance should do the trick
            foreach( $this->wrappers as $wrapper ) {
                if ($wrapper->holds( $object ) ) {
                    //error_log('Reusing wrapper ' . get_class( $object ) );
                    return $wrapper;
                }
            }
        }

        if ( is_numeric( $object ) ) {
            $post_type = get_post_type( $object );
            if ( $post_type == 'shop_coupon' ) {
                $object = WJECF_WC()->get_coupon( $object );
            } elseif ( $post_type == 'product' ) {
                $object = new WC_Product( $object );
            } 
        }
        if ( is_string( $object ) ) {
            $object = WJECF_WC()->get_coupon( $object );
        }


        if ( $object instanceof WC_Coupon ) {
            return $this->wrappers[] = new WJECF_Wrap_Coupon( $object );
        }

        if ( $object instanceof WC_Product ) {
            return $this->wrappers[] = new WJECF_Wrap_Product( $object );
        }

        throw new Exception( 'Cannot wrap ' . get_class( $object ) );
    }

    /**
     * Returns a specific item in the cart.
     *
     * @param string $cart_item_key Cart item key.
     * @return array Item data
     */
    public function get_cart_item( $cart_item_key ) {        
        if ( $this->check_woocommerce_version('2.2.9') ) {
            return WC()->cart->get_cart_item( $cart_item_key );
        }

        return isset( WC()->cart->cart_contents[ $cart_item_key ] ) ? WC()->cart->cart_contents[ $cart_item_key ] : array();
       }

    /**
     * Get categories of a product (and anchestors)
     * @param int $product_id 
     * @return array product_cat_ids
     */
    public function wc_get_product_cat_ids( $product_id ) {
        //Since WC 2.5.0
        if ( function_exists( 'wc_get_product_cat_ids' ) ) {
            return wc_get_product_cat_ids( $product_id );
        }

        $product_cats = wp_get_post_terms( $product_id, 'product_cat', array( "fields" => "ids" ) );

        foreach ( $product_cats as $product_cat ) {
            $product_cats = array_merge( $product_cats, get_ancestors( $product_cat, 'product_cat' ) );
        }
        return $product_cats;
    }

    /**
     * Coupon types that apply to individual products. Controls which validation rules will apply.
     *
     * @since  2.5.0
     * @return array
     */
    public function wc_get_product_coupon_types() {
        //Since WC 2.5.0
        if ( function_exists( 'wc_get_product_coupon_types' ) ) {
            return wc_get_product_coupon_types();
        }
        return array( 'fixed_product', 'percent_product' );
    }

    public function wc_get_cart_coupon_types() {
        //Since WC 2.5.0
        if ( function_exists( 'wc_get_cart_coupon_types' ) ) {
            return wc_get_cart_coupon_types();
        }
        return array( 'fixed_cart', 'percent' );
    }

    /**
     * Output a list of variation attributes for use in the cart forms.
     *
     * @param array $args
     * @since 2.5.1
     */
    public function wc_dropdown_variation_attribute_options( $args = array() ) {
        if ( function_exists( 'wc_dropdown_variation_attribute_options' ) ) {
            return wc_dropdown_variation_attribute_options( $args );
        }

        //Copied from WC2.4.0 wc-template-functions.php
        $args = wp_parse_args( $args, array(
            'options'          => false,
            'attribute'        => false,
            'product'          => false,
            'selected'         => false,
            'name'             => '',
            'id'               => '',
            'show_option_none' => __( 'Choose an option', 'woocommerce' )
        ) );

        $options   = $args['options'];
        $product   = $args['product'];
        $attribute = $args['attribute'];
        $name      = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
        $id        = $args['id'] ? $args['id'] : sanitize_title( $attribute );

        if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
            $attributes = $product->get_variation_attributes();
            $options    = $attributes[ $attribute ];
        }

        echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" data-attribute_name="attribute_' . esc_attr( sanitize_title( $attribute ) ) . '">';

        if ( $args['show_option_none'] ) {
            echo '<option value="">' . esc_html( $args['show_option_none'] ) . '</option>';
        }

        if ( ! empty( $options ) ) {
            if ( $product && taxonomy_exists( $attribute ) ) {
                // Get terms if this is a taxonomy - ordered. We need the names too.
                $terms = wc_get_product_terms( $product->id, $attribute, array( 'fields' => 'all' ) );

                foreach ( $terms as $term ) {
                    if ( in_array( $term->slug, $options ) ) {
                        echo '<option value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $args['selected'] ), $term->slug, false ) . '>' . apply_filters( 'woocommerce_variation_option_name', $term->name ) . '</option>';
                    }
                }
            } else {
                foreach ( $options as $option ) {
                    // This handles < 2.4.0 bw compatibility where text attributes were not sanitized.
                    $selected = sanitize_title( $args['selected'] ) === $args['selected'] ? selected( $args['selected'], sanitize_title( $option ), false ) : selected( $args['selected'], $option, false );
                    echo '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';
                }
            }
        }

        echo '</select>';
    }

    /**
     * Get attibutes/data for an individual variation from the database and maintain it's integrity.
     * @since  2.5.1
     * @param  int $variation_id
     * @return array
     */
    public function wc_get_product_variation_attributes( $variation_id ) {
        if ( function_exists( 'wc_get_product_variation_attributes' ) ) {
            return wc_get_product_variation_attributes( $variation_id );
        }

        //Copied from WC2.4.0 wc-product-functions.php

        // Build variation data from meta
        $all_meta                = get_post_meta( $variation_id );
        $parent_id               = wp_get_post_parent_id( $variation_id );
        $parent_attributes       = array_filter( (array) get_post_meta( $parent_id, '_product_attributes', true ) );
        $found_parent_attributes = array();
        $variation_attributes    = array();

        // Compare to parent variable product attributes and ensure they match
        foreach ( $parent_attributes as $attribute_name => $options ) {
            $attribute                 = 'attribute_' . sanitize_title( $attribute_name );
            $found_parent_attributes[] = $attribute;
            if ( ! array_key_exists( $attribute, $variation_attributes ) ) {
                $variation_attributes[ $attribute ] = ''; // Add it - 'any' will be asumed
            }
        }

        // Get the variation attributes from meta
        foreach ( $all_meta as $name => $value ) {
            // Only look at valid attribute meta, and also compare variation level attributes and remove any which do not exist at parent level
            if ( 0 !== strpos( $name, 'attribute_' ) || ! in_array( $name, $found_parent_attributes ) ) {
                unset( $variation_attributes[ $name ] );
                continue;
            }
            /**
             * Pre 2.4 handling where 'slugs' were saved instead of the full text attribute.
             * Attempt to get full version of the text attribute from the parent.
             */
            if ( sanitize_title( $value[0] ) === $value[0] && version_compare( get_post_meta( $parent_id, '_product_version', true ), '2.4.0', '<' ) ) {
                foreach ( $parent_attributes as $attribute ) {
                    if ( $name !== 'attribute_' . sanitize_title( $attribute['name'] ) ) {
                        continue;
                    }
                    $text_attributes = wc_get_text_attributes( $attribute['value'] );

                    foreach ( $text_attributes as $text_attribute ) {
                        if ( sanitize_title( $text_attribute ) === $value[0] ) {
                            $value[0] = $text_attribute;
                            break;
                        }
                    }
                }
            }

            $variation_attributes[ $name ] = $value[0];
        }

        return $variation_attributes;
    } 

    public function find_matching_product_variation( $product, $match_attributes = array() ) {
        if ( $this->check_woocommerce_version( '3.0') ) {
            $data_store   = WC_Data_Store::load( 'product' );
            $variation_id = $data_store->find_matching_product_variation( $product, $match_attributes );
            return $variation_id;
        }

        return $product->get_matching_variation( $match_attributes );      
    }

    /**
     * @since 2.4.0 for WC 2.7 compatibility
     * 
     * Get a WC_Coupon object
     * @param WC_Coupon|string|WP_Post $coupon The coupon code or a WC_Coupon object
     * @return WC_Coupon The coupon object
     */
    public function get_coupon( $coupon ) {
        if ( $coupon instanceof WP_Post ) {
            $coupon = $coupon->ID;
        }
        if ( is_numeric( $coupon ) && ! $this->check_woocommerce_version( '3.0' ) ) {
            //By id; not neccesary for WC3.0; as the WC_Coupon constructor accepts an id
            global $wpdb;
            $coupon_code = $wpdb->get_var( $wpdb->prepare( "SELECT post_title FROM $wpdb->posts WHERE id = %d AND post_type = 'shop_coupon'", $coupon ) );
            if ( $coupon_code !== null) {
                $coupon = $coupon_code;
            }
        }
        if ( ! ( $coupon instanceof WC_Coupon ) ) {
            //By code
            $coupon = new WC_Coupon( $coupon );
        }
        return $coupon;
    }    


//VERSION

    /**
     * Check whether WooCommerce version is greater or equal than $req_version
     * @param string @req_version The version to compare to
     * @return bool true if WooCommerce is at least the given version
     */
    public function check_woocommerce_version( $req_version ) {
        return version_compare( $this->get_woocommerce_version(), $req_version, '>=' );
    }    

    private $wc_version = null;
    
    /**
     * Get the WooCommerce version number
     * @return string|bool WC Version number or false if WC not detected
     */
    public function get_woocommerce_version() {
        if ( isset( $this->wc_version ) ) {
            return $this->wc_version;
        }

        if ( defined( 'WC_VERSION' ) ) {
            return $this->wc_version = WC_VERSION;
        }

        // If get_plugins() isn't available, require it
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }        
        // Create the plugins folder and file variables
        $plugin_folder = get_plugins( '/woocommerce' );
        $plugin_file = 'woocommerce.php';
        
        // If the plugin version number is set, return it 
        if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
            return $this->wc_version = $plugin_folder[$plugin_file]['Version'];
        }

        return $this->wc_version = false; // Not found
    }

//INSTANCE

    /**
     * Singleton Instance
     *
     * @static
     * @return Singleton Instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    protected static $_instance = null;
}


class WJECF_Wrap {
    protected $object = null;

    public function __construct( $object ) {
        $this->object = $object;
        //error_log('Wrapping ' . get_class( $object ) );
    }

    public $use_wc27 = true;
    public function get_id() {
        //Since WC 2.7
        if ( $this->use_wc27 && is_callable( array( $this->object, 'get_id' ) ) ) {
            return $this->object->get_id();
        }
        return $this->object->id;
    }

    public function holds( $object ) {
        return $object === $this->object;
    }

    /**
     * Get Meta Data by Key.
     * 
     * If no value found: 
     * If $single is true, an empty string is returned.
     * If $single is false, an empty array is returned.
     * 
     * @since  2.4.0
     * @param  string $key
     * @param  bool $single return first found meta, or all
     * @return mixed
     */
    public final function get_meta( $meta_key, $single = true ) {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'get_meta' ) ) ) {            
            return $this->get_meta_wc27( $meta_key, $single );
        }

        return $this->get_meta_legacy( $meta_key, $single );
    }

    protected function get_meta_wc27( $meta_key, $single = true ) {
        $values = $this->object->get_meta( $meta_key, $single );
        if ($single) {
            return $values; //it's just one, dispite the plural in the name!
        }

        if ( $values === '' ) {
            return array(); //get_meta returns empty string if meta does not exist
        }

        return wp_list_pluck( array_values( $values ), 'value' ); //when not using array_values; the index might not start with 0
    } 

    protected function get_meta_legacy( $meta_key, $single = true ) {
        throw new Exception( sprintf( '%s::get_meta_legacy not implemented', get_class( $this ) ) );
    }

    /**
     * Update single meta data item by meta key.
     * Call save() if the values must to be persisted.
     * @since  2.4.0
     * @param  string $meta_key
     * @param  mixed $value The value; use null to clear
     */
    public final function set_meta( $meta_key, $value ) {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'update_meta_data' ) ) ) {            
            if ( $value === null ) {
                $this->object->delete_meta_data( $meta_key );
            } else {
                $this->object->update_meta_data( $meta_key, $value );
            }
            return;
        }

        $this->set_meta_legacy( $meta_key, $value );
    }

    protected function set_meta_legacy( $meta_key, $value ) {
        throw new Exception( sprintf( '%s::set_meta_legacy not implemented', get_class( $this ) ) );
    }

}

/**
 * Wrap a data object ( Coupons and products were converted to WC_Data since WC 2.7.0 )
 */
class WJECF_Wrap_Coupon extends WJECF_Wrap {

    public function exists() {
        return $this->get_id() > 0;
    }

    public function get_code() {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'get_code' ) ) ) {
            return $this->object->get_code();
        }

        return $this->object->code;
    }

    public function get_amount() {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'get_amount' ) ) ) {
            return $this->object->get_amount();
        }

        return $this->object->coupon_amount;
    }    

    public function get_individual_use() {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'get_individual_use' ) ) ) {
            return $this->object->get_individual_use();
        }
        
        return $this->object->individual_use == 'yes';
    }

    public function get_limit_usage_to_x_items() {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'get_limit_usage_to_x_items' ) ) ) {
            return $this->object->get_limit_usage_to_x_items();
        }
        
        return $this->object->limit_usage_to_x_items;
    }

    public function set_limit_usage_to_x_items( $limit_usage_to_x_items ) {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'set_limit_usage_to_x_items' ) ) ) {
            $this->object->set_limit_usage_to_x_items( $limit_usage_to_x_items );
        } else {
            $this->object->limit_usage_to_x_items = $limit_usage_to_x_items;
        }
    }

    public function get_discount_type() {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'get_discount_type' ) ) ) {
            return $this->object->get_discount_type();
        }
        
        return $this->object->discount_type;
    }    

    public function set_discount_type( $discount_type ) {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'set_discount_type' ) ) ) {
            $this->object->set_discount_type( $discount_type );
        } else {
            $this->object->discount_type = $discount_type;
            $this->object->type = $discount_type;
        }
    }


    public function get_email_restrictions() {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'get_email_restrictions' ) ) ) {
            return $this->object->get_email_restrictions();
        }
        
        return $this->object->customer_email;
    }

    public function get_product_ids() {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'get_product_ids' ) ) ) {
            return $this->object->get_product_ids();
        }
        
        return $this->object->product_ids;
    }

    public function get_free_shipping() {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'get_free_shipping' ) ) ) {
            return $this->object->get_free_shipping();
        }
        
        return $this->object->enable_free_shipping();
    }    

    public function get_product_categories() {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'get_product_categories' ) ) ) {
            return $this->object->get_product_categories();
        }
        
        return $this->object->product_categories;
    }

    public function get_minimum_amount() {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'get_minimum_amount' ) ) ) {
            return $this->object->get_minimum_amount();
        }
        
        return $this->object->minimum_amount;
    }

    /**
     * Set the product IDs this coupon cannot be used with.
     * @since  2.4.2 (For WC3.0)
     * @param  array $excluded_product_ids
     * @throws WC_Data_Exception
     */
    public function set_excluded_product_ids( $excluded_product_ids ) {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'set_excluded_product_ids' ) ) ) {
            $this->object->set_excluded_product_ids( $excluded_product_ids );
        } else {
             //NOTE: Prior to WC2.7 it was called exclude_ instead of excluded_
            $this->object->exclude_product_ids = $excluded_product_ids;
        }
    }

    /**
     * Set the product category IDs this coupon cannot be used with.
     * @since  2.4.2 (For WC3.0)
     * @param  array $excluded_product_categories
     * @throws WC_Data_Exception
     */
    public function set_excluded_product_categories( $excluded_product_categories ) {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'set_excluded_product_categories' ) ) ) {
            $this->object->set_excluded_product_categories( $excluded_product_categories );
        } else {
             //NOTE: Prior to WC2.7 it was called exclude_ instead of excluded_
            $this->object->exclude_product_categories = $excluded_product_categories;
        }
    }

    /**
     * Set if this coupon should excluded sale items or not.
     * @since  2.4.2 (For WC3.0)
     * @param  bool $exclude_sale_items
     * @throws WC_Data_Exception
     */
    public function set_exclude_sale_items( $exclude_sale_items ) {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'set_exclude_sale_items' ) ) ) {
            $this->object->set_exclude_sale_items( $exclude_sale_items );
        } else {
             //NOTE: Prior to WC2.7 it was yes/no instead of boolean
            $this->object->exclude_sale_items = $exclude_sale_items ? 'yes' : 'no';
        }
    }    

    /**
     * Check the type of the coupon
     * @param string|array $type The type(s) we want to check for
     * @return bool True if the coupon is of the type
     */
    public function is_type( $type ) {
        //Backwards compatibility 2.2.11
        if ( method_exists( $this->object, 'is_type' ) ) {
            return $this->object->is_type( $type );
        }
        
        return ( $this->object->discount_type == $type || ( is_array( $type ) && in_array( $this->object->discount_type, $type ) ) ) ? true : false;
    }    

    protected function set_meta_legacy( $meta_key, $value ) {
        $this->maybe_get_custom_fields();
        //WJECF()->log('...setting legacy meta ' . $meta_key );
        $this->legacy_custom_fields[ $meta_key ] = array( $value );
        $this->legacy_unsaved_keys[] = $meta_key;
    }

    /**
     * Save the metadata
     * @return id of this object
     */
    public function save() {
        //WJECF()->log('Saving ' . $this->get_id() );
        if ( $this->use_wc27 && is_callable( array( $this->object, 'save' ) ) ) {            
            return $this->object->save();
        }

        //Save the unsaved...
        foreach( $this->legacy_unsaved_keys as $meta_key ) {
            //WJECF()->log('...saving legacy meta ' . $meta_key );
            $value = reset( $this->legacy_custom_fields[ $meta_key ] );
            if ( $value === null ) {
                delete_post_meta( $this->get_id(), $meta_key );
            } else {
                update_post_meta( $this->get_id(), $meta_key, $value );
            }
        }
        $this->legacy_unsaved_keys = array();

        return $this->get_id();
    }

    protected $legacy_custom_fields = null; // [ 'meta_key' => [ array_of_values ] ]
    protected $legacy_unsaved_keys = array();

    protected function maybe_get_custom_fields() {
        //Read custom fields if not yet done
        if ( is_null( $this->legacy_custom_fields ) ) {
            $this->legacy_custom_fields = $this->object->coupon_custom_fields;
        }
    }

    protected function get_meta_legacy( $meta_key, $single = true ) {
        //Read custom fields if not yet done
        $this->maybe_get_custom_fields();

        if ( isset( $this->legacy_custom_fields[ $meta_key ] ) ) {
            $values = $this->legacy_custom_fields[ $meta_key ];
            //WP_CLI::log( "LEGACY:" . print_r( $values, true ));
            if ($single) {
                return maybe_unserialize( reset( $values ) ); //reset yields the first
            }
            $values = array_map( 'maybe_unserialize', $values );
            return $values;
        }

        return $single ? '' : array();
    }

}

class WJECF_Wrap_Product extends WJECF_Wrap {

    protected $legacy_custom_fields = null; // [ 'meta_key' => [ array_of_values ] ]
    protected $legacy_unsaved_keys = array();

    protected function get_meta_legacy( $meta_key, $single = true ) {
        if ( isset( $this->legacy_custom_fields[ $meta_key ] ) ) {
            $values = $this->legacy_custom_fields[ $meta_key ];
            //WP_CLI::log( "LEGACY:" . print_r( $values, true ));
            if ($single) {
                return maybe_unserialize( reset( $values ) ); //reset yields the first
            }
            $values = array_map( 'maybe_unserialize', $values );
            return $values;
        }

        return $single ? '' : array();
    }

    public function set_meta_legacy( $meta_key, $value ) {
        $this->legacy_custom_fields[ $meta_key ] = array( 0 => $value );
        $this->legacy_unsaved_keys[] = $meta_key;
    }    

    private function is_variation() {
        return $this->object instanceof WC_Product_Variation;
    }

    /**
     * Retrieve the id of the product or the variation id if it's a variant.
     * 
     * (2.4.0: Moved from WJECF_Controller to WJECF_WC)
     * 
     * @param WC_Product $product 
     * @return int|bool The variation or product id. False if not a valid product
     */
    public function get_product_or_variation_id() {
        if ( $this->is_variation() ) {
            return $this->get_variation_id();
        } elseif ( $this->object instanceof WC_Product ) {
            return $this->get_id();
        } else {
            return false;
        }
    }

    /**
     * Retrieve the id of the parent product if it's a variation; otherwise retrieve this products id
     * 
     * (2.4.0: Moved from WJECF_Controller to WJECF_WC)
     * 
     * @param WC_Product $product 
     * @return int|bool The product id. False if this product is not a variation
     */
    public function get_variable_product_id() {
        if ( ! $this->is_variation() ) {
            return false;
        }

        if ( $this->use_wc27 && is_callable( array( $this->object, 'get_parent_id' ) ) ) {
            return $this->object->get_parent_id();
        } else {
            return wp_get_post_parent_id( $this->object->variation_id );
        }
    }

    /**
     * Get current variation id
     * @return int|bool False if this is not a variation
     */
    protected function get_variation_id() {
        if ( ! $this->is_variation() ) {
            return false;
        }

        if ( $this->use_wc27 && is_callable( array( $this->object, 'get_id' ) ) ) {
            //WP_CLI::log( "get_variation_id:WC27 " . get_class( $this->object ) );
            return $this->object->get_id(); 
        } elseif ( $this->use_wc27 && is_callable( array( $this->object, 'get_variation_id' ) ) ) {
            //WP_CLI::log( "get_variation_id:LEGACY " . get_class( $this->object ) );
            return $this->object->get_variation_id(); 
        }
        //WP_CLI::log( "get_variation_id:VERY OLD " . get_class( $this->object ) );
        return $this->object->variation_id;
    }


    public function get_name() {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'get_name' ) ) ) {
            return $this->object->get_name();
        } else {
            return $this->object->post->post_title;
        }
    }    

    public function get_description() {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'get_description' ) ) ) {
            return $this->object->get_description();
        } else {
            return $this->object->post->post_content;
        }
    }

    public function get_short_description() {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'get_short_description' ) ) ) {
            return $this->object->get_short_description();
        } else {
            return $this->object->post->post_excerpt;
        }
    }

    public function get_tag_ids() {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'get_tag_ids' ) ) ) {
            return $this->object->get_tag_ids();
        } else {
            return $this->legacy_get_term_ids( 'product_tag' );
        }
    }

    protected function legacy_get_term_ids( $taxonomy ) {
        $terms = get_the_terms( $this->get_id(), $taxonomy );
        if ( false === $terms || is_wp_error( $terms ) ) {
            return array();
        }
        return wp_list_pluck( $terms, 'term_id' );
    }

    /**
     * If set, get the default attributes for a variable product.
     *
     * @param string $attribute_name
     * @return string
     */
    public function get_variation_default_attribute( $attribute_name ) {
        if ( $this->use_wc27 && is_callable( array( $this->object, 'get_variation_default_attribute' ) ) ) {
            return $this->object->get_variation_default_attribute( $attribute_name );
        }
        return '';
    }

}