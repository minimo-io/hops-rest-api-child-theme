<?php

// saswp_modify_schema_output
//         return  apply_filters('saswp_modify_schema_output', $all_schema_output);



add_filter( 'saswp_modify_schema_output', 'hops_modify_general_schema', 10, 1 );
add_filter( 'saswp_modify_product_schema_output', 'hops_modify_product_schema', 10, 1 );



function hops_modify_product_schema($all_schema_output){


    if (
        
        class_exists( 'WooCommerce' ) 
        && class_exists('ACF')
        && is_product()

        ){


    }


    return $all_schema_output;
}

function hops_modify_general_schema($schema){


    if (
        
        class_exists( 'WooCommerce' ) 
        && class_exists('ACF')
        && is_product()

        ){

        $brewery = get_field("brewery", get_the_ID());
        

        if (isset($schema[0]["brand"]["name"])){
         $schema[0]["brand"]["name"] = $brewery->post_title;
        }


    }

    if($_GET["dev"]) print_r($schema);

    return $schema;
}


?>