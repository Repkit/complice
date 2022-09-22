<?php 

error_reporting(-1);
ini_set('display_errors',1);

require('../wp-config.php');
include_once("../wp-includes/wp-db.php");

if(!current_user_can('administrator')) {
    wp_redirect( wp_login_url() );
}

$new_valability = $_POST['new_valability'] ?? null;

if($new_valability){

    $exclude_products = $_POST['exclude_products'] ?? null;
    $include_products = $_POST['include_products'] ?? null;
    $all_products = $_POST['all_products'] ?? null;

    $sql = "UPDATE `wp_postmeta` SET `meta_value`= '$new_valability' WHERE meta_key = '_voucher_valability' ";
    if(!empty($all_products)){
        $sql .= "";
    }else{
        $sql .= "AND length(meta_value) > 0";
    }

    if(!empty($exclude_products)){
        $sql .= " AND post_id not in ($exclude_products)";
    }

    if(!empty($include_products)){
        $sql .= " AND post_id in ($include_products)";
    }

    // echo $sql;
    try{
        $results = $wpdb->get_results($sql);  
        $status = 'completed';
    } catch(Exception $e) {
        $status = $e->getMessage();
    }
}

?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">

    <!-- Bootstrap CSS -->
    <link href="//cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <title>Complice.ro | Update product voucher valability</title>
  </head>
  <body>
    <div class="container">
        <h1>Update product voucher valability!</h1>
        <small>
            <i>This will update all products' voucher valability that have already set a valability. <br/> 
                Products with no voucher valability defined yet will not be affected.
            </i>
        </small>
        <hr>
        <form method="post" action="">
          <div class="mb-3">
            <label for="new_valability" class="form-label">New voucher valability</label>
            <input type="text" class="form-control" name="new_valability" id="new_valability" aria-describedby="new_valabilityHelp">
            <div id="new_valabilityHelp" class="form-text">will override existing voucher valability</div>
          </div>
          <div class="mb-3">
            <label for="exclude_products" class="form-label">Exclude products</label>
            <input type="text" class="form-control" name="exclude_products" id="exclude_products" aria-describedby="exclude_productsHelp">
            <div id="exclude_productsHelp" class="form-text">products that will not be updated (ids comma separated)</div>
          </div>
          <div class="mb-3">
            <label for="include_products" class="form-label">Only for products</label>
            <input type="text" class="form-control" name="include_products" id="include_products" aria-describedby="include_productsHelp">
            <div id="include_productsHelp" class="form-text">only those products will be updated (ids comma separated)</div>
          </div>
          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" name="all_products" id="all_products">
            <label class="form-check-label" for="all_products">update all products (<small><i>even if they don't have a valability setted before)</label>
          </div>
          <button type="submit" class="btn btn-primary">Submit</button>
        </form>

        <?php if (!empty($status)): ?>
            <div class="alert alert-success" role="alert">
              <?php echo $status;?>
            </div>
        <?php endif;?>
    </div>

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="//cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>
    -->
  </body>
</html>