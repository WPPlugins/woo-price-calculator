<?php

namespace WSF\Helper;

use WSF\WSF;

class WooCommerceHelper {
    
    var $wsf;

    public function __construct(WSF $wsf) {
        $this->wsf = $wsf;
    }
    /*
     * Ritorna il prezzo in formato WooCommerce
     */
    public function get_price($price){
            return html_entity_decode(wc_price($price));
    }

    /*
     * Ritorna la valuta WooComerce utilizzata
     */
    public function get_currency_symbol(){
            return html_entity_decode(get_woocommerce_currency_symbol());
    }

    /*
     * Ritorna il formato del prezzo WooCommerce utilizzato
     */
    public function get_price_format(){
            return get_woocommerce_price_format();
    }

    /*
     * Ritorna l'ID del carrello corrente
     */
    public function get_current_cartid(){
            global $woocommerce;
            $items = $woocommerce->cart->get_cart();

            foreach($items as $key => $value){
                    return $key;
            }
    }

    /*
     * Ritorna un prodotto di WooCommerce utilizzando l'ID
     */
    public function get_woocommerce_product_by_id($product_id){
        $obj_product = new \WC_Product($product_id);
        return $obj_product;
    }

    /*
     * Ritorna la lista di tutti i prodotti WooCommerce
     */
    public function get_woocommerce_products(){
        $args = array( 'post_type' => 'product', 'posts_per_page' => -1);
        $loop = new \WP_Query( $args );

        $products = array();
        while ( $loop->have_posts() ) : $loop->the_post(); 
            global $product; 
            $products[] = $product;
        endwhile; 
            wp_reset_query();

       return $products;
    }
    
    public function getWooCommerceProductCategories(){

        $result     = array();
        foreach (get_terms('product_cat', array('hide_empty' => 0, 'parent' => 0)) as $each) {
            $result     = $result + $this->getProductCategoriesRecursive($each->taxonomy, $each->term_id);
        }

        return $result;
    }
    

    function getProductCategoriesRecursive($taxonomy = '', $termId, $separator='', $parent_shown = true){

        $args   = array(
            'hierarchical'      => 1,
            'taxonomy'          => $taxonomy,
            'hide_empty'        => 0,
            'orderby'           => 'id',
            'parent'            => $termId,
        );
        
        $term           = get_term($termId , $taxonomy); 
        $result         = array();
        
        if ($parent_shown) {
            //$output                 = $term->name . '<br/>'; 
            $result[$term->term_id]    = $term->name;
            $parent_shown           = false;
        }
        
        $terms          = get_terms($taxonomy, $args);
        $separator      .= $term->name . ' > ';  

        if(count($terms) > 0){            
            /*
             * $term->term_id
             * $category->term_id
             */
            foreach ($terms as $term) {
                //$output .=  $separator . $term->name . " " . $term->slug . '<br/>';
                $result[$term->term_id]        = $separator . $term->name;
                
                //$output .=  $this->getProductCategoriesRecursive($taxonomy, $term->term_id, $separator, $parent_shown);
                $result  = $result + $this->getProductCategoriesRecursive($taxonomy, $term->term_id, $separator, $parent_shown);
            }
        }
        
        return $result;
    }
    
    function getCategoryProductsByCategorySlug($productCategoryName = null){

        $args = array( 
            'post_type'             => 'product', 
            'posts_per_page'        => -1, 
            'product_cat'           => $productCategoryName, 
            'orderby'               => 'id',
        );

        $loop       = new \WP_Query($args);
        $products   = array();
        
        while($loop->have_posts()){
            $loop->the_post(); 
            global $product; 
            
            $products[]     = $product->get_id();

        }
        
        return $products;
    }
    
    function getCategoryProductsByCategoryId($categoryId = null){
        $term = get_term($categoryId, 'product_cat');
        
        if(empty($term)){
            return array();
        }
        
        if($categoryId == null){
            $slug   = null;
        }else{
            $slug = $term->slug;
        }
        
        return $this->getCategoryProductsByCategorySlug($slug);
    }


}