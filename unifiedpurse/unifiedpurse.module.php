<?php
function unifiedpurse_help($path, $arg) {
    $output = '<p>'.  t("Through UnifiedPurse, you can accept payments with, Bitcoin, Litecoin, Ethereum and over 80 alternatives");
    //    The line above outputs in ALL admin/module pages
    switch ($path) {
        case "admin/help/unifiedpurse":
        $output = '<p>'.  t("eCommerce - UnifiedPurse - Drupal Plugin.") .'</p>';
            break;
    }
    return $output;
} // function unifiedpurse_help

/**
 * Valid permissions for this module
 * @return array An array of valid permissions for the unifiedpurse module
 */
function unifiedpurse_perm() {
    return array('administer unifiedpurse');
} // function unifiedpurse_perm()

/**
 * Menu for this module
 * @return array An array with this module's settings.
 */
function unifiedpurse_menu() {
    $items = array();


      //Link to the sms_zone admin page:
    $items['unifiedpurse'] = array(
        'title' => 'UnifiedPurse (Bitcoin, Litecoin, Ethereum, 80+ alternatives)',
        'description' => 'UnifiedPurse - Drupal Plugin',

		'page callback'    => 'drupal_get_form',
        'page arguments'   => array('unifiedpurse_form'),

        'access arguments' => array('administer nodes'),
        'type' => MENU_NORMAL_ITEM,
    );
	

    return $items;
}




function unifiedpurse_form() {
   $form['merchant'] = array(
      '#type' => 'textfield', 
      '#title' => t('UnifiedPurse Username or Email'), 
      '#default_value' => variable_get('unifiedpurse_merchant',''), 
      '#description' => t(''),
      '#required' => TRUE
	  );

    $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save Changes'),
  );


	  return $form;
}


function unifiedpurse_form_submit(&$form, $form_state) {

$merchant=$form_state['values']['merchant'];

variable_set('unifiedpurse_merchant',$merchant);

drupal_set_message(t("Your changes were saved successfully."));
  
$form_state['redirect'] = 'unifiedpurse';
}


function unifiedpurse_nodeapi(&$node, $op, $a3 = NULL, $a4 = NULL){
	
		
	if($op=="alter")	 
	{
		$sitename  = variable_get('site_name', '');
 
         // Get the body
        $body = $node->body;

        // Regular expression to fetch the unifiedpurse tags
		$regex = '/{unifiedpurse\s*.*?}/i';
		preg_match_all( $regex, $body, $matches );

		
        // Fetch the default parameters
        $merchant_id= variable_get('unifiedpurse_merchant','');
			
			
		foreach($matches[0] as $key => $match) {

			$pattern = '/item\s*\((?<val>[^\(\)]+)\)/';
			preg_match($pattern, $match, $m);
			$item = $m['val'];
			
			$pattern = '/price\s*\((?<val>[^\(\)]+)\)/';
			preg_match($pattern, $match, $m);
			$price = $m['val'];
			
			$pattern = '/currency\s*\((?<val>[^\(\)]+)\)/';
			preg_match($pattern, $match, $m);
			$currency = $m['val'];
			
			$pattern = '/description\s*\((?<val>[^\(\)]+)\)/';
			preg_match($pattern, $match, $m);
			$description = empty($m['val']) ? $item.' at '.number_format($price,2) : $m['val'];
			
			$f = '<form method="POST" action="https://unifiedpurse.com/sci/">
			<input type="hidden" name="receiver" value="'.$merchant_id.'" />
			<input type="hidden" name="memo" value="'.$item.' ('.number_format($price,2).') order from '.$sitename.' ('.$description.')" />
			<input type="hidden" name="description_1" value="'.$description.'" />
			<input type="hidden" name="amount" value="'.$price.'" />
			<input type="hidden" name="currency" value="'.$currency.'" />
			<input type="submit" value="Pay with Bitcoin, Litecoin, Ethereum, 80+ alternatives (via UnifiedPurse)" />
			</form>';
			
	/*		
	<input type='hidden' name='notification_url' value='$notify_url' />
	<input type='hidden' name='success_url' value='$notify_url' />
	<input type='hidden' name='cancel_url' value='$notify_url' />
		*/	
            $body = str_replace($match,$f,$body);
		}
		
		$node->body=$body;
	}
}