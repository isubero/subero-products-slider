# Subero Products Slider
Shortcode for displaying easily displaying responsive WooCommerce Products Sliders. If you are not familiar with WordPress shortcodes, you can [read about this functionality here](https://codex.wordpress.org/shortcode).

## Shortcode arguments

### product_ids
Enter the IDs of the products you want to be present in the slider. ID's **must be separated by commas**.

Example: [subero_products_slider products_ids="532, 147, 5621"]

### category
Indicates the **category slug** used to query products.

Example: [subero_products_slider category="category-slug"]

### on_sale
Whether to filter products on sale or not. Accepts two options "true" or "false". Default option is false.

Example: [subero_products_slider on_sale="true"]

### limit
Indicates how many products should be returned, default is set to 10. Set this option to false to disable the limit.

Example: [subero_products_slider limit="20"]

Disable limit: [subero_products_slider limit="false"]

### Combining arguments
You can use more than one argument at once.

#### Show a slider with all products on sale of a certain category
[subero_products_slider category="shirts" on_sale="true" limit="false"]
