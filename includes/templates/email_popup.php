<div class="email_popup_box" id="email_popup_box" style="display: none;">
    <form action="" id="getEmail" name="getEmail">
        <h3><?php echo $title; ?></h3>
        <div class="inputBox">
            <input type="text" class="regular-text" name="email_address" id="email_address" placeholder="Please enter email address" value=""><br>
            <p class="fade_font">Why? to use the trail period. Also, no worries, we hate spam too.</p>
        </div>
        <div class="inputBox">
            <input type="submit" name="submit_email" class="button button-primary button-large" id="submit_email" value="Submit">
        </div>
    </form>
</div>
<style type="text/css">
    .inputBox{ text-align: center;}
    .inputBox input[type="text"].error{ border-color: red;}
    .inputBox input[type="submit"]{ margin-top: 10px;}
    label.error{ color: red; font-weight: bold;}
</style>


<div id="confirm_popup" style="display: none; width: 350px;">
    <form name="confirm_payment" id="confirm_payment" action="" method="post">
        <div id="confirmMessage" style="text-align: center; display: none;"></div>
        <div class="input-row">
            <label> Do you have coupon code ? </label>
            <input type="radio" name="is_coupon_code" value="yes" class="is_coupon_code"> Yes
            <input type="radio" name="is_coupon_code" value="no" checked="checked" class="is_coupon_code"> No
        </div>
        <div class="input-row">
            <input type="text" placeholder="Enter coupon code here.." style="display: none;" name="check_coupon_code" id="check_coupon_code" value="">
            <input type="hidden" style="display: none;" name="paypal_url" id="paypal_url" value="paypal_payment">
        </div>
        <div class="input-row">
            <input type="submit" id="apply_coupon" value="Proceed" class="button button-primary button-large">
        </div>
    </form>
</div>