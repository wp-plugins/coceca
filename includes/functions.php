<?php
function coceca_plugin_path( $path = '' ) {
    return path_join( COCECA_PLUGIN_DIR, trim( $path, '/' ) );
}

function coceca_plugin_url( $path = '' ) {
    $url = untrailingslashit( COCECA_PLUGIN_URL );

    if ( ! empty( $path ) && is_string( $path ) && false === strpos( $path, '..' ) )
        $url .= '/' . ltrim( $path, '/' );

    return $url;
}

function coceca_upload_dir( $type = false ) {
    $uploads = wp_upload_dir();

    $uploads = apply_filters( 'coceca_upload_dir', array(
        'dir' => $uploads['basedir'],
        'url' => $uploads['baseurl'] ) );

    if ( 'dir' == $type )
        return $uploads['dir'];
    if ( 'url' == $type )
        return $uploads['url'];

    return $uploads;
}


function activate_url($menu,$plugin_id='',$plugin='',$plugin_name='',$plugin_source='',$activate_path=''){
    //$encrpted_string = syonencryptor('encrypt',getHost().':'.absint($_GET['plugin_id']));
    $plugin_path = explode('/',$activate_path);
    $url = wp_nonce_url(
        add_query_arg(
            array(
                'page'          => $menu,
                'plugin_id'     => $plugin_id,
                'plugin'        => $plugin_path[0],
                'plugin_name'   => $plugin_name,
                //'plugin_source' => EXT_SITE_URL.'coceca/wordpress_plugins/CTA/CTA1.1/mtb-call-to-action.zip',
                'plugin_source' => trim($plugin_source),
                'mtb-install' => 'install-plugin',
            ),
            network_admin_url( 'admin.php' )
        ),
        'mtb-install'
    );
    return $url;
}

function plugin_activate($menu,$plugin_id='',$plugin_name='',$plugin_source='',$activate_path=''){
    $plugin_path = explode('/',$activate_path);
    $url = wp_nonce_url(
        add_query_arg(
            array(
                'page'          => $menu,
                //'plugin_id'     => $plugin_id,
                'plugin'        => $plugin_path[0],
                'plugin_name'   => $plugin_name,
                'mtb-activate' => 'activate-plugin',
            ),
            network_admin_url( 'admin.php' )
        ),
        'mtb-activate'
    );
    return $url;
}

function checkPluginSourceExists($file_exists){
    if(!empty($file_exists)){
        $plugin_exists = explode('/',$file_exists);
        $plugin_data = get_plugins( '/' . $plugin_exists[0] );
        if(!empty($plugin_data)){
            return true;
        }
        else{
            return false;
        }
    }else{
        return false;
    }
}


function checkDomainExists(){
    $result = '';
    $api_data = array('is_json'=>'1','token'=>'VXJ6dpIpZELStgGoxXqtYh34lIpF1sQn','check_host'=>getHost());
    $result = CallAPI('GET',EXT_SITE_URL.'wpapi/checkDomainExists/',$api_data);
    $results = json_decode($result,true);
    return $results;
}

function checkDomainIs_boolean(){
    $result = '';
    $api_data = array('is_json'=>'1','token'=>'VXJ6dpIpZELStgGoxXqtYh34lIpF1sQn','check_host'=>getHost());
    $result = CallAPI('GET',EXT_SITE_URL.'wpapi/checkDomainIs_boolean/',$api_data);
    $results = json_decode($result,true);
    return $results;
}


function isActivatePlugin($m_p_id,$plugin_id,$user_id){
    $api_data = array(
        'is_json'=>'1',
        'token'=>'VXJ6dpIpZELStgGoxXqtYh34lIpF1sQn',
        'm_p_id'=>$m_p_id,
        'plugin_id'=>$plugin_id,
        'user_id'=>$user_id
    );
    $result = CallAPI('GET',EXT_SITE_URL.'wpapi/isActivatePlugin/',$api_data);
    return json_decode($result,true);
}

function list_extentions(){
    $results = CallAPI('GET',EXT_SITE_URL.'wpapi/list_extentions/',array('is_json'=>'1','token'=>'VXJ6dpIpZELStgGoxXqtYh34lIpF1sQn','check_host'=>getHost()));
    $result_items = json_decode($results,true);
    return $result_items;
}

function insertPluginData($data){
    $api_data = array(
        'is_json'=>'1',
        'token'=>'VXJ6dpIpZELStgGoxXqtYh34lIpF1sQn',
    );
    $insertData = array_merge($data,$api_data);
    $result = CallAPI('GET',EXT_SITE_URL.'wpapi/insertPluginData/',$insertData);
}

function checkTrialExpired($plugin_id=1){
    $api_data = array(
        'is_json'=>'1',
        'plugin_id'=>$plugin_id,
        'token'=>'VXJ6dpIpZELStgGoxXqtYh34lIpF1sQn',
        'check_host'=>getHost(),
    );
    $result = CallAPI('GET',EXT_SITE_URL.'wpapi/trialExpired/',$api_data);
    return json_decode($result,true);
}

function CallAPI($method, $url, $data = false)
{
    $curl = curl_init();
    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

   //echo $url; die;

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-Requested-With:XMLHttpRequest'));

    $result = curl_exec($curl);

    if($result === false)
    {
        echo 'Curl error: ' . curl_error($curl);
    }else{
        return $result;
    }
    curl_close($curl);
}

function getHost(){
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $main_uri =  $protocol.$_SERVER['HTTP_HOST'].'/';
    return $main_uri;
}

// Function to get the client IP address
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function InsertActivateDownload($mpid='',$cpid=''){
    $api_data = array(
        'is_json'=>'1',
        'token'=>'VXJ6dpIpZELStgGoxXqtYh34lIpF1sQn',
        'host_name'=>getHost(),
        'host_ip'=>get_client_ip(),
        'm_p_id' =>$mpid,
        'c_p_id' =>$cpid,
    );

    $result = CallAPI('GET',EXT_SITE_URL.'wpapi/insert_activate_download/',$api_data);
    return json_decode($result,true);
}
function UpdateActivateDownload($mpid='',$cpid=''){
    $api_data = array(
        'is_json'=>'1',
        'token'=>'VXJ6dpIpZELStgGoxXqtYh34lIpF1sQn',
        'host_name'=>getHost(),
        'host_ip'=>get_client_ip(),
        'm_p_id' =>$mpid,
        'c_p_id' =>$cpid,
    );
    $result = CallAPI('GET',EXT_SITE_URL.'wpapi/update_activate_download/',$api_data);
    return json_decode($result,true);
}

function user_activate_deactivate_plugins($m_p_id='',$user_id,$activate_deactive){
    $api_data = array(
        'is_json'=>'1',
        'token'=>'VXJ6dpIpZELStgGoxXqtYh34lIpF1sQn',
        'host_name'=>getHost(),
        'm_p_id' =>$m_p_id,
        'user_id' =>$user_id,
        'is_activated' =>$activate_deactive
    );
    $result = CallAPI('GET',EXT_SITE_URL.'wpapi/user_activate_deactivate_plugins/',$api_data);
    //print_r($result); die;
    return json_decode($result,true);
}

function coceca_active_deactive($m_p_id='',$user_id='',$activate_deactive){
    $api_data = array(
        'is_json'=>'1',
        'token'=>'VXJ6dpIpZELStgGoxXqtYh34lIpF1sQn',
        'host_name'=>getHost(),
        'm_p_id' =>$m_p_id,
        'user_id' =>$user_id,
        'is_activated' =>$activate_deactive
    );
    $result = CallAPI('GET',EXT_SITE_URL.'wpapi/coceca_active_deactive/',$api_data);
    return json_decode($result,true);
}

function syonencryptor($action, $string)
{
    $output = false;
    $encrypt_method = "AES-256-CBC";
    //pls set your unique hashing key
    $secret_key = 'SyonSuperKey';
    $secret_iv = 'Infomedia'.date('Y');
    // hash
    $key = hash('sha256', $secret_key);
    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    //do the encyption given text/string/number
    if( $action == 'encrypt' ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    }
    else if( $action == 'decrypt' ){
        //decrypt the given text/string/number
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }

    return $output;
}

function checkValidCoupon($coupon_code){
    $api_data = array(
        'is_json'=>'1',
        'token'=>'VXJ6dpIpZELStgGoxXqtYh34lIpF1sQn',
        'coupon_code'=>$coupon_code,
    );
    $result = CallAPI('GET',EXT_SITE_URL.'wpapi/checkValidCoupon/',$api_data);
    return json_decode($result,true);
}

if(!function_exists('toPublicId')){
    function toPublicId($id)
    {
        return $id * 14981488888 + 8259204988888;
    }
}

if (!function_exists('toInternalId')) {
    function toInternalId($publicId)
    {
        return ($publicId - 8259204988888) / 14981488888;
    }
}