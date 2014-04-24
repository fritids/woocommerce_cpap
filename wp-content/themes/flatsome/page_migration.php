<?php
/*
Template Name: Migration
 * it converts custom fields int attributes
*/

$args = array( 'post_type' => 'product', 'posts_per_page' => 1000);

 $loop = new WP_Query( $args );

 $args = array(
  'name'   => 'genre',
  
  
); 
 $attribute_taxonomies=wc_get_attribute_taxonomies();
 
 $order=array(
   "pa_brand"  => 0,
    "pa_hcpcs-reimbursement-code"  => 1,
    "pa_included-in-box" => 2,
    "pa_multiple-sizes-included" =>3,
    "pa_prescription-required" => 4,
   
    "pa_pressure-range" => 6,
    "pa_dimensions" => 7,
    "pa_msrp-full-support" => 8,
    "msrp-limited-support" =>9
 );
 
while ( $loop->have_posts() ) : $loop->the_post(); 
global $product; 
    if ($loop->post->ID!="3474"){
        continue;
    }
        
    echo ' Name : ' .$product->get_title()." SKU: ". $product->get_sku()." ID: ".$loop->post->ID."<br />"; 
    
    $attributes = $product->get_attributes();
    
    foreach($attributes as $attribute){
        echo wc_attribute_label( $attribute['name'] )." : ";
    }
    
   
    echo "<br/><br/> Custom Fields:";
   
    $custom_field_keys=get_post_custom_keys($loop->post->ID ); 
    
    
    foreach($custom_field_keys as $key){
        if (substr( $key, 0, 1 ) !== "_"){
            
            if (($key!="Resources") && ($key!="Specifications")){
               
                
                $values=get_post_custom_values($key, $loop->post->ID);
                    $key=str_replace( " ", "-",$key);
                    echo ('pa_'.strtolower($key)."<br/>" );
                    
                $terms = wp_get_object_terms( $loop->post->ID, 'pa_'.strtolower($key)  );
                
                
               if ( (sizeof($terms)==0  ||  is_array($terms)==FALSE  )) {
                    
                    
              //    var_dump(wp_set_object_terms(  $loop->post->ID, "asdfdsf", 'pa_brand',true ));
                    
                     $attributes = $product->get_attributes();
                       
                     $position=$order['pa_'.strtolower($key)];
                    if ($position===null){
                       $position=100; 
                    }
                    
                   //delete 
                
                   
                   
                   $attributes['pa_'.strtolower($key)] = array(
                    //Make sure the 'name' is same as you have the attribute
                   
                       
                    'name' => 'pa_'.strtolower($key),
                        'value' => '',
                    'position' => $position,
                    'is_visible' => 1,
                    'is_variation' => 0,
                    'is_taxonomy' => 1
                   );
                   
                  var_dump(wp_set_object_terms( $loop->post->ID, null, 'pa_'.strtolower($key), false ));
//Add as post meta
                 var_dump( update_post_meta( $loop->post->ID, '_product_attributes', $attributes));
               
               ////////////////////
                 var_dump(wp_set_object_terms( $loop->post->ID, $values[0], 'pa_'.strtolower($key), false ));
                   
                   echo $key." : ".$values[0]. "<br/>"; 
                                      
               }
                
                
               
            }
        }
    }
    
    
    
    echo "<br/><br/>";
    
//    // Iterate Global attributes
//    foreach ( $attribute_taxonomies as $tax ) {
//        echo $tax->attribute_name."<br/>";
//    }
    
endwhile; 


wp_reset_query(); 