<?php
// File protection as per Zen-Cart suggestions
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
// EOF: File protection

// Only output the CharityClear Transaction information if it was recorded for this order!
if (isset($form_transaction_info->fields)) {

// Strip slashes in case they were added to handle apostrophes:
    foreach ($form_transaction_info->fields as $key => $value) {
        $form_transaction_info->fields[$key] = stripslashes($value);
    }

// Display all CharityClear Form status fields (in admin Orders page):
    $output = '<td><table>' . "\n";
    $output .= '<tr style="background-color : #cccccc; border-style : dotted;">' . "\n";
    $output .= '<td valign="top"><strong>CharityClear Info:</strong><table>' . "\n";

    $output .= '<tr><td class="main">' . "\n";
    $output .= "Transaction Unique Code\n";
    $output .= '</td><td class="main">' . "\n";
    $output .= $form_transaction_info->fields['transid'] . "\n";
    $output .= '</td></tr>' . "\n";
   $output .= '<tr><td class="main">' . "\n";
    $output .= "xref\n";
    $output .= '</td><td class="main">' . "\n";
    $output .= $form_transaction_info->fields['xref'] . "\n";
    $output .= '</td></tr>' . "\n";

    $output .= '<tr><td class="main">' . "\n";
    $output .= "Amount Received\n";
    $output .= '</td><td class="main">' . "\n";
    $output .= substr($form_transaction_info->fields['received'], 0, -2) . "." . substr($form_transaction_info->fields['received'], strlen($form_transaction_info->fields['received']) - 2, strlen($form_transaction_info->fields['received'])) . "\n";
    $output .= '</td></tr>' . "\n";

    $output .= '</table></td>' . "\n";



if(MODULE_PAYMENT_CHARITYCLEAR_CAPTURE_TYPE == 'Direct'){

   // if (method_exists($this, '_doRefund')) {
        $output .= '<td><table class="noprint">'."\n";
        $output .= '<tr style="background-color : #dddddd; border-style : dotted;">'."\n";
        $output .= '<td class="main">' . MODULE_PAYMENT_CHARITYCLEAR_REFUND_TITLE . '<br />'. "\n";
        $output .= zen_draw_form('charityclearRefund', FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=doRefund', 'post', '', true) . zen_hide_session_id();;

        $output .= MODULE_PAYMENT_CHARITYCLEAR_REFUND . '<br />';
        $output .= MODULE_PAYMENT_CHARITYCLEAR_REFUND_AMOUNT_TEXT . ' ' . zen_draw_input_field('refamt', '', 'length="8" placeholder="Enter Amount"') . '<br />';
        // confirm checkbox
        $output .= MODULE_PAYMENT_CHARITYCLEAR_REFUND_CONFIRM_CHECK . zen_draw_checkbox_field('refconfirm', '', false) . '<br />';
        //comment field
        $output .= '<br />' . MODULE_PAYMENT_CHARITYCLEAR_REFUND_TEXT_COMMENTS . '<br />' . zen_draw_textarea_field('refnote', 'soft', '50', '3', MODULE_PAYMENT_CHARITYCLEAR_REFUND_DEFAULT_MESSAGE);
        //message text
        $output .= '<br />' . MODULE_PAYMENT_CHARITYCLEAR_REFUND_SUFFIX;
        $output .= '<br /><input type="submit" name="buttonrefund" value="' . MODULE_PAYMENT_CHARITYCLEAR_REFUND_BUTTON_TEXT . '" title="' . MODULE_PAYMENT_CHARITYCLEAR_REFUND_BUTTON_TEXT . '" />';
        $output .= '</form>';
        $output .='</td></tr></table></td>'."\n";
  //  echo $outputRefund;
  //  }




        $output .= '<td valign="top"><table class="noprint">'."\n";
        $output .= '<tr style="background-color : #dddddd; border-style : dotted;">'."\n";
        $output .= '<td class="main">' . MODULE_PAYMENT_CHARITYCLEAR_CAPTURE_TITLE . '<br />'. "\n";
        $output .= zen_draw_form('lpapicapture', FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=doCapture', 'post', '', true) . zen_hide_session_id();
        $output .= MODULE_PAYMENT_CHARITYCLEAR_CAPTURE . '<br />';
        $output .= '<br />' . MODULE_PAYMENT_CHARITYCLEAR_CAPTURE_AMOUNT_TEXT . ' ' . zen_draw_input_field('captamt', '', 'length="8" placeholder="Enter Amount"') . '<br />';
         // confirm checkbox
        $output .= MODULE_PAYMENT_CHARITYCLEAR_CAPTURE_CONFIRM_CHECK . zen_draw_checkbox_field('captconfirm', '', false) . '<br />';
        //comment field
        $output .= '<br />' . MODULE_PAYMENT_CHARITYCLEAR_CAPTURE_TEXT_COMMENTS . '<br />' . zen_draw_textarea_field('captnote', 'soft', '50', '2', MODULE_PAYMENT_CHARITYCLEAR_CAPTURE_DEFAULT_MESSAGE);
        //message text

        $output .= '<br /><input type="submit" name="btndocapture" value="' . MODULE_PAYMENT_CHARITYCLEAR_CAPTURE_BUTTON_TEXT . '" title="' . MODULE_PAYMENT_CHARITYCLEAR_CAPTURE_BUTTON_TEXT . '" />';
        $output .= '</form>';
       $output .='</td></tr></table></td>'."\n";

}
    $output .= '</tr>' . "\n";

    $output .= '</table></td>' . "\n";


}
