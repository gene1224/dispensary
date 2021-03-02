```
composer install
```
```php
<?php
//Add on functions.php or your plugin

/**
* Hook after import is done
*
* @param wc_products $products
* @return void
*/
function import_email_function($products)
{
	//print_r($products);
	//EMAIL FUNCTION HERE
}
add_action('product_import_finished', 'import_email_function'); 

/**
* Max Product Filter, It should return int value
*
* @param int $max_product
* @return int
*/

function example_callback($max_product)
{
	//Fetch user subscription's max products and return;
	//$max_product default value is 10
	$users_max_product = $max_product + 1;
	return $users_max_product;
}
add_filter('max_products_to_import', 'max_product_function');
?>