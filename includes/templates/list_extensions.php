<?php
    global $current_user;
    $classes = 'welcome-panel';

    $boolean_domain_exixts = checkDomainIs_boolean();
    $results_domain = checkDomainExists();
?>

<?php if(!empty($results_domain)): ?>
<p class="display_email">Your registered email address :- <b><?php echo (!empty($results_domain)) ? $results_domain['email_address'] : ''; ?></b> <a target="_blank" href="<?php echo EXT_SITE_URL.'user/login'; ?>">Click here to CoCeCa member area</a></p>
<?php endif; ?>
<?php if(empty($results_domain['email_address'])): ?>
    <p class="submit_email"><a href="javascript:void(0)" onclick="getUserEmail();"><b>Click here to get a full access to CoCeCa member area</b></a></p>
<?php endif; ?>
<div id="welcome-panel" class="<?php echo esc_attr( $classes ); ?>">
    <p>CTA plugin is an innovative solution designed to help you grow your WordPress blog. It creates an opportunity for you to promote your WordPress Websites & Blogs and engage your site visitors, in more ways than one.</p>
</div>

<div class="extentions_lists" id="all_extentions">

    <?php $result_items = list_extentions(); ?>

    <ul>
        <?php if(!empty($result_items)) {
            foreach($result_items as $plugins){
                $is_activated = isActivatePlugin($results_domain['id'],$plugins['id'],$results_domain['user_id']);
                ?>
                <li class="mtb-addons-extension <?php echo (is_plugin_active($plugins['activate_path']) && (isset($is_activated['is_activated']) && $is_activated['is_activated'] == '1')) ? 'active' : 'inactive'?>">
                    <div class="content top cf">
                        <h2><?php echo $plugins['plugin_title']; ?></h2>
                        <p><?php echo $plugins['plugin_content']; ?></p>
                    </div>
                    <div class="bottom cf">
                        <?php
                        $plugin_url = $plugin_page_url = $plugin_activate_url='';
                        if(!empty($plugins['plugin_source'])){
                            $plugin_slug = strtolower(str_replace(' ','-',$plugins['plugin_title']));
                            $plugin_url = activate_url($this->menu,$plugins['id'],$plugin_slug,$plugins['plugin_title'],$plugins['plugin_source'],$plugins['activate_path']);

                            $plugin_activate_url = plugin_activate($this->menu,$plugin_slug,$plugins['plugin_title'],$plugins['plugin_source'],$plugins['activate_path']);
                          //  echo $plugin_activate_url;
                        }

                        if(!empty($plugins['plugin_page_url'])){
                            $plugin_page_url = admin_url($plugins['plugin_page_url']);
                        }else{
                            $plugin_page_url = admin_url('plugins.php');
                        }

                        if(!is_plugin_active($plugins['activate_path'])){ ?>
                            <?php if($boolean_domain_exixts!=1) { ?>
                                <a href="javascript:void(0)" onclick="getUserEmail();" class="install_now button"> Install Now </a>
                             <?php }else{?>
                                <?php if(checkPluginSourceExists($plugins['activate_path'])) {  ?>
                                    <a href="<?php echo $plugin_activate_url; ?>" class="install_now button button-primary"> Activate </a>
                                <?php }else{ ?>
                                    <a href="<?php echo $plugin_url; ?>" class="install_now button"> Install Now </a>
                                <?php } ?>
                            <?php } ?>
                        <?php }else{ ?>
                            <?php if(!checkTrialExpired($plugins['id'])){ ?>
                                <a href="<?php echo $plugin_page_url;?>" class="installed button"> Manage Plugin </a>
                            <?php }else{ ?>
                                <!--<a href="<?php /*echo wp_nonce_url(admin_url('admin-ajax.php?action=cta_gopro&plugin_id=1'),'gopro-CTA_1', 'com_nonce'); */?>" class="button"> Go For Pro </a>-->
                                <a href="<?php echo wp_nonce_url(admin_url('admin-ajax.php?action=coceca_gopro&plugin_id='.$plugins['id'].''),'gopro-CoCeCa_'.$plugins['id'], 'com_nonce'); ?>" data-id="<?php echo $plugins['id']; ?>" class="upgrade_goForPro button button-primary button-large"> <?php echo '$'.$plugins['price']; ?> Lifetime</a>
                            <?php } ?>
                        <?php } ?>
                    </div>

                </li>
            <?php } }?>

        <div class="clear"></div>
    </ul>
</div>

<p class="support_text_cta">
    <a target="_blank" href="<?php echo COCECA_SITE_URL; ?>">Get Started</a>&nbsp; | &nbsp; <a target="_blank" href="<?php echo COCECA_SITE_URL.'help/'; ?>">Support</a>
</p>
<style>
    .submit_email{ text-align: right;} .submit_email a{ text-decoration: none;}
</style>
<?php include_once('email_popup.php'); ?>