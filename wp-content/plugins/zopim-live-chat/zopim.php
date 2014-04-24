<?php

/*
Plugin Name: Zopim Widget
Plugin URI: http://www.zopim.com/?iref=wp_plugin
Description: Zopim is an award winning chat solution that helps website owners to engage their visitors and convert customers into fans!
Author: Zopim
Version: 1.2.8
Author URI: http://www.zopim.com/?iref=wp_plugin
*/

define('ZOPIM_SCRIPT_DOMAIN',         "zopim.com");
define('ZOPIM_BASE_URL',              "https://www.zopim.com/");
define('ZOPIM_SIGNUP_REDIRECT_URL',   ZOPIM_BASE_URL."?aref=MjUxMjY4:1TeORR:9SP1e-iPTuAVXROJA6UU5seC8x4&visit_id=6ffe00ec3cfc11e2b5ab22000a1db8fa&utm_source=account%2Bsetup%2Bpage&utm_medium=link&utm_campaign=wp%2Bsignup2#signup");
define('ZOPIM_GETACCOUNTDETAILS_URL', ZOPIM_BASE_URL."plugins/getAccountDetails");
define('ZOPIM_SETDISPLAYNAME_URL',    ZOPIM_BASE_URL."plugins/setDisplayName");
define('ZOPIM_SETEDITOR_URL',    	  ZOPIM_BASE_URL."plugins/setEditor");
define('ZOPIM_LOGIN_URL',             ZOPIM_BASE_URL."plugins/login");
define('ZOPIM_SIGNUP_URL',            ZOPIM_BASE_URL."plugins/createTrialAccount");
define('ZOPIM_DASHBOARD_URL',         "http://dashboard.zopim.com/?utm_source=wp&utm_medium=iframe&utm_campaign=wp%2Bdashboard");
define('ZOPIM_DASHBOARD_LINK',        "http://dashboard.zopim.com/?utm_source=wp&utm_medium=link&utm_campaign=wp%2Bdashboard");
define('ZOPIM_THEMEEDITOR_URL',       "http://dashboard.zopim.com/#Widget/appearance");
define('ZOPIM_THEMEEDITOR_LINK',      "http://dashboard.zopim.com/#Widget/appearance");
define('ZOPIM_SMALL_LOGO',            "http://zopim.com/assets/branding/zopim.com/chatman/online.png");

require_once dirname( __FILE__ ) . '/accountconfig.php';

function add_zopim_caps() {
	$role = get_role( 'administrator' );
	$role->add_cap( 'access_zopim' );
}

add_action( 'admin_init', 'add_zopim_caps');


// We need some CSS to position the paragraph
function zopimme() {
	global $current_user, $zopimshown;
	get_currentuserinfo();

	$code = get_option('zopimCode');	

	if (($code == "" || $code=="zopim") && (!ereg("zopim", $_GET["page"]))&& (!ereg("zopim", $_SERVER["SERVER_NAME"]))) { return; }

	// dont show this more than once
	if (isset($zopimshown) && $zopimshown == 1) { return; }
	$zopimshown = 1;

	echo "<!--Start of Zopim Live Chat Script-->
<script type=\"text/javascript\">
window.\$zopim||(function(d,s){var z=\$zopim=function(c){z._.push(c)},$=z.s=
d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set.
_.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute('charset','utf-8');
$.src='//v2.zopim.com/?".$code."';z.t=+new Date;$.
type='text/javascript';e.parentNode.insertBefore($,e)})(document,'script');
</script>";

	echo '<script>';
	if (isset($current_user)):
		$firstname = $current_user->display_name;
		$useremail = $current_user->user_email;
		if ($firstname!="" && $useremail != ""):
			echo "\$zopim(function(){\$zopim.livechat.set({name: '$firstname', email: '$useremail'}); });";
		endif;
	endif;

	echo zopim_get_widget_options();
	echo '</script>';
	echo "<!--End of Zopim Live Chat Script-->";
}

function zopim_get_widget_options() {
	$opts = get_option('zopimWidgetOptions');
	if ($opts) return stripslashes($opts);

	//$opts = zopim_old_plugin_settings();
	$zopim_embed_opts .= "\$zopim( function() {";
	$zopim_embed_opts .= "\n})";
	$opts = $zopim_embed_opts;

	update_option('zopimWidgetOptions', $opts);

	$list = array(
		'zopimLang',
		'zopimPosition',
		'zopimTheme',
		'zopimColor',
		'zopimUseGreetings',
		'zopimUseBubble',
		'zopimBubbleTitle',
		'zopimBubbleText',
		'zopimBubbleEnable',
		'zopimHideOnOffline'
	);

	foreach ($list as $key):
		delete_option($key);
	endforeach;

	if ($opts) return $opts; 
	else return '';
}


function zopim_old_plugin_settings() {
	$theoptions = array();

	if (get_option('zopimLang') != "" && get_option('zopimLang') != "--")
		$theoptions[] = " language: '".get_option('zopimLang')."'";

	$zopim_embed_opts = '';
	$zopim_embed_opts .= "\$zopim( function() {";

	if (count($theoptions) > 0)
		$zopim_embed_opts .= '$zopim.livechat.set({'.implode(", ", $theoptions)."});";

	if (get_option('zopimPosition')) $zopim_embed_opts .= "\n\$zopim.livechat.button.setPosition('".get_option('zopimPosition')."');";
	if (get_option('zopimTheme'))    $zopim_embed_opts .= "\n\$zopim.livechat.window.setTheme('".get_option('zopimTheme')."');";
	if (get_option('zopimColor'))    $zopim_embed_opts .= "\n\$zopim.livechat.window.setColor('".get_option('zopimColor')."');";

	if (get_option('zopimUseGreetings') == "zopimUseGreetings") {
		if (get_option('zopimGreetings') != "") {
			$greetings = json_to_array(get_option('zopimGreetings'));
			foreach ($greetings as $i => $v) {
			 foreach ($v as $j => $k) {
				$greetings->$i->$j = str_replace("\r\n", "\\n", $greetings->$i->$j);
				}
			}
			$zopim_embed_opts .= "\n\$zopim.livechat.setGreetings({
'online' : ['".addslashes($greetings->online->bar)."', '".addslashes($greetings->online->window)."'],
'offline': ['".addslashes($greetings->offline->bar)."', '".addslashes($greetings->offline->window)."'],
'away'   : ['".addslashes($greetings->away->bar)."', '".addslashes($greetings->away->window)."']  });";
		}
	}

	if (get_option('zopimUseBubble') == "zopimUseBubble") {
		if (get_option('zopimBubbleTitle')) $zopim_embed_opts .= "\n\$zopim.livechat.bubble.setTitle('".addslashes(get_option('zopimBubbleTitle'))."');";
		if (get_option('zopimBubbleText'))  $zopim_embed_opts .= "\n\$zopim.livechat.bubble.setText('".addslashes(get_option('zopimBubbleText'))."');";
	}

	if (get_option('zopimBubbleEnable') == "show")
		$zopim_embed_opts .= "\n\$zopim.livechat.bubble.show(true);";
	else if (get_option('zopimBubbleEnable') == "hide")
		$zopim_embed_opts .= "\n\$zopim.livechat.bubble.hide(true);";

	// this must be called last
	if (get_option('zopimHideOnOffline') == "zopimHideOnOffline")
		$zopim_embed_opts .= "\n\$zopim.livechat.button.setHideWhenOffline(true);";

	$zopim_embed_opts .= "\n})";	
	return $zopim_embed_opts;

}

function zopim_create_menu() {
	//create new top-level menu
	add_menu_page('Account Configuration', 'Zopim Chat', 'access_zopim', 'zopim_account_config', 'zopim_account_config', ZOPIM_SMALL_LOGO);

	// add_submenu_page('zopim_about', "About", "About", "access_zopim", 'zopim_about', 'zopim_about');
	add_submenu_page('zopim_account_config', 'Account Configuration', 'Account Setup', 'access_zopim', 'zopim_account_config', 'zopim_account_config');
	add_submenu_page('zopim_account_config', 'Customize Widget', 'Customize', 'access_zopim', 'open_zopim_customize', 'open_zopim_customize');
	add_submenu_page('zopim_account_config', 'Dashboard', 'Dashboard', 'access_zopim', 'open_zopim_dashboard', 'open_zopim_dashboard');

	//call register settings function
	add_action( 'admin_init', 'register_zopim_plugin_settings' );
}

function zopim_about() {
	echo "about";
}

function zopim_resize_iframe($target) {

?>
<script>
(function() {
	var wpwrap = document.getElementById('wpwrap');
	var ztarget = document.getElementById('<?php echo $target; ?>');
	// window.addEventListener('resize', zopim_resize, false);
	function zopim_resize() {
		if (wpwrap && wpwrap.clientHeight) {
			ztarget.height = Math.max(wpwrap.clientHeight - 110, 700);
		}
	}
	zopim_resize();
})()
</script>

<?php
}

function zopim_customize_widget() {

	$params = '';
	$code = get_option('zopimCode');
	//if (!empty($code)) $params .= '&account_key=' . urlencode($code);	
	//$params .= '&url=' . urlencode(get_site_option('siteurl'));
	echo '<div id="dashboarddiv" style="overflow:hidden;"><iframe id="dashboard-widget" src="'.ZOPIM_THEMEEDITOR_URL.'" height=700 width=110% scrolling="no" style="margin-left:-180px;"></iframe></div>';
	echo 'You may also <a href="'.ZOPIM_THEMEEDITOR_LINK.$params.'" target="customize" onclick="javascript:document.getElementById(\'dashboarddiv\').innerHTML=\'\'; ">access the theme editor in a new window</a>.';
	zopim_resize_iframe('dashboard-widget');
}

function zopim_dashboard() {
	global $current_user;

	if (isset($current_user)):
		$useremail = $current_user->data->user_email;
	endif;
	// Get Blog's URL

	echo '<div id="dashboarddiv"><iframe id="dashboardiframe" src="'.ZOPIM_DASHBOARD_URL.'" height=700 width=98% scrolling="no"></iframe></div>';
	echo 'You may also <a href="'.ZOPIM_DASHBOARD_LINK.'" target="dashboard" onclick="javascript:document.getElementById(\'dashboarddiv\').innerHTML=\'\'; ">access the dashboard in a new window</a>.';
	zopim_resize_iframe('dashboardiframe');
}

function open_zopim_dashboard() {
	echo "<script type='text/javascript'>
		var window = window.open('".ZOPIM_DASHBOARD_LINK."', '_blank', 'width=1000,height=800');
		if (window){
			window.focus();
		} else{
			alert('Zopim Plugin: Please allow popups for this site');
		}
		window.location = '".home_url()."/wp-admin'
		</script>";
}

function open_zopim_customize() {
	echo "<script type='text/javascript'>
		var window = window.open('".ZOPIM_THEMEEDITOR_LINK."', '_blank', 'width=1000,height=800');
		if (window){
			window.focus();
		} else{
			alert('Zopim Plugin: Please allow popups for this site');
		}
		window.location = '".home_url()."/wp-admin'
		</script>";
}

// Register the option settings we will be using
function register_zopim_plugin_settings() {

	// Authentication and codes
	register_setting( 'zopim-settings-group', 'zopimCode' );
	register_setting( 'zopim-settings-group', 'zopimUsername' );
	register_setting( 'zopim-settings-group', 'zopimSalt' );
	register_setting( 'zopim-settings-group', 'zopimUseSSL' );

}

add_action('get_footer', 'zopimme');
// create custom plugin settings menu
add_action('admin_menu', 'zopim_create_menu');

function zopim_post_request($url, $_data, $optional_headers = null)
{
	if (get_option('zopimUseSSL') != "zopimUseSSL")
		$url = str_replace("https", "http", $url);
	
	$args = array('body' => $_data);
	$response = wp_remote_post( $url, $args );	
	return $response['body'];
}

function zopim_url_get($filename) {
	$response = wp_remote_get($filename);
	return $response['body'];
}

function json_to_array($json) {
	require_once('JSON.php');
	$jsonparser = new Services_JSON();
	return ($jsonparser->decode($json));
}

function to_json($variable) {
	require_once('JSON.php');
	$jsonparser = new Services_JSON();
	return ($jsonparser->encode($variable));
}

function getAccountDetails($salt) {
	$salty = array("salt" => get_option('zopimSalt'));
	return json_to_array(zopim_post_request(ZOPIM_GETACCOUNTDETAILS_URL, $salty));
}

function setEditor($salt) {
	$salty = array("salt" => get_option('zopimSalt'));
	return json_to_array(zopim_post_request(ZOPIM_SETEDITOR_URL, $salty));
}

?>