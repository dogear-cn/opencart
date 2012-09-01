<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/payment.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
      <table class="form">
        <tr>
          <td><span class="required">*</span> <?php echo $entry_seller_email; ?></td>
          <td><input type="text" name="alipay_direct_seller_email" value="<?php echo $alipay_direct_seller_email; ?>" size="50" />
            <?php if ($error_email) { ?>
            <span class="error"><?php echo $error_email; ?></span>
            <?php } ?></td>
        </tr>
	<tr>
          <td><span class="required">*</span> <?php echo $entry_security_code; ?></td>
          <td><input type="text" name="alipay_direct_security_code" value="<?php echo $alipay_direct_security_code; ?>" size="50" />
	  <?php if ($error_secrity_code) { ?>
            <span class="error"><?php echo $error_secrity_code; ?></span>
            <?php } ?></td>
        </tr>
	<tr>
          <td><span class="required">*</span> <?php echo $entry_partner; ?></td>
          <td><input type="text" name="alipay_direct_partner" value="<?php echo $alipay_direct_partner; ?>" size="50" />
	  <?php if ($error_partner) { ?>
            <span class="error"><?php echo $error_partner; ?></span>
            <?php } ?></td>
        </tr>
        <tr>
            <td><span class="required">*</span> <?php echo $entry_currency_code; ?></td>
            <td><select name="alipay_direct_currency_code">
              <?php foreach ($currencies as $currency) { ?>
              <?php if ($currency['code'] == $alipay_direct_currency_code) { ?>
              <option value="<?php echo $currency['code']; ?>" selected="selected"><?php echo $currency['title']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $currency['code']; ?>"><?php echo $currency['title']; ?></option>
              <?php } ?>
              <?php } ?>
            </select></td>
        </tr>
        <tr>
          <td><span class="required">*</span> <?php echo $entry_order_status; ?></td>
          <td><select name="alipay_direct_order_status_id">
              <?php foreach ($order_statuses as $order_status) { ?>
              <?php if ($order_status['order_status_id'] == $alipay_direct_order_status_id) { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
              <?php } ?>
              <?php } ?>
            </select></td>
        </tr>

        <tr>
          <td><span class="required">*</span> <?php echo $entry_wait_buyer_pay; ?></td>
          <td><select name="alipay_direct_wait_buyer_pay">
              <?php foreach ($order_statuses as $order_status) { ?>
              <?php if ($order_status['order_status_id'] == $alipay_direct_wait_buyer_pay) { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
              <?php } ?>
              <?php } ?>
            </select></td>
        </tr>      
        <tr>
          <td><span class="required">*</span> <?php echo $entry_wait_seller_send; ?></td>
          <td><select name="alipay_direct_wait_seller_send">
              <?php foreach ($order_statuses as $order_status) { ?>
              <?php if ($order_status['order_status_id'] == $alipay_direct_wait_seller_send) { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
              <?php } ?>
              <?php } ?>
            </select></td>
        </tr>
        <tr>
          <td><span class="required">*</span> <?php echo $entry_wait_buyer_confirm; ?></td>
          <td><select name="alipay_direct_wait_buyer_confirm">
              <?php foreach ($order_statuses as $order_status) { ?>
              <?php if ($order_status['order_status_id'] == $alipay_direct_wait_buyer_confirm) { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
              <?php } ?>
              <?php } ?>
            </select></td>
        </tr>
        <tr>
          <td><span class="required">*</span> <?php echo $entry_trade_finished; ?></td>
          <td><select name="alipay_direct_trade_finished">
              <?php foreach ($order_statuses as $order_status) { ?>
              <?php if ($order_status['order_status_id'] == $alipay_direct_trade_finished) { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
              <?php } ?>
              <?php } ?>
            </select></td>
        </tr>  


        <tr>
          <td><?php echo $entry_status; ?></td>
          <td><select name="alipay_direct_status">
              <?php if ($alipay_direct_status) { ?>
              <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
              <option value="0"><?php echo $text_disabled; ?></option>
              <?php } else { ?>
              <option value="1"><?php echo $text_enabled; ?></option>
              <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
              <?php } ?>
            </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_sort_order; ?></td>
          <td><input type="text" name="alipay_direct_sort_order" value="<?php echo $alipay_direct_sort_order; ?>" size="1" /></td>
        </tr>
      </table>
    </form>
    </div>
  </div>
</div>
<?php echo $footer; ?> 