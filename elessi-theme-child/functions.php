<?php
//
// Recommended way to include parent theme styles.
// (Please see http://codex.wordpress.org/Child_Themes#How_to_Create_a_Child_Theme)
//  
add_action('wp_enqueue_scripts', 'theme_enqueue_styles', 998);

function theme_enqueue_styles() {
    wp_enqueue_style('elessi-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('elessi-child-style', get_stylesheet_uri());
}


/**
 * Reorder product data tabs
 */
add_filter( 'woocommerce_product_tabs', 'woo_reorder_tabs', 98 );
function woo_reorder_tabs( $tabs ) {

	$tabs['descriere']['priority'] = 5;			// Descriere first
	$tabs['specifications']['priority'] = 10;			// Detalii cadou second
	$tabs['additional_information']['priority'] = 15;	// Additional information third
	$tabs['reviews']['priority'] = 20;			// Reviews last


	return $tabs;
}

add_action( 'woocommerce_after_shop_loop_item', 'wc_add_short_description' );
/**
 * WooCommerce, Add Short Description to Products on Shop Page
 */
function wc_add_short_description() {
	global $product;

	?>
        <div itemprop="description">
            <?php echo apply_filters( 'woocommerce_short_description', $product->post-> post_excerpt ) ?>
        </div>
	<?php
}

add_filter('nasa_max_depth_main_menu', 'custom_max_depth_menu');

function custom_max_depth_menu($depth) {

return 4; // Return max depth menu – Default is 3

}

/** Add shop page to breadcrumbs structure**/
add_filter( 'woocommerce_get_breadcrumb', function($crumbs, $Breadcrumb){
        $shop_page_id = wc_get_page_id('shop'); //Get the shop page ID
        if($shop_page_id > 0 && !is_shop()) { //Check we got an ID (shop page is set). Added check for is_shop to prevent Home / Shop / Shop as suggested in comments
            $new_breadcrumb = [
                _x( 'Toate Experientele', 'breadcrumb', 'woocommerce' ), //Title
                get_permalink(wc_get_page_id('shop')) // URL
            ];
            array_splice($crumbs, 1, 0, [$new_breadcrumb]); //Insert a new breadcrumb after the 'Home' crumb
        }
        return $crumbs;
    }, 10, 2 );

/*Custom checkout fileds display*/
add_filter('woocommerce_order_details_after_order_table','th34r_filter_display_custom_field_in_thnku_page', 10, 1 ); 
function th34r_filter_display_custom_field_in_thnku_page( $order) {
    $field_array = array(
        'Formula de inceput:' => 'voucher_start', 
        'Formula de incheiere:' => 'voucher_end', 
        'Metoda de livrare:' => 'metoda_livrare', 
        'Destinatar:' => 'email_to', 
        'Email beneficiar:' => 'email_beneficiar', 
        'Data livrare:' => 'moment_livrare',
        'Ora livrare:' => 'ora_livrare'
    );
    $order_id = $order->get_id();
    ?>
      <table>
        <h4 style="font-size:17px;">Detalii comanda</h4>
          <?php
           foreach ($field_array as $label => $name) {
              $value = get_post_meta($order_id,$name,true) ;
              if($value) { ?>
                <tr>
                   <td style="font-size:14px;"><b><?php echo $label; ?></b></td>
                   <td style="font-size:14px;"><?php echo $value; ?></td>
                </tr> <?php
              }
            } ?>
      </table>
    <?php
}
/*Automated NEW Badge*/
add_filter('nasa_badges', 'childtheme_custom_badges', 5);
function childtheme_custom_badges($badges) {
    global $product;
    
    $product_id = $product->get_id();
    $custom_badge = '';

    /**
     * Default New Badge
     */
    $newness_days = 60;
    $created = strtotime($product->get_date_created());
    $badge_hot = nasa_get_product_meta_value($product_id, '_bubble_hot');
    if (!$badge_hot && ((time() - (60 * 60 * 24 * $newness_days)) < $created)) {
        $custom_badge .= '' . esc_html__( 'New', 'elessi-theme' ) . '';
    }
    
    return $custom_badge . $badges;
}

//---RPK START---//
add_action( 'woocommerce_after_checkout_form', 'rpk_checkout_show_preview_voucher_js');
function rpk_checkout_show_preview_voucher_js() {
    $vdesc = '';
    $show = false;
    $bannedCateg = [
        'bijuterii', 'globuri-craciun','gourmet',
        'martisoare', 'cadouri-simbolice', 'abonamente'
    ];
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        $product = $cart_item['data'];
        if(!empty($product)){
            $pcateg = get_the_terms( $product->get_id(), 'product_cat' );
            foreach ($pcateg as $categ) {
                if(in_array($categ->slug, $bannedCateg)){
                    return;
                }
            }

            $item_data = apply_filters( 'woocommerce_get_item_data', $item_data, $cart_item );
            $xtrainfo = '';
            $xtrainfoarr = [];
            if(is_array($item_data)){
                foreach($item_data as $idata){
                    $field_display_value = wp_kses_post( force_balance_tags( $idata['value'] ) );
                    $field_display_value = str_replace('<p>','',$field_display_value);
                    $field_display_value = str_replace('</p>','',$field_display_value);
                    $field_display_value = preg_replace("/\([^)]+\)/","",$field_display_value);
                    $xtrainfoarr[] = $field_display_value;
                }
                $xtrainfo = implode(PHP_EOL, $xtrainfoarr);
            }
            $vintro = $product->get_meta('_voucher_intro');
            $voutro = $product->get_meta('_voucher_outro');
            $vdesc = $product->get_meta('_voucher_desc');
            if(empty($vdesc)){
                $vdesc = $product->get_description();
            }
            $vsupplier = $product->get_meta('_voucher_supplier');
            $vvalabil = $product->get_meta('_voucher_valability');
            echo '<span id="hpdesc" style="display:none">'.stripslashes( $vdesc ).'</span>';
            echo '<span id="hpvintro" style="display:none">'.$vintro.'</span>';
            echo '<span id="hpvoutro" style="display:none">'.$voutro.'</span>';
            echo '<span id="hpvsupplier" style="display:none">'.$vsupplier.'</span>';
            echo '<span id="hpvvalabil" style="display:none">'.$vvalabil.'</span>';
            echo '<span id="hpvexpinfo" style="display:none">'.$vexpinfo.'</span>';
            echo '<span id="hpvextrainfo" style="display:none">'.$xtrainfo.'</span>';
            $show = true;
            break;
        }
    }

    echo '<button id="preview_voucher" style="display:none">Vezi draft voucher</button>';
    echo '<form id="form_prev_voucher"  style="display:none" action="/rpk/voucher.php" method="POST" target="VoucherPreview">';
        echo '<input type="hidden" id="vintro" name="intro"/>';
        echo '<input type="hidden" id="voutro" name="outro"/>';
        echo '<textarea id="vdesc" name="description"></textarea>';
        echo '<input type="hidden" id="vvintro" name="vintro"/>';
        echo '<input type="hidden" id="vvoutro" name="voutro"/>';
        echo '<input type="hidden" id="vsupplier" name="supplier"/>';
        echo '<input type="hidden" id="vvalabil" name="valability"/>';
        echo '<input type="hidden" id="vexpinfo" name="experiencesdetail"/>';
        echo '<input type="hidden" id="vadinfo" name="additional_info"/>';
        echo '<input type="hidden" id="vextrainfo" name="extra_info"/>';
        echo '<input type="hidden" name="preview" value="true"/>';
    echo '</form>';

    $script = <<<RPK
<script>

const vstart = document.getElementById('voucher_start');
const vend = document.getElementById('voucher_end');
const btnvpreview = jQuery("#preview_voucher");
vstart.value = '';
vend.value = '';

jQuery("#voucher_titlu_field").append(jQuery("#preview_voucher"));

jQuery("#vdesc").val(jQuery("#hpdesc").html());
jQuery("#hpdesc").remove();

jQuery("#vvintro").val(jQuery("#hpvintro").html());
jQuery("#hpvintro").remove();

jQuery("#vvoutro").val(jQuery("#hpvoutro").html());
jQuery("#hpvoutro").remove();

jQuery("#vsupplier").val(jQuery("#hpvsupplier").html());
jQuery("#hpvsupplier").remove();

jQuery("#vvalabil").val(jQuery("#hpvvalabil").html());
jQuery("#hpvvalabil").remove();

jQuery("#vexpinfo").val(jQuery("#hpvexpinfo").html());
jQuery("#hpvexpinfo").remove();

jQuery("#vextrainfo").val(jQuery("#hpvextrainfo").html());
jQuery("#hpvextrainfo").remove();

vstart.addEventListener('blur', (event) => {
    showPreviewVoucherButton();
  }, true);

vend.addEventListener('blur', (event) => {
    showPreviewVoucherButton();
  }, true);

function showPreviewVoucherButton(){
    console.log(vstart.value.length, vend.value.length);
    if(vstart.value.length > 1 && vend.value.length > 1){
        btnvpreview.show();
    }else{
        btnvpreview.hide();
    }
}

btnvpreview.click(function(e){
    e.preventDefault();
    jQuery("#vintro").val(vstart.value);
    jQuery("#voutro").val(vend.value);
    var formpv = jQuery("#form_prev_voucher");
    prevoucher = window.open("", "VoucherPreview", "width=800,height=600,resizable=yes");
    formpv.submit();
});

</script>
RPK;
    echo $script;
}

add_filter( 'woocommerce_checkout_fields', 'add_delivery_time' );
function add_delivery_time( $checkout_fields ) {
	$checkout_fields['billing']['ora_livrare']['priority'] = 90;
	return $checkout_fields;
}

// First Register the Tab by hooking into the 'woocommerce_product_data_tabs' filter
add_filter( 'woocommerce_product_data_tabs', 'add_my_custom_product_data_tab' );
function add_my_custom_product_data_tab( $product_data_tabs ) {
	$product_data_tabs['my-custom-tab'] = array(
		'label' => __( 'Voucher', 'my_text_domain' ),
		'target' => 'my_custom_product_data',
	);
	return $product_data_tabs;
}

/*Next provide the corresponding tab content by hooking into the 'woocommerce_product_data_panels' action hook
See https://github.com/woothemes/woocommerce/blob/master/includes/admin/meta-boxes/class-wc-meta-box-product-data.php
for more examples of tab content
See https://github.com/woothemes/woocommerce/blob/master/includes/admin/wc-meta-box-functions.php for other built-in
functions you can call to output text boxes, select boxes, etc. */
add_action( 'woocommerce_product_data_panels', 'add_my_custom_product_data_fields' );
function add_my_custom_product_data_fields() {
	global $woocommerce, $post;
	?>
	<!-- id below must match target registered in above add_my_custom_product_data_tab function -->
	<div id="my_custom_product_data" class="panel woocommerce_options_panel">
		<?php
        $script = <<<RPK
        
        <script type="text/javascript">

        const btnvpreview = jQuery("#btn_preview_voucher");
        function previewvoucher()
        {
            jQuery("#vintro").val(jQuery("#_voucher_intro").val());
            // jQuery("#vdesc").val(tinymce.get("_voucher_desc").getContent());
            jQuery("#vdesc").val(jQuery('[name="_voucher_desc"]').val());
            jQuery("#voutro").val(jQuery("#_voucher_outro").val());
            jQuery("#vsupplier").val(jQuery("#_voucher_supplier").val());
            jQuery("#vvalabil").val(jQuery("#_voucher_valability").val());
            var formpv = jQuery('<form>', {
                "id": "form_prev_voucher",
                "html": jQuery("#div_prev_voucher").html(),
                "action": "/rpk/voucher.php",
                "method": "POST",
                "target": "VoucherPreview"
            }).appendTo(document.body);
            prevoucher = window.open("", "VoucherPreview", "width=800,height=600,resizable=yes");
            formpv.submit();
        }

        btnvpreview.click(function(e){
            e.preventDefault();
            previewvoucher();
        });
        
        </script>
        
        <div id="div_prev_voucher"  style="display:none" action= method="POST" target="VoucherPreview">
            <input type="hidden" id="vintro" name="intro"/>
            <input type="hidden" id="vdesc" name="description"/>
            <input type="hidden" id="voutro" name="outro"/>
            <input type="hidden" id="vsupplier" name="supplier"/>
            <input type="hidden" id="vvalabil" name="valability"/>
            <input type="hidden" name="preview" value="true"/>
            <input type="hidden" name="debug" value="true"/>
        </div>

RPK;
        echo $script;
		woocommerce_wp_textarea_input( array( 
			'id'            => '_voucher_intro', 
			'wrapper_class' => 'show_if_simple', 
			'label'         => __( 'Formula inceput voucher', 'my_text_domain' ),
			'description'   => __( 'Text introdus de client', 'my_text_domain' ),
			// 'default'  		=> '0',
			'desc_tip'    	=> false,
		) );
        
        $product = wc_get_product($post->ID);
        $content  = $product->get_meta( '_voucher_desc' );
        
        $options = array(
            'textarea_rows' => 6,
            // 'tinymce'       => array(
            //     'inline'           => true,
            // ),
        );
        wp_editor( stripslashes( $content ), '_voucher_desc', $options );
        woocommerce_wp_textarea_input( array( 
			'id'            => '_voucher_outro', 
			'wrapper_class' => 'show_if_simple', 
			'label'         => __( 'Formula incheiere voucher', 'my_text_domain' ),
			'description'   => __( 'Text introdus de client', 'my_text_domain' ),
			// 'default'  		=> '0',
			'desc_tip'    	=> false,
		) );
        woocommerce_wp_textarea_input( array( 
			'id'            => '_voucher_supplier', 
			'wrapper_class' => 'show_if_simple', 
			'label'         => __( 'Detalii tehnice', 'my_text_domain' ),
			'description'   => __( 'Info despre parteneri, adresa, program de lucru, nr de persoane, etc...', 'my_text_domain' ),
			// 'default'  		=> '0',
			'desc_tip'    	=> false,
		) );
        woocommerce_wp_text_input( array( 
			'id'            => '_voucher_valability', 
			'wrapper_class' => 'show_if_simple', 
			'label'         => __( 'Valabilitate voucher', 'my_text_domain' ),
			'description'   => __( 'Voucher valability info', 'my_text_domain' ),
			// 'default'  		=> '0',
			'desc_tip'    	=> false,
		) );
        $vexperiences  = $product->get_meta( '_voucher_experiences' );
        $dataid = 0;
        if(!empty($vexperiences)){
            $vexperiences = json_decode($vexperiences,true);
            $nestedids = [];
            
            $expHTML = '';
            foreach($vexperiences as $exp){
                $nestedids[$dataid] = [];
                $expHTML .= '<li>';
                $expHTML .= '<input class="ddexp" type="text" name="exp['.$dataid.'][name]" value="'. $exp['name'] .'">';
                if(0 == $dataid){
                    $expHTML .= '<a href="#!" class="addexp" onclick="addme(this, \'experiences_tpl\')">add</a>';
                }else{
                    $expHTML .= '<a href="#!" class="delexp" onclick="removeme(this, 0)">del</a>';
                }
                                
                $expHTML .= '<ul data-id="'.$dataid.'" id="nested" class="nested" style="padding-left: 10px;margin-top: -30px;">';
                $idx = 0;
                foreach ($exp['exp'] as $subexp){
                    $expHTML .= '<li>';
                    $expHTML .= '<input class="subexpname" type="text" name="exp['.$dataid.'][exp]['.$idx.'][name]" value="'. $subexp['name'] .'">';
                    $expHTML .= '<input class="subexpsup" type="text" name="exp['.$dataid.'][exp]['.$idx.'][supplier]" value="'. $subexp['supplier'] .'">';
                    if(0 == $idx){
                        $expHTML .= '<a href="#!" class="addsupexp" onclick="addme(this, \'nested_tpl\')">add</a>';
                    }
                    else{
                        $expHTML .= '<a href="#!" class="addsupexp" onclick="addme(this, \'nested_tpl\')">add</a> ';
                        $expHTML .= '<a href="#!" class="delsupexp" onclick="removeme(this, 1)">del</a>';
                    }
                    
                    $expHTML .= '</li>';
                    $nestedids[$dataid][] = $idx;
                    $idx++;
                }
                $expHTML .= '</ul>';
                $expHTML .= '</li>';
                $dataid++;
            }
            $dataid--;
        }else{
            $nestedids = [
                0 => [0]
            ];
            $expHTML = <<<RPK
                <li>
                    <input class="ddexp" type="text" name="exp[0][name]" placeholder="exp dropdown">
                    <a href="#!" class="addexp" onclick="addme(this, 'experiences_tpl')">add</a>
                    <ul data-id="0" id="nested" class="nested" style="padding-left: 10px;margin-top: -30px;">
                        <li>
                            <input class="subexpname" type="text" name="exp[0][exp][0][name]" placeholder="name">
                            <input class="subexpsup" type="text" name="exp[0][exp][0][supplier]" placeholder="supplier">
                            <a href="#!" class="addsupexp" onclick="addme(this, 'nested_tpl')">add</a>
                        </li>
                    </ul>
                </li>
RPK;
        }
        $jsonNestedIds = json_encode($nestedids);
        
        $htmlExp =  <<<RPK
        <div class="form-field experiences">
            <ul data-id="0" id="experiences">
            $expHTML
            </ul>
        </div>
RPK;
        $htmlExpConfig =  <<<RPK
        <div style="display: none;">
            <ul data-id="rpkid">
                <li id="experiences_tpl">
                    <input class="ddexp" type="text" name="exp[rpkid][name]" placeholder="exp dropdown">
                    <a href="#!" class="delexp" onclick="removeme(this, 0)">del</a>
                    <ul data-id="rpkid" class="nested">
                        <li id="nested_tpl">
                            <input class="subexpname" type="text" name="exp[rpkid][exp][rpksubid][name]" placeholder="name">
                            <input class="subexpsup" type="text" name="exp[rpkid][exp][rpksubid][supplier]" placeholder="supplier">
                            <a href="#!" class="addsupexp" onclick="addme(this, 'nestedtpl')">add</a>
                            <a href="#!" class="delsupexp" onclick="removeme(this, 1)">del</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
        <style>
        #experiences, .nested { 
            padding: 0;
            display: grid;
            width: 80%;
            margin: auto;
          }
          
          #experiences li{
            display: block;
          }
          
          .nested li {
            display: block;
            border: 1px solid #CCC;
            padding: 5px 10px;
            float: left;
          }
          </style>
        <script>

            var id = $dataid;
            var nestedids = $jsonNestedIds;

            function addme(el, tplid){
                var idx, mainidx;
                var parent = jQuery(el).closest("ul");
                if(tplid == 'experiences_tpl'){
                    idx = 0;
                    id++;
                    nestedids[id] = [0];
                    mainidx = id;
                }else{
                    tplid = 'nested_tpl';
                    mainidx = parent.data('id');
                    idx = nestedids[mainidx].length;
                    nestedids[mainidx].push(idx);
                }
                
                var toappend = jQuery('#'+tplid).clone();
                toappend.removeAttr('id');
                var html = document.getElementById(tplid).innerHTML;
                html = html.replace(/nested_tpl/g, 'nested_tpl'+mainidx+idx);
                html = html.replace(/rpkid/g, mainidx);
                html = html.replace(/rpksubid/g, idx);
                toappend.html(html);
                parent.append(toappend);
                return false; //prevent going top
            }

            function removeme(el,child){
                var idx, mainidx;
                var parent = jQuery(el).closest("li");
                // mainidx = parent.closest("ul").data('id');
                if(child == 1){
                    // nestedids[mainidx].pop();
                }else{
                    delete nestedids[id];
                    // id--;
                }
                parent.remove();
                return false; //prevent going top
            }
        </script>
RPK;

        echo $htmlExp;
        echo $htmlExpConfig;
        echo '<a href="javascript:void();" style="margin: 10px;" onclick="previewvoucher();" id="btn_preview_voucher" class="button save_voucher button-primary">preview voucher</a>';
        echo '<br>';
		?>
	</div>
	<?php

}

add_action('woocommerce_process_product_meta', function($post_id) {
    $product = wc_get_product($post_id);
    
    $product->update_meta_data('_voucher_intro', $_POST['_voucher_intro']);
    $product->update_meta_data('_voucher_desc', $_POST['_voucher_desc']);
    $product->update_meta_data('_voucher_outro', $_POST['_voucher_outro']);
    $product->update_meta_data('_voucher_supplier', $_POST['_voucher_supplier']);
    $product->update_meta_data('_voucher_valability', $_POST['_voucher_valability']);
    $vexperiences = $_POST['exp'];
    if(is_array($vexperiences)){
        unset($vexperiences['rpkid']);
        $product->update_meta_data('_voucher_experiences', json_encode($vexperiences));
    }
    
    $product->save();
});

add_filter( 'woocommerce_email_attachments', 'attach_pdf_file_to_customer_completed_email', 10, 3);
function attach_pdf_file_to_customer_completed_email( $attachments, $email_id, $order ) {
    $bannedCateg = [
        'bijuterii', 'globuri-craciun','gourmet',
        'martisoare', 'cadouri-simbolice', 'abonamente'
    ];
    if( isset( $email_id ) &&  in_array($email_id, ['customer_completed_order', 'customer_processing_order']) ){
        $order_status = $order->get_status();
        if(!in_array( $order_status,['completed','processing'] ) ){
            return;
        }
        $order_id = $order->get_order_number();
        $pdf = get_stylesheet_directory() . '/vouchers/order_'.$order_id.'.pdf';
       
        $vdesc = $order->get_meta('voucher_desc');
        $items = $order->get_items();
        $xtrainfoarr = [];
        foreach ( $items as $item ) {
            $product_id = $item->get_product_id();
            $pcateg = get_the_terms( $product_id, 'product_cat' );
            foreach ($pcateg as $categ) {
                if(in_array($categ->slug, $bannedCateg)){
                    return;
                }
            }
            $meta_data = $item->get_formatted_meta_data( '' );
            foreach ( $meta_data as $meta_id => $meta ){
                $field_display_value = wp_kses_post( force_balance_tags( $meta->display_value ) );
                $field_display_value = str_replace('<p>','',$field_display_value);
                $field_display_value = str_replace('</p>','',$field_display_value);
                $field_display_value = preg_replace("/\([^)]+\)/","",$field_display_value);
                $xtrainfoarr[] = $field_display_value;
            }
            
            if(empty($vdesc)){
                $product = wc_get_product($product_id);
                $vdesc = $product->get_description();
            }
            
            break;
        }
        $stripdesc = stripslashes( $vdesc );
        $data = [
            'intro' => $order->get_meta('voucher_start'),
            'outro' => $order->get_meta('voucher_end'),
            'description' => $stripdesc,
            'supplier' => $order->get_meta('voucher_supplier'),
            'valability' => $order->get_meta('voucher_valability'),
            'extra_info' => implode(PHP_EOL, $xtrainfoarr),
            'oid' => $order_id,
        ];

        rpk_download_voucher('https://complice.ro/rpk/voucher.php', $data, $pdf);
        $attachments[] = $pdf; // Child theme
    }
    return $attachments;
}

//---SAVE VOUCHER FIELDS ON ORDER
add_action('woocommerce_checkout_update_order_meta', 'update_order_with_voucher' , 10, 2);
function update_order_with_voucher($order_id, $posted){
    // logy(json_encode($posted));
    $order = wc_get_order( $order_id );
    $items = $order->get_items();
    logy($items);
    foreach ( $items as $item ) {
        $product_id = $item->get_product_id();
        $product = wc_get_product($product_id);
        break;
    }

    $vdesc = $product->get_meta('_voucher_desc');
    if(empty($vdesc)){
        $vdesc = $product->get_description();
    }
    $vsupplier = $product->get_meta('_voucher_supplier');
    $vvalabil = $product->get_meta('_voucher_valability');
    $vexperiences  = $product->get_meta( '_voucher_experiences' );

    $order->update_meta_data( 'voucher_supplier', $vsupplier );
    $order->update_meta_data( 'voucher_desc', $vdesc );
    $order->update_meta_data( 'voucher_valability', $vvalabil );
    $order->update_meta_data( '_voucher_experiences', $vexperiences );
    $email_beneficiar = $posted['email_beneficiar'];

    if(!empty($email_beneficiar)){
        // logy($email_beneficiar);
        $order->update_meta_data( '_voucher_for_cron', 1 );
    }else{
        // logy('unable to get email_beneficiar');
    }
    $order->save();
}

//---DOWNLOAD VOUCHER BUTTON ON ORDER

// Add a custom metabox only for shop_order post type (order edit pages)
add_action( 'add_meta_boxes', 'add_meta_boxesws' );
function add_meta_boxesws(){
    add_meta_box( 'custom_order_meta_box', __( 'Voucher' ),
        'custom_metabox_content', 'shop_order', 'normal', 'default');
}

function custom_metabox_content(){
    
    $order_id = isset($_GET['post']) ? $_GET['post'] : false;
    if(! $order_id ) return; // Exit

    $order = wc_get_order( $order_id );

    $show = preshow_download_voucher_btn($order_id, $order);
    
    if($show){
        echo '<a href="javascript:void(0);" style="margin: 10px;" onclick="downloadvouchera();" id="btn_download_voucher" class="button save_voucher button-primary">Download voucher</a>';
    }
}

function preshow_download_voucher_btn($order_id, $order){

    $bannedCateg = [
        'bijuterii', 'globuri-craciun','gourmet',
        'martisoare', 'cadouri-simbolice', 'abonamente'
    ];

    $vintro = $order->get_meta('voucher_start');
    $voutro = $order->get_meta('voucher_end');
    $vsupplier = $order->get_meta('voucher_supplier');
    $vvalabil = $order->get_meta('voucher_valability');
    $vexperiences  = $order->get_meta( '_voucher_experiences' );
    $vdesc = $order->get_meta('voucher_desc');
    
    $items = $order->get_items();
    $xtrainfoarr = [];
    foreach ( $items as $item ) {
        $product_id = $item->get_product_id();
        $pcateg = get_the_terms( $product_id, 'product_cat' );
        foreach ($pcateg as $categ) {
            if(in_array($categ->slug, $bannedCateg)){
                return false;
            }
        }
        $meta_data = $item->get_formatted_meta_data( '' );
        foreach ( $meta_data as $meta_id => $meta ){
            $field_display_value = wp_kses_post( force_balance_tags( $meta->display_value ) );
            $field_display_value = str_replace('<p>','',$field_display_value);
            $field_display_value = str_replace('</p>','',$field_display_value);
            $field_display_value = preg_replace("/\([^)]+\)/","",$field_display_value);
            $xtrainfoarr[] = $field_display_value;
        }
        
        if(empty($vdesc)){
            $product = wc_get_product($product_id);
            $vdesc = $product->get_description();
        }
        
        break;
    }

    $xtrainfo = implode(PHP_EOL, $xtrainfoarr);
    
    $stripdesc = stripslashes( $vdesc );

    echo '<span id="hpvintro" style="display:none">'.$vintro.'</span>';
    echo '<span id="hpvoutro" style="display:none">'.$voutro.'</span>';
    echo '<span id="hpvdesc" style="display:none">'.$stripdesc.'</span>';
    echo '<span id="hpvsupplier" style="display:none">'.$vsupplier.'</span>';
    echo '<span id="hpvvalabil" style="display:none">'.$vvalabil.'</span>';
    echo '<span id="hpvoid" style="display:none">'.$order_id.'</span>';
    echo '<span id="hpvextrainfo" style="display:none">'.$xtrainfo.'</span>';
    
    $script = <<<RPK
        
        <script type="text/javascript">

        function downloadvouchera()
        {
            jQuery("#vintro").val(jQuery("#hpvintro").html());
            jQuery("#voutro").val(jQuery("#hpvoutro").html());
            jQuery("#vdesc").val(jQuery("#hpvdesc").html());
            jQuery("#vsupplier").val(jQuery("#hpvsupplier").html());
            jQuery("#vvalabil").val(jQuery("#hpvvalabil").html());
            jQuery("#vextrainfo").val(jQuery("#hpvextrainfo").html());
            jQuery("#void").val(jQuery("#hpvoid").html());

            var formpv = jQuery('<form>', {
                "id": "form_download_voucher",
                "html": jQuery("#div_download_voucher").html(),
                "action": "/rpk/voucher.php",
                "method": "POST",
                "target": "VoucherDownload"
            }).appendTo(document.body);
            downloadvoucher = window.open("", "VoucherDownload", "width=800,height=600,resizable=yes");
            formpv.submit();
        }
        
        </script>
        
        <div id="div_download_voucher"  style="display:none" action= method="POST" target="VoucherDownload">
            <input type="hidden" id="vintro" name="intro"/>
            <input type="hidden" id="voutro" name="outro"/>
            <textarea id="vdesc" name="description">$stripdesc</textarea>
            <input type="hidden" id="vsupplier" name="supplier"/>
            <input type="hidden" id="vvalabil" name="valability"/>
            <input type="hidden" id="vextrainfo" name="extra_info"/>
            <input type="hidden" id="void" name="oid"/>
        </div>

RPK;
    
    echo $script;

    return true;
}

function display_voucher_reserve_experiences_form()
{
	$order_id = isset($_GET['order_number']) ? $_GET['order_number'] : false;
    if(! $order_id ) return; // Exit

    $order_id = sanitize_text_field( $order_id );
    $order = wc_get_order( $order_id );
    if($order){
        $bannedCateg = [
            'bijuterii', 'globuri-craciun','gourmet',
            'martisoare', 'cadouri-simbolice', 'abonamente'
        ];

        $items = $order->get_items();
        $xtrainfoarr = [];
        foreach ( $items as $item ) {
            $product_id = $item->get_product_id();
            $pcateg = get_the_terms( $product_id, 'product_cat' );
            foreach ($pcateg as $categ) {
                if(in_array($categ->slug, $bannedCateg)){
                    echo '<div class="lt-col large-12 columns">';
                    esc_html_e( 'Aceasta comanda nu a avut un vocher asociat pentru care sa fie nevoie de programare.', 'woocommerce' );
                    echo '</div>';
                    return;
                }
            }
        }
    }
    
    $vexperiences = get_post_meta( $order_id, '_voucher_experiences', true );
    if(empty($vexperiences)){
        // fallback to the old workflow
        $js = <<<RPK
        <script type="text/javascript">
            jQuery(document).ready(function(){
                var el = jQuery("input[name='voucher']");
                el.val($order_id);
                el.parent().parent().parent().hide();
            });
        </script>
RPK;
        echo $js;
        echo do_shortcode('[contact-form-7 id="11478"]');
        return;
    }
    $script = <<<RPK
        
    	<script type="text/javascript">
            jQuery(document).ready(function(){
                var el = jQuery("input[name='voucher']");
                el.val($order_id);
                el.parent().parent().parent().hide();
                jQuery("input[name='tel-596']").parent().parent().parent().after(jQuery("#experience_fieldset"));
                var experienta = jQuery("input[name='experienta']");
                experienta.parent().parent().parent().hide();
                jQuery('#experiences').prop('selectedIndex', 0);
            });

            function program_experience(el){
                var val = (el.value || el.options[el.selectedIndex].value);
                jQuery('.subexperience').hide();
                jQuery('#subexperiences'+val).prop('selectedIndex', 0).show();
                jQuery("input[name='experienta']").val();
            }
            function program_subexperience(el,oidx){
                var val = (el.value || el.options[el.selectedIndex].value);
                if(val == 0 || val > 0){
                    jQuery("input[name='experienta']").val(el.options[el.selectedIndex].text);
                    jQuery("input[name='voucheroption']").val(oidx + ':' + val);

                    var bookdata = jQuery(el).find(":selected").data('bookeda');
                    if(bookdata){
                        var booked = jQuery('<textarea />').html(bookdata).text();
                        var bookedObj = JSON.parse(booked);
                        Object.keys(bookedObj).forEach(function(key,index) {
                            if(key == 'interval1' || key == 'interval2'){
                                val = bookedObj[key][0];
                                jQuery("select[name='"+key+"']").val(val).attr('disabled', true);
                            }else{
                                val = bookedObj[key];
                                jQuery("input[name='"+key+"']").val(val).attr('readonly', true);
                            }
                        });
                        jQuery("#form-submit").hide();
                    }else{
                        jQuery("select[name='interval1']").val('').attr('disabled', false);
                        jQuery("select[name='interval2']").val('').attr('disabled', false);
                        jQuery("input[name='date-951']").val('').attr('readonly', false);
                        jQuery("input[name='date-952']").val('').attr('readonly', false);
                        jQuery("input[name='detalii']").val('').attr('readonly', false);
                        jQuery("#form-submit").show();
                    }

                }else{
                    
                }
            }

            function schedule_show(){
                var el = jQuery("input[name='date-951']");
                el.val();
                var parent = el.parent().parent().parent().parent();
                parent.show();
                parent.prev().show();

                el = jQuery("input[name='date-952']");
                el.val();
                el.parent().parent().parent().parent().show();

                el = jQuery("input[name='detalii']");
                el.val();
                el.parent().parent().parent().parent().show();
                jQuery(".acceptance-470").show();
                jQuery(".acceptancemk-471").show();
            }

            function schedule_hide(){
                var el = jQuery("input[name='date-951']");
                el.val();
                var parent = el.parent().parent().parent().parent();
                parent.hide();
                parent.prev().hide();

                el = jQuery("input[name='date-952']");
                el.val();
                el.parent().parent().parent().parent().hide();

                el = jQuery("input[name='detalii']");
                el.val();
                el.parent().parent().parent().parent().hide();
                jQuery(".acceptance-470").hide();
                jQuery(".acceptancemk-471").hide();
            }
    	</script>
RPK;
    
    echo $script;

    $experiences = json_decode($vexperiences, true);
    $alreadyBookedIdx = -1;
    foreach($experiences as $expidx => $exp){
        if( !empty($exp['booked']) ) {
            $alreadyBookedIdx = $expidx;
            break;
        }
    }
    $expselect = '<select name="experience" id="experiences" onchange="program_experience(this)" class="wpcf7-form-control wpcf7-select wpcf7-validates-as-required">';
    $expselect .= '<option>Selecteaza optiune</option>';
    $subexphtml = [];
    foreach($experiences as $expidx => $exp){
        
        $mainattr = '';
        if($alreadyBookedIdx >= 0 && $expidx != $alreadyBookedIdx){
            $mainattr = 'disabled';
        }
        $expselect .= '<option '.$mainattr.' value="'. $expidx.'">'. $exp['name'].'</option>';
        $subexpselect = '<select style="display:none" name="subexperience" id="subexperiences'. $expidx.'" onchange="program_subexperience(this, '. $expidx.')" class="subexperience wpcf7-form-control wpcf7-select wpcf7-validates-as-required">';
        $subexpselect .= '<option>Selecteaza experienta</option>';
        foreach($exp['exp'] as $childexpidx => $childexp){
            $tmpattr = '';
            $addvaltext = '';
            if(!empty($childexp['booked'])){
                $json = json_encode($childexp['booked']);
                $tmpattr = 'data-bookeda="'.htmlentities(htmlspecialchars($json, ENT_QUOTES, 'UTF-8')).'"';
                $addvaltext = '(deja solicitata)';
            }
            $subexpselect .= '<option '.$tmpattr.' value="'. $childexpidx.'">'. $childexp['name'].' '.$addvaltext.'</option>';
        }
        $subexpselect .= '</select>';
        $subexphtml[] = $subexpselect;
    }
    $expselect .= '</select>';
    echo '<div id="experience_fieldset" class="lt-col large-12 columns">';
    echo $expselect;
    echo implode(PHP_EOL, $subexphtml);
    echo '</div>';
    echo '<div id="contactform721" style="display:block">';
    echo do_shortcode('[contact-form-7 id="11478"]');
    echo '</div>';

}

// Shotcode that display the form and output order details once submitted
add_shortcode( 'voucher_reserve_experiences_form', 'form_get_voucher_experiences' );
function form_get_voucher_experiences(){
    $order_id = isset($_GET['order_number']) ? $_GET['order_number'] : false;
    ob_start(); // Buffering data

    ?>
    <form action="" method="get">
        <label for="order_number">ID-ul voucherului</label><br>
        <input type="text" name="order_number" value="<?php echo $order_id;?>" size="30"><br>
        <input type="submit" id="submit" value="Incarca"><br>
    </form>
    <?php

    display_voucher_reserve_experiences_form();

    return ob_get_clean(); // Output data from buffer
}

// send email to supplier
function wpcf7_before_send_mail_function( $contact_form, $abort, $submission ) {

    $data = $submission->get_posted_data();
    $oid = $data['voucher'];
    $bookedoption = $data['voucheroption'];
    $hasEO = false;
    if(!empty($oid) && !empty($bookedoption)){
        // check if has EO
        $order = wc_get_order( $oid );
        if(!$order){
            return $contact_form;
        }
        $items = $order->get_items();
        foreach ( $items as $item ) {
            $meta_data = $item->get_formatted_meta_data( '' );
            foreach ( $meta_data as $meta_id => $meta ){
                $field_display_value = wp_kses_post( force_balance_tags( $meta->display_value ) );
                if($field_display_value){
                    $hasEO = true;
                    break;
                }
            }
            
            if($hasEO){
                break;
            }
        }

        $vexperiences = get_post_meta( $oid, '_voucher_experiences', true );
        if(!empty($vexperiences)){
            $option = explode(':', $bookedoption);
            if(count($option) == 2){
                $experiences = json_decode($vexperiences, true);
                $bookexp = $experiences[$option[0]];
                /* here we can store booked details */
                $experiences[$option[0]]['booked'] = true;
                $experiences[$option[0]]['exp'][$option[1]]['booked'] = $data;
                if($hasEO){
                    $supplier = 'office@complice.ro';
                }else{
                    $supplier = $bookexp['exp'][$option[1]]['supplier'];
                }
                if($supplier){
                    $properties = $contact_form->get_properties();
                    $additionalHeader = $properties['mail']['additional_headers'];
                    if(!empty($additionalHeader)){
                        $additionalHeader .= PHP_EOL . 'Cc: '. $supplier;
                    }else{
                        $additionalHeader = 'Cc: '. $supplier;
                    }
                    $properties['mail']['additional_headers'] = $additionalHeader;
                    $contact_form->set_properties($properties);
                    update_post_meta( $oid, '_voucher_experiences',  json_encode($experiences));
                }
            }
        }
    }
    return $contact_form;
  
  }
  add_filter( 'wpcf7_before_send_mail', 'wpcf7_before_send_mail_function', 10, 3 );


// DOWNLOAD VOUCHER ON MY ACCOUNT
add_action('woocommerce_order_details_after_order_table', 'myacc_download_voucher');

function myacc_download_voucher($order){

    $order_id = $order->get_order_number();
    $order_status = $order->get_status();
    if(!in_array( $order_status,['completed','processing'] ) ){
        return;
    }

    $show = preshow_download_voucher_btn($order_id, $order);

    if($show){
        echo '<a href="javascript:void(0);" style="margin: 10px;" onclick="downloadvouchera();" id="btn_download_voucher" class="button save_voucher button-primary">Download voucher</a>';
    }
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function(){
            jQuery('.order-again').append(jQuery('#btn_download_voucher'));
        });
    </script>
    <?php

}

add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', 'handle_order_number_custom_query_var', 10, 2 );
function handle_order_number_custom_query_var( $query, $query_vars ) {
    if ( ! empty( $query_vars['voucher_for_cron'] ) ) {
        $query['meta_query'][] = array(
            'key' => '_voucher_for_cron',
            'value' => esc_attr( $query_vars['voucher_for_cron'] ),
        );
    }

    return $query;
}

// bypass spam filter
// add_filter('wpcf7_spam', '__return_false');

//---UTILS ---//
function rpk_download_voucher($Url, $Data, $filename)
{
    $fp = fopen($filename, 'wb');
    // $verbose = fopen('php://temp', 'w+');
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $Url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_FILE => $fp,
      CURLOPT_HEADER => 0,
      // CURLOPT_VERBOSE => true,
      // CURLOPT_STDERR => $verbose,
    //   CURLOPT_PROXY => $_SERVER['SERVER_ADDR'] . ':' .  $_SERVER['SERVER_PORT'],
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSL_VERIFYSTATUS => false,
      CURLOPT_CUSTOMREQUEST => 'POST', //
      CURLOPT_POSTFIELDS => http_build_query($Data),
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/x-www-form-urlencoded'
      ),
    ));

    $result = curl_exec($curl);

    // file_put_contents($filename.'.log', 'voucher generated'.PHP_EOL, FILE_APPEND);
    // if (!curl_errno($curl)) {
    //     $info = curl_getinfo($curl);
    //     file_put_contents($filename.'.log',var_export($info, true), FILE_APPEND);
    //     file_put_contents($filename.'.log', __FILE__.'::'.__LINE__.PHP_EOL, FILE_APPEND);
    // }else{
    //     file_put_contents($filename.'.log',PHP_EOL.htmlspecialchars(curl_error($curl)), FILE_APPEND);
    //     file_put_contents($filename.'.log', __FILE__.'::'.__LINE__.PHP_EOL, FILE_APPEND);
    // }
    // if ($result === FALSE) {
    //     // printf("cUrl error (#%d): %s<br>\n", curl_errno($curl), htmlspecialchars(curl_error($curl)));
    //     file_put_contents($filename.'.log',PHP_EOL.htmlspecialchars(curl_error($curl)), FILE_APPEND);
    //     file_put_contents($filename.'.log', __FILE__.'::'.__LINE__.PHP_EOL, FILE_APPEND);
    //     rewind($verbose);
    //     $verboseLog = stream_get_contents($verbose);

    //     // echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
    //     file_put_contents($filename.'.log', PHP_EOL.htmlspecialchars($verboseLog), FILE_APPEND);
    //     file_put_contents($filename.'.log', __FILE__.'::'.__LINE__.PHP_EOL, FILE_APPEND);
    // }else{
    //     // echo $result;
    //     file_put_contents($filename.'.data',var_export($result, true));
    // }

    curl_close($curl);

    // Close file
    fclose($fp);

    // exit(__FILE__.'::'.__LINE__);

    return;
}

function logy( $data, $overwrite = false, $filename = null) {
    $path = get_stylesheet_directory() . '/logs/';

    if( empty($filename) ){
        $d = date('Y-m-d');
        $filename = $d.'_log.txt';
    }
    
    if(!$overwrite){
        file_put_contents($path.$filename, $data, FILE_APPEND);
    }else{
        file_put_contents($path.$filename, $data);
    }
    
}
//---RPK END---//