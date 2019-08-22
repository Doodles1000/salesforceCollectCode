<?php
add_action('after_body', 'salesforce_collectcode');

function salesforce_collectcode(){ 
    global $wp;
    $trackview = ["trackPageView"];
     if(is_search()){
         $search .= '{"search" : "' . get_search_query() . '"}'; 
        array_push($trackview, json_decode($search));
     }
     if(is_product_category()){
         $category = get_queried_object();
         $term .= '{"category" : "' . $category->term_id . '"}'; 
         array_push($trackview, json_decode($term)); 
      }
      $trackConversion = [];
      if( is_wc_endpoint_url( 'order-received' ) ){
          // Get the order ID
          $order_id  = absint( $wp->query_vars['order-received'] );
          $order = wc_get_order( $order_id );
           $items = $order->get_items();
           if (!empty($items)) {
               array_push($trackConversion, "trackConversion");
               $citems = [];
               foreach($items as $item => $values) { 
                   $_product =  wc_get_product( $values['product_id']); 
                   $_id = $values['product_id'];
                   $_sku = get_post_meta($values['product_id'] , '_sku', true);
                   $price = get_post_meta($values['product_id'] , '_price', true);
                   $tcart = '{"item" : "' . $_sku . '", "quantity" : ' . $values['quantity'] . ', "price" : ' . $price .  ', "unique_id": "' . $_id . '" }'; 
                    array_push($citems, json_decode($tcart));  
            } 
            $cart = '{"cart" :' . json_encode($citems) . '}';
            array_push($trackConversion, json_decode($cart));
        }

      }
      if(is_product()){
          $term .= '{"item" : "' . get_the_id() . '"}'; 
          array_push($trackview, json_decode($term));  
        }
        $trackCart = [];
        $items = WC()->cart->get_cart();
        if (!empty($items)) {
           array_push($trackCart, "trackCart");
           $citems = [];
           foreach($items as $item => $values) { 
               $_product =  wc_get_product( $values['product_id']); 
               $_id = $values['product_id'];
               $_sku = get_post_meta($values['product_id'] , '_sku', true);
               $price = get_post_meta($values['product_id'] , '_price', true);
               $tcart = '{"item" : "' . $_sku . '", "quantity" : ' . $values['quantity'] . ', "price" : ' . $price .  ', "unique_id": "' . $_id . '" }'; 
                array_push($citems, json_decode($tcart));  
            } 
            $cart = '{"cart" :' . json_encode($citems) . '}';
            array_push($trackCart, json_decode($cart));
        }
    ?>
    <script>
        _etmc.push(["setOrgId","100023742"]);
        <?php if (is_user_logged_in()) { 
            $current_user = wp_get_current_user();
            esc_html($current_user->user_email) 
            ?>
        _etmc.push(["setUserInfo", {"email": "<?php echo esc_html($current_user->user_email)?>"}]);
        <?php } ?>
         <?php if(!empty($trackCart)){ ?>
          _etmc.push(<?php echo json_encode($trackCart); ?>);
        <?php } ?>
        <?php if(!empty($trackConversion)){ ?>
          _etmc.push(<?php echo json_encode($trackConversion); ?>);
        <?php } ?>
        _etmc.push(<?php echo json_encode($trackview); ?>);
    </script>
<?php }
