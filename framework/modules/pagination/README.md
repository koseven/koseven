# Koseven - pagination module
Pagination module for Koseven

## Getting Started
2. Enable the module in your bootstrap file:

```php
/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
// ...
        'pagination'       => MODPATH.'pagination',
// ...
));
  ```
3. Make sure the settings in `config/pagination.php` are correct for your environment. If not, copy the file to `application/config/pagination.php` and change the values accordingly.

## Usage

```php

class Controller_Products extends Controller_Template
{
    public function action_index() {

    	$products_count = ORM::factory('Product')->count_all();

    	$pagination = Pagination::factory(array(
            'total_items' => $products_count,
            'items_per_page' => 20,
        ));

    	$products = ORM::factory('Product')
    	    ->limit($pagination->items_per_page)
    	    ->offset($pagination->offset)
    	    ->find_all();

    	$content = View::factory('index/v_products')
    	    ->set('products', $products)
    	    ->set('pagination', $pagination);
    }
 }

```

## License
This package is open-sourced software licensed under the [BSD license](https://koseven.ga/LICENSE.md)