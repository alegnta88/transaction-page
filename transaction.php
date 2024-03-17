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
    // Check if both order ID, transaction ID, and bank name are present in the form data
    if (isset($_POST['order_id']) && isset($_POST['transaction_id']) && isset($_POST['bank_name'])) {
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

                // Check if the order is already completed
                $order = wc_get_order($order_id);
                if ($order && $order->get_status() !== 'completed') {
                    // Save transaction ID and bank name as post meta
                    update_post_meta($order_id, 'transaction_id', $transaction_id);
                    update_post_meta($order_id, 'bank_name', $bank_name); // Save bank name

                    // Update order status to "Completed"
                    $order->update_status('completed');

                    // Output JavaScript to show the success modal
                    echo '<script type="text/javascript">
                        window.onload = function() {
                            document.getElementById("successModal").style.display = "block";
                        };
                    </script>';
                } else {
                    echo '<script type="text/javascript">
                        window.onload = function() {
                            document.getElementById("alreadyCompletedModal").style.display = "block";
                        };
                            </script>';
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
            background-color: #bc982b;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.8);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            border-radius: 10px;
            opacity: .8;
            height: 200px;
            font-size: 26px;
        }
        .close {
            color: #000;
            float: right;
            font-size: 35px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <img src="image/Kegeberew-logo.png" alt="Logo" class="logo" style="height:100px;">
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

    <div id="successModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <center><img src="image/success.jpg" alt="Logo" class="logo" style="height:80px;"></center>
            <center><p>ትእዛዝዎ በተሳካ ሁኔታ ተላልፏል እናመሰግናለን!</p></center>
        </div>
    </div>

    <div id="alreadyCompletedModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <center><img src="image/warn.jpg" alt="Logo" class="logo" style="height:80px;"></center>
            <center><p style="color:black;">ይህ ትእዛዝ ከዚህ በፊት ተላልፏል እናመሰግናለን!</p></center>
        </div>
    </div>

<script>
    // Get the modal for success
    var successModal = document.getElementById("successModal");
    // Get the modal for already completed
    var alreadyCompletedModal = document.getElementById("alreadyCompletedModal");

    // Get the <span> element that closes the modal for success
    var successModalClose = successModal.getElementsByClassName("close")[0];
    // Get the <span> element that closes the modal for already completed
    var alreadyCompletedModalClose = alreadyCompletedModal.getElementsByClassName("close")[0];

    // When the user clicks on <span> (x), close the modal for success
    successModalClose.onclick = function() {
        successModal.style.display = "none";
    };

    // When the user clicks on <span> (x), close the modal for already completed
    alreadyCompletedModalClose.onclick = function() {
        alreadyCompletedModal.style.display = "none";
    };

    // When the user clicks anywhere outside of the modal for success, close it
    window.onclick = function(event) {
        if (event.target == successModal) {
            successModal.style.display = "none";
        } else if (event.target == alreadyCompletedModal) {
            alreadyCompletedModal.style.display = "none";
        }
    };
</script>

</body>
</html>
