<?php
// Disable deprecated warnings
error_reporting(E_ERROR | E_PARSE);

require_once('wp-load.php');

// Add action to add meta box
add_action('add_meta_boxes', 'add_transaction_id_meta_box');

// Function to add meta box
function add_transaction_id_meta_box() {
    add_meta_box(
        'transaction_id_meta_box',
        'Transaction ID',
        'display_transaction_id_meta_box',
        'shop_order',
        'normal',
        'default'
    );
}

// Function to display transaction ID meta box
function display_transaction_id_meta_box($post) {
    $transaction_id = get_post_meta($post->ID, 'transaction_id', true);
    $bank_name = get_post_meta($post->ID, 'bank_name', true); // Retrieve bank name from post meta
    ?>
    <label for="bank_name">Bank Name:</label>
    <input type="text" id="bank_name" name="bank_name" value="<?php echo esc_attr($bank_name); ?>" disabled>
    <br>
    <label for="transaction_id">Transaction ID:</label>
    <input type="text" id="transaction_id" name="transaction_id" value="<?php echo esc_attr($transaction_id); ?>" disabled>
    <?php
}

// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Check if both order ID and transaction ID are present in the form data
    if (isset($_POST['order_id']) && isset($_POST['transaction_id'])) {
        try {
            // Retrieve and sanitize the inputs
            $order_id = isset($_POST['order_id']) ? sanitize_text_field($_POST['order_id']) : '';
            $transaction_id = isset($_POST['transaction_id']) ? sanitize_text_field($_POST['transaction_id']) : '';
            $bank_name = isset($_POST['bank_name']) ? sanitize_text_field($_POST['bank_name']) : ''; // Retrieve bank name from form data

            // Include WordPress core files for WooCommerce functions
            define('ABSPATH', dirname(__FILE__) . '/');
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');

            // Check if WooCommerce is active
            if (is_plugin_active('woocommerce/woocommerce.php')) {
                // Load WooCommerce functions
                include_once(ABSPATH . 'wp-content/plugins/woocommerce/includes/wc-core-functions.php');

                // Update order status to "Completed"
                $order = wc_get_order($order_id);
                if ($order) {
                    // Save transaction ID and bank name as post meta
                    update_post_meta($order_id, 'transaction_id', $transaction_id);
                    update_post_meta($order_id, 'bank_name', $bank_name); // Save bank name

                    // Update order status to "Completed"
                    $order->update_status('completed');

                    // Redirect to the order received page
                    wp_redirect('https://pb365.kegeberew.com/checkout/order-received/');
                    exit; // Ensure that the script stops executing here
                } else {
                    echo 'Invalid order ID.';
                }
            } else {
                echo 'WooCommerce is not active.';
            }
        } catch (Exception $e) {
            // Handle any unexpected exceptions
            echo 'An error occurred: ' . $e->getMessage();
        }
    } else {
        echo 'Order ID, transaction ID, and bank name are required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction ID Form</title>
    <style>
        body {
            background-color: #dd9933;
        }
    </style>
</head>
<body>
    <h3>Kegeberew E-Commerce</h3>
    <form method="post">
        <input type="hidden" name="order_id" value="<?php echo isset($_GET['order_id']) ? htmlspecialchars($_GET['order_id']) : ''; ?>">
        <label for="bank_name">Bank Name:</label><br>
        <select id="bank_name" name="bank_name" style="height: 40px; width: 300px; padding: 5px;" required>
            <option value="">ገቢ ያደረጉበትን ባንክ ይምረጡ</option>
            <option value="Commercial bank of Ethiopia">የኢትዮጵያ ንግድ ባንክ</option>
            <option value="Awash Bank">አዋሽ ባንክ</option>
            <option value="Abyssinia Bank">አቢሲኒያ ባንክ</option>
            <option value="Dashen Bank">ዳሽን ባንክ</option>
            <option value="Hibret Bank">ህብረት ባንክ</option>
        </select>
        <br><br>
        <label for="transaction_id">Your Transaction Number:</label><br>
        <input type="text" id="transaction_id" name="transaction_id" required style="height: 40px; width: 300px; padding: 5px; box-sizing: border-box;"><br><br>
        <button type="submit" name="submit" style="height: 40px; width: 100px; padding: 5px;">Submit</button>
    </form>
</body>
</html>