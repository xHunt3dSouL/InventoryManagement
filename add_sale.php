<?php
$page_title = 'Add Sale';
require_once('includes/load.php');
require_once('includes/functions.php');

// Rest of your code...

// Checking what level user has permission to view this page
page_require_level(3);

// Include the file containing the necessary functions
require_once('includes/functions.php'); // Replace with the actual path to your functions file

if (isset($_POST['add_sale'])) {
    $req_fields = array('s_id', 'quantity', 'price', 'total', 'date');
    validate_fields($req_fields);

    if (empty($errors)) {
        // Escape and sanitize input data
        $p_id = $db->escape((int)$_POST['s_id']);
        $s_qty = $db->escape((int)$_POST['quantity']);
        $s_total = $db->escape($_POST['total']);
        $date = $db->escape($_POST['date']);
        $s_date = make_date(); // Make sure make_date() is defined

        // Check if the quantity is greater than zero
        if ($s_qty <= 0) {
            $session->msg('d', 'Error: Sale quantity must be greater than zero.');
            redirect('add_sale.php', false);
        }

        // Check available stock
        $available_stock = find_product_by_id($p_id)['quantity'];

        if ($s_qty > $available_stock) {
            $session->msg('d', 'Error: Sale quantity exceeds available stock.');
            redirect('add_sale.php', false);
        }

        // Insert sale record
        $sql = "INSERT INTO sales (product_id, qty, price, date) VALUES ('$p_id', '$s_qty', '$s_total', '$s_date')";

        if ($db->query($sql)) {
            // Update product quantity after sale
            update_product_qty($s_qty, $p_id); // Make sure update_product_qty() is defined
            $session->msg('s', 'Sale added.');
            redirect('add_sale.php', false);
        } else {
            $session->msg('d', 'Sorry, failed to add sale.');
            redirect('add_sale.php', false);
        }
    } else {
        $session->msg('d', $errors);
        redirect('add_sale.php', false);
    }
}
?>

<?php include_once('layouts/header.php'); ?>
<div class="row">
    <div class="col-md-6">
        <?php echo display_msg($msg); ?>
        <form method="post" action="ajax.php" autocomplete="off" id="sug-form">
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-primary">Find It</button>
                    </span>
                    <input type="text" id="sug_input" class="form-control" name="title" placeholder="Search for product name">
                </div>
                <div id="result" class="list-group"></div>
            </div>
        </form>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    <span>Sale Edit</span>
                </strong>
            </div>
            <div class="panel-body">
                <form method="post" action="add_sale.php">
                    <table class="table table-bordered">
                        <thead>
                            <th> Item </th>
                            <th> Price </th>
                            <th> Qty </th>
                            <th> Total </th>
                            <th> Date</th>
                            <th> Action</th>
                        </thead>
                        <tbody id="product_info"></tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include_once('layouts/footer.php'); ?>
