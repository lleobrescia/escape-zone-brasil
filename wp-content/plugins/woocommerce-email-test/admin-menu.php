<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

    if( isset($_GET['tab']) ){
        $tab = $_GET['tab'];
    } else {
        $tab = 'options';
    }
?>
    <div class="wrap">

		<h2>WooCommerce Email Test</h2>

        <h2 class="nav-tab-wrapper">
            <a href="?page=woocommerce-email-test&tab=options" class="nav-tab <?php echo $tab == 'options' ? 'nav-tab-active' : ''; ?>">Email Test</a>
            <a href="?page=woocommerce-email-test&tab=premium" class="nav-tab <?php echo $tab == 'premium' ? 'nav-tab-active' : ''; ?>">Go Premium</a>
        </h2>       



        <!-- options tab -->
        <?php if( $tab == 'options' ){ ?>
        
        
        
 		<?php 
		
		// update options if POST	
		wetp_update_test_email_options();
		
        // get option values
		$test_email_options = wetp_get_test_email_options();

		?>       
        
		
		<h3>Settings</h3>
		
		<form method="post" action=""> 
    
			<div class="form-field ">
				<label for="wc_email_test_order_id"><strong>Order ID</strong> for test email content (defaults to most recent if left blank)</label>	<br/>				
				<?php echo $order_id_select = wetp_get_order_id_select_field( $test_email_options['wc_email_test_order_id'] ); ?>						
			</div>	

			<?php wp_nonce_field( 'wept_update_form', 'nonce' ); ?>

			<p class="submit">
				<input id="submit" class="button button-primary" type="submit" value="Save Settings" name="submit"></input>
			</p>
			
		</form>
	
		<hr/>
		
		<h3>Email Preview</h3>
		<p>The below buttons will open a new tab containing a preview of the test email within your browser
			<br/>
			Note - test emails will not get sent to any inbox. 
            <br/>
            <br/>
            <b>Want to send emails to an inbox?</b> Purchase the <a href="http://raiserweb.com/product/woocommerce-email-test-premium-plugin-license/" target="_blank" >premium plugin</a> today.
		</p>
		
		<br/>
		
		<?php wetp_show_test_email_buttons(); ?>
        
        <?php } ?>
        <!-- .options tab -->
        
        
        
        
        <!-- premium tab -->
        <?php if( $tab == 'premium' ){ ?>
        
            <?php 
            // update options if POST	
            wetp_update_license_key();
            
            // get option values
            $test_email_options = wetp_get_license_options();            
            ?>
            
            <h2>Go Premium Today!</h2>
            
            <a href="http://raiserweb.com/product/woocommerce-email-test-premium-plugin-license/" class="button button-primary" target="_blank">Purchase A License ></a>
            
            <p>I would like to thank you for using this free plugin. Please rate it on our <a href="https://wordpress.org/support/plugin/woocommerce-email-test/reviews/" target="_blank">wordpress plugin page</a></p>

            <p>Why not upgrade to the premium version, for a small one off fee of only <b>&pound;14.99</b>? This fee helps support my work as a web developer in the wordpres community, to help make more great plugins.</p>
            
            <p>The premium plugin includes the following additional features:</p>

            <h4>Real email in-box testing</h4>
            <p>The premium version lets you send any of the test emails to an email address of you choice, at the click of a button. This allows you to test the email in a real inbox, instead of simply in the browser.</p>
            
            <h4>Additional testing for the following WooCommerce email types:</h4>
               
            <ol>
            <li>Cancelled Order</li>
            <li>Failed Order</li>
            <li>Customer On Hold Order</li>
            <li>Customer Refunded Order</li>
            <li>Customer Reset Password</li>
            <li>Customer New Account</li>
            </ol>

            <h4>Support for the WooCommerce Subscription plugin</h4>

            <p>If you have the WooCommerce subscription plugin, you can test the emails associated with this plugin. These are:</p>
            
            <ol>
            <li>New Renewal Order</li>
            <li>Completed Renewal Order</li>
            <li>Completed Switch Order</li>
            <li>Customer Renewal Invoice</li>
            </ol>
            
            <h4>Lifetime updates</h4>
            
            <p>Any updates to the premium plugin will be available to you at no extra cost.</p>
            
            <h2>Easy to buy</h2>
            
            <p>Purchasing a license key couldn't be easier. Simply click the button below. You will be taken to our website to make the purchase, and receive your unique license key.<p>
            <p>Then simply enter the license key into the licence key input box below, and click 'Save key'. If the license key is valid, you will be given the option to update this plugin to the premium plugin via the wordpress <a href="<?php echo admin_url('plugins.php');?>" >plugins menu</a>.  </p>
            
            <a href="http://raiserweb.com/product/woocommerce-email-test-premium-plugin-license/" class="button button-primary" target="_blank">Purchase A License ></a>
            
            <br/>
            <br/>
            <hr/>
            
            <form method="post" action="">
                     
                <div class="form-field ">
                    <label for="wc_email_test_license_key"><strong>License Key:</strong><br/>				
                    <input style="width:400px;" type="text" value="<?php echo $test_email_options['wetp_main_lisence_key']; ?>" name="wetp_main_lisence_key" >					
                </div>	            
                <p class="submit">
                    <input id="submit"  class="button button-primary" type="submit" value="Save Key" name="submit"></input>
                </p>
                
                <?php wp_nonce_field( 'wept_update_lisence_key', 'nonce' ); ?>

            </form>
            
            
            
        <?php } ?>
        <!-- .premium tab -->
        
        
    </div>