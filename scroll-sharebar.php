<?php
/*
Plugin Name: Scrolling Social Sharebar (Twitter Like Google +1 Linkedin and Stumbleupon)
Plugin URI: http://techxt.com/scrolling-social-sharebar-plugin/
Description: Scrolling Social Sharebar (Twitter Like Google +1 Linkedin and Stumbleupon)
Version: 1.7
Author: Sudipto Pratap Mahato
Author URI: http://techxt.com
*/

$dispssbar = FALSE;
$mainloop =FALSE;
function main_loop_test($query) {
  global $wp_the_query;
  global $mainloop;
  if ($query === $wp_the_query) {
    $mainloop=TRUE;
  }else{ $mainloop=FALSE;}
}
add_action('loop_start', 'main_loop_test');

function disp_ssharebar($content) {
global $dispssbar;
global $post;
global $mainloop;
if($mainloop==FALSE)return $content;
$plink = get_permalink($post->ID);
$eplink = urlencode($plink);
$ptitle = get_the_title($post->ID);
$expostid=str_replace(' ','',get_option('ssbar_excludeid',''));
$expostcat=str_replace(' ','',get_option('ssbar_excludecat',''));
if($expostid!=''){
	$pids=explode(",",$expostid);
	if (in_array($post->ID, $pids)) {
    		return $content;
	}
	$psttype=get_post_type($post->ID);
	if (in_array($psttype, $pids)&&$psttype!==flase) {
    		return $content;
	}
	$pstfrmt=get_post_format($post->ID);
	if (in_array($pstfrmt, $pids)&&$pstfrmt!==false) {
    		return $content;
	}
}
if($expostcat!=''){
	$pcat=explode(",",$expostcat);
	if (in_category($pcat)) {
    		return $content;
	}
}
if(is_home() && $dispssbar==FALSE && get_option('ssbar_dhome','checked')=='checked')
{
	$sharelinks=disp_ssharebar_func();
	$content=$sharelinks.$content;
	$dispssbar=TRUE;
}
if((is_single()&&get_option('ssbar_dpost','checked')=='checked')||(is_page()&&get_option('ssbar_dpage','checked')=='checked')){
	$sharelinks=disp_ssharebar_func();
	$content=$sharelinks.$content;
	$dispssbar=TRUE;
}

return $content;
}


function ssharebar_css() {
if (get_option('ssbar_mob', false)==true && ssharebar_check_mobile())return;
$leftpad=get_option('ssbar_leftpadding','-80px');
$toppad=get_option('ssbar_toppadding','20');
$bottompad=get_option('ssbar_bottompadding','0');
wp_print_scripts( 'jquery' );
?>
<!-- This site is powered by Scrolling Social Sharebar - http://techxt.com/scrolling-social-sharebar-plugin/ -->
<style type="text/css">
   #scrollbarbox
   {
    	<?php if(trim(get_option('ssbar_barbackground','#fff'))!='')echo 'background:'.get_option('ssbar_barbackground','#fff').';'; ?>
	<?php if(trim(get_option('ssbar_barborder','1px solid #000'))!='')echo 'border:'.get_option('ssbar_barborder','1px solid #000').';'; ?>
	<?php if(trim(get_option('ssbar_leftpadding','-80px'))!=''&&!is_home())echo 'margin-left:'.get_option('ssbar_leftpadding','-80px').';'; ?>
	<?php if(trim(get_option('ssbar_leftpaddinghm','-80px'))!=''&&is_home())echo 'margin-left:'.get_option('ssbar_leftpaddinghm','-80px').';'; ?>
	<?php if(trim(get_option('ssbar_barshadow',''))!='')echo 'box-shadow:'.get_option('ssbar_barshadow','').';'; ?>
	<?php if(trim(get_option('ssbar_barradius',''))!='')echo 'border-radius:'.get_option('ssbar_barradius','').';'; ?>
	<?php if(trim(get_option('ssbar_barpadding','5px'))!='')echo 'padding:'.get_option('ssbar_barpadding','5px').';'; ?>
    	display: block;
    	margin-top: 0;
    	position: absolute;
    }
    #scrollbarbox table,#scrollbarbox table td
    {
    	background:transparent !important;
    	border:none !important;
    	padding:0px !important;
    	margin:0px !important;
    }
    .sharebarbtn
    {
    	line-height:1;
    }
<?php if(trim(get_option('ssbar_buttonpadding','0px'))!='')echo '.sharebarbtn{padding:'.get_option('ssbar_buttonpadding','5px').';}'; ?>    

    div.sbpinned 
    {
    	position: fixed !important;
		z-index: 9999;
   	top: <?php echo $toppad; ?>px;
    }
</style>
<?php if(get_option( 'ssbar_atype', 'scroll' )== "scroll" ){ ?>
<script type="text/javascript">
(function($) {
	$(function() {
            var offset = $("div#scrollbarbox").offset();
            var bottomPadding = <?php echo $bottompad; ?>;
            var topPadding = <?php echo $toppad; ?>;
            $(window).scroll(function() 
            {
            	if($(window).scrollTop()<$(document).height()-bottomPadding-$("#scrollbarbox").height()){
            		if ($(window).scrollTop() > offset.top) 
                	{
			    var ss= $(window).scrollTop() - offset.top + topPadding;
        	            $("#scrollbarbox").stop().animate({marginTop:ss});
                	} 
                	else {$("#scrollbarbox").stop().animate({marginTop: 0}); }
                };
            });
        });
})(jQuery);
</script>
<?php }else { ?>
<script type="text/javascript">

(function($) {
	$(function() {
            var offset = $("#scrollbarbox").offset();
            var bottomPadding = <?php echo $bottompad; ?>;
            var topPadding = <?php echo $toppad; ?>;
            $(window).scroll(function() {
		
                if ($(window).scrollTop() > offset.top-topPadding && $(window).scrollTop()<$(document).height()-bottomPadding-$("#scrollbarbox").height()) 
                {
		    $("#scrollbarbox").addClass("sbpinned");
                } else {
                    $("#scrollbarbox").removeClass("sbpinned");
                }
            });
        });
})(jQuery);
</script>
<?php } ?>
<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script><script type="text/javascript" src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script><script type="text/javascript" src="http://apis.google.com/js/plusone.js"></script><script type="text/javascript" src="http://platform.linkedin.com/in.js"></script>

<?php
if(get_option('ssbar_addog', true )==true)ssharebar_fb_thumb();
}

function ssharebar_fb_thumb()
{
global $post;
$thumb = false;
if(function_exists('get_post_thumbnail_id')&&function_exists('wp_get_attachment_image_src'))
{
	$image_id = get_post_thumbnail_id();
	$image_url = wp_get_attachment_image_src($image_id,'large');
	$thumb = $image_url[0]; 
}
$default_img = get_option('ssbar_defthumb',''); 
if ( $thumb == false ) 
	$thumb=$default_img; 

if(is_single()) { 
?>
	<meta property="og:type" content="article" />
	<meta property="og:title" content="<?php single_post_title(''); ?>" />
	<meta property="og:url" content="<?php the_permalink(); ?>"/>
	<meta property="og:description" content="<?php echo strip_tags(get_the_excerpt($post->ID)); ?>" />  
	<?php if(trim($thumb)!=''){ ?>
		<meta property="og:image" content="<?php echo $thumb; ?>" />
	<?php } ?>
<?php  } else { ?>
	<meta property="og:type" content="article" />
  	<meta property="og:title" content="<?php bloginfo('name'); ?>" />
	<meta property="og:url" content="<?php bloginfo('url'); ?>"/>
	<meta property="og:description" content="<?php bloginfo('description'); ?>" />
	<?php if(trim($default_img)!=''){ ?>
		<meta property="og:image" content="<?php echo $default_img; ?>" />
	<?php } ?>
<?php  } 

}
function ssharebar_option()
{
?>
	<h2>Scrolling Social Sharebar Plugin Options</h2>
	<p>Like this Plugin then why not hit the like button. Your like will motivate me to enhance the features of the Plugin :)<br />
	<iframe style="overflow: hidden; width: 450px; height: 35px;" src="http://www.facebook.com/plugins/like.php?app_id=199883273397074&amp;href=http%3A%2F%2Fwww.facebook.com%2Fpages%2FTech-XT%2F223482634358279&amp;send=false&amp;layout=standard&amp;width=450&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=35" frameborder="0" scrolling="no" width="320" height="35"></iframe><br />And if you are too generous then you can always <b>DONATE</b> by clicking the donation button.<br/>A Donation will help in the uninterrupted developement of the plugin.<br /><a href="http://techxt.com/scrolling-social-sharebar-plugin/" TARGET='_blank'>Click here</a> for <b>Reference on using the plugin</b> or if you want to <b>report a bug</b> or if you want to <b>suggest a Feature</b><br /></p>
<table class="form-ta">	
<tr valign="top">
<td width="78%">
	<form method="post" action="options.php">
	<?php wp_nonce_field('update-options'); ?>
	
	<h3 style="color: #cc0000;">Where to Display</h3>
	<p><input type="checkbox" name="ssbar_dpost" value="checked" <?php echo get_option('ssbar_dpost','checked'); ?> />Dispaly on Post</p>
	<p><input type="checkbox" name="ssbar_dpage" value="checked" <?php echo get_option('ssbar_dpage','checked'); ?> />Dispaly on Page</p>
	<p><input type="checkbox" name="ssbar_dhome" value="checked" <?php echo get_option('ssbar_dhome','checked'); ?> />Dispaly on HomePage</p>
	
	<h3 style="color: #cc0000;">Select Icons to display</h3>
<p><input type="checkbox" name="ssbar_fblike" id="ssbar_fblike" value="true"<?php if (get_option( 'ssbar_fblike', true ) == true) echo ' checked'; ?>> Display Facebook Like</p>
<p><input type="checkbox" name="ssbar_twitter" id="ssbar_twitter" value="true"<?php if (get_option( 'ssbar_twitter', true ) == true) echo ' checked'; ?>> Display Twitter&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;via @<input type="text" name="ssbar_twittervia" style="width: 150px;" value="<?php echo get_option('ssbar_twittervia',''); ?>" /></p>
<p><input type="checkbox" name="ssbar_plusone" id="ssbar_plusone" value="true"<?php if (get_option( 'ssbar_plusone', true ) == true) echo ' checked'; ?>> Display Google +1 </p>
<p><input type="checkbox" name="ssbar_linkedin" id="ssbar_linkedin" value="true"<?php if (get_option( 'ssbar_linkedin', false ) == true) echo ' checked'; ?>> Display Linkedin </p>
<p><input type="checkbox" name="ssbar_stumble" id="ssbar_stumble" value="true"<?php if (get_option( 'ssbar_stumble', false ) == true) echo ' checked'; ?>> Display Stumbleupon </p>
<p><input type="checkbox" name="ssbar_fbshare" id="ssbar_fbshare" value="true"<?php if (get_option( 'ssbar_fbshare', false ) == true) echo ' checked'; ?>> Display Facebook Share </p>
<p><input type="checkbox" name="ssbar_cbtn" id="ssbar_cbtn" value="true"<?php if (get_option( 'ssbar_cbtn', false ) == true) echo ' checked'; ?>> Display Custom Buttons </p>
<p><input type="checkbox" name="ssbar_addthis" id="ssbar_addthis" value="true"<?php if (get_option( 'ssbar_addthis', true ) == true) echo ' checked'; ?>> Display Addthis Button </p>
<p><b>Default Thumbnail URL</b> <input type="text" name="ssbar_defthumb" style="width: 300px;" value="<?php echo get_option('ssbar_defthumb',''); ?>" /></p>
<h3 style="color: #cc0000;">Margins (Positioning the Bar)</h3>
<p><b>Left Margin HomePage: </b><input style="width: 60px;" type="text" name="ssbar_leftpaddinghm" value="<?php echo get_option('ssbar_leftpaddinghm','-80px'); ?>" /> <b>Include px</b> at the end of the value<br />(Negative value will shift Icon Bar towards Left and Positive value will move it towards Right)</p>	
<p><b>Left Margin Posts/Pages: </b><input style="width: 60px;" type="text" name="ssbar_leftpadding" value="<?php echo get_option('ssbar_leftpadding','-80px'); ?>" /> <b>Include px</b> at the end of the value<br />(Negative value will shift Icon Bar towards Left and Positive value will move it towards Right)</p>
	<p><b>Top Margin : </b><input style="width: 60px;" type="text" name="ssbar_toppadding" value="<?php echo get_option('ssbar_toppadding','20'); ?>" /> <b>Do not Include px</b> at the end of the value<br />(Increasing the value will move the bar Down)</p>
	<p><b>Bottom Margin : </b><input style="width: 60px;" type="text" name="ssbar_bottompadding" value="<?php echo get_option('ssbar_bottompadding','0'); ?>" /> <b>Do not Include px</b> at the end of the value<br />(The margin from bottom of the page where the bar will stop scrolling)</p>

<h3 style="color: #cc0000;">Choose Animation</h3>
<input type="radio" name="ssbar_atype" value="scroll" <?php if (get_option( 'ssbar_atype', 'scroll' ) == "scroll" ) echo ' checked'; ?>></input><label for="ssbar_atype">Animated&nbsp;&nbsp;&nbsp;&nbsp;</label>
<input type="radio" name="ssbar_atype" value="fixed" <?php if (get_option( 'ssbar_atype', 'scroll' ) == "fixed" ) echo ' checked'; ?>></input><label for="ssbar_atype">Fixed</label>	

<h3 style="color: #cc0000;">Add your own Custom Buttons</h3>
<p>
To add more than one custom button, separate the buttons codes with the word <b>[BUTTON]</b><br />
e.g {code of first button} [BUTTON] {code of second button}
</p>
<p>
Following <b>Tags</b> that will be replace by actual codes when the buttons are displayed<br/>
<b>%%URL%%</b> - The URL of the Post/Page<br/>
<b>%%EURL%%</b> - The HTML encoded URL of the Post/Page<br/>
<b>%%TITLE%%</b> - The Title of the Post/Page<br/>
<b>%%DESC%%</b> - Description or Post Excerpts<br/>
<b>%%PIMAGE%%</b> - Link to the Featured Image of the post or the first image if featured image not set.<br/>

</p>
<textarea name="ssbar_cblarge" rows="10" cols="50" style="width:500px;"><?php echo stripslashes(htmlspecialchars(get_option('ssbar_cblarge',''))); ?></textarea>


  <h3 style="color: #cc0000;">Mobile browsers</h3>
<p><input type="checkbox" name="ssbar_mob" id="ssbar_mob" value="true"<?php if (get_option( 'ssbar_mob', false ) == true) echo ' checked'; ?>><b>Disable on Mobile Browser</b><br /> Check this option if you have installed a mobile theme plugin like Wptouch, WordPress Mobile Pack etc.</p>

<h3 style="color: #cc0000;">Styling the Bar</h3>
	<p><b>Background : </b><input style="width: 150px;" type="text" name="ssbar_barbackground" value="<?php echo get_option('ssbar_barbackground','#fff'); ?>" /> <b>Default : #fff</b></p>
	<p><b>Border : </b><input style="width: 150px;" type="text" name="ssbar_barborder" value="<?php echo get_option('ssbar_barborder','1px solid #000'); ?>" /> <b>Default : 1px solid #000</b></p>
	<p><b>Padding(Bar) : </b><input style="width: 150px;" type="text" name="ssbar_barpadding" value="<?php echo get_option('ssbar_barpadding','5px'); ?>" /> <b>Default : 5px</b></p>
        <p><b>Padding(between buttons) : </b><input style="width: 150px;" type="text" name="ssbar_buttonpadding" value="<?php echo get_option('ssbar_buttonpadding','0px'); ?>" /> <b>Default : 0px</b></p>
	<p><b>Border Radius : </b><input style="width: 150px;" type="text" name="ssbar_barradius" value="<?php echo get_option('ssbar_barradius',''); ?>" /> <b>e.g. - 10px 0px 10px 0px </b> (May not work with IE)</p>
	<p><b>Shadow : </b><input style="width: 150px;" type="text" name="ssbar_barshadow" value="<?php echo get_option('ssbar_barshadow',''); ?>" /> <b>e.g. - 2px 2px 2px 2px #888</b> (May not work with IE)</p>

<h3 style="color: #cc0000;">Meta tags & links</h3>
<p><input type="checkbox" name="ssbar_addog" id="ssbar_addog" value="true"<?php if (get_option( 'ssbar_addog', true ) == true) echo ' checked'; ?>> Add Facebook Open Graph (og) Meta tags</p>
<p><input type="checkbox" name="ssbar_addcredit" id="ssbar_addcredit" value="true"<?php if (get_option( 'ssbar_addcredit', true ) == true) echo ' checked'; ?>> Add Sharebar link (I appreciate if you keep this checked)</p>
	
<h3 style="color: #cc0000;">Don't display on Posts/Pages</h3>
<p>Enter the <b>ID's</b> of those Pages/Posts separated by comma. e.g 13,5,87<br/>You can also include a <b>custom post types</b> or <b>custom post format</b> (all separated by comma)<br /> 
<input type="text" name="ssbar_excludeid" style="width: 300px;" value="<?php echo get_option('ssbar_excludeid',''); ?>" /></p>

<h3 style="color: #cc0000;">Don't display on Category</h3>
<p>Enter the ID's of those Categories separated by comma. e.g 131,45,817<br/>
<input type="text" name="ssbar_excludecat" style="width: 300px;" value="<?php echo get_option('ssbar_excludecat',''); ?>" /></p>

	
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="ssbar_mob,ssbar_leftpadding,ssbar_toppadding,ssbar_dpost,ssbar_dpage,ssbar_fblike,ssbar_twitter,ssbar_plusone,ssbar_linkedin,ssbar_stumble,ssbar_fbshare,ssbar_addthis,ssbar_barbackground,ssbar_barborder,ssbar_barpadding,ssbar_barshadow,ssbar_barradius,ssbar_defthumb,ssbar_atype,ssbar_bottompadding,ssbar_excludecat,ssbar_excludeid,ssbar_buttonpadding,ssbar_twittervia,ssbar_addog,ssbar_addcredit,ssbar_dhome,ssbar_leftpaddinghm,ssbar_cbtn,ssbar_cblarge" />
	<p class="submit" style="position: fixed; background: none repeat scroll 0% 0% rgb(51, 51, 51); padding: 10px; bottom: 39px; border-radius: 10px 10px 10px 10px; margin-left: 550px;">
		<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	</p>
	</form>
</td><td width="2%">&nbsp;</td><td width="20%"><a href="http://techxt.com/sharebar_ad" target="_blank"><img src="http://techxt.com/sharebar_ad.png" /></a><br/><b>Follow us on</b><br/><a href="http://twitter.com/techxt" target="_blank"><img src="http://a0.twimg.com/a/1303316982/images/twitter_logo_header.png" /></a><br/><a href="http://facebook.com/techxt" target="_blank"><img src="https://secure-media-sf2p.facebook.com/ads3/creative/pressroom/jpg/b_1234209334_facebook_logo.jpg" height="38px" width="118px"/></a><p></p><b>Feeds and News</b><br /><?php ssharebar_get_feed() ?>
<p></p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_donations">
<input type="hidden" name="business" value="isudipto@gmail.com">
<input type="hidden" name="lc" value="US">
<input type="hidden" name="item_name" value="Scrolling Social Sharebar">
<input type="hidden" name="no_note" value="0">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest">
<input type="image" src="https://www.paypalobjects.com/WEBSCR-640-20110401-1/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/WEBSCR-640-20110401-1/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
<br />Consider a Donation and remember $X is always better than $0
</td></tr></table>
<?php
}
function ssharebar_admin()
{
	add_options_page('Scrolling Social Sharebar', 'Scrolling Social Sharebar', 7, 'scrollsharebar', 'ssharebar_option');
}
add_action('admin_menu', 'ssharebar_admin');
add_action('wp_head', 'ssharebar_css');
add_filter('the_content', 'disp_ssharebar',1);
add_filter('the_excerpt', 'disp_ssharebar',1);
add_filter('get_the_excerpt', 'disp_ssharebar',1);

function ssharebar_get_feed() {
	include_once(ABSPATH . WPINC . '/feed.php');
	$rss = fetch_feed('http://feeds.feedburner.com/techxt');
	if (!is_wp_error( $rss ) ){
		$rss5 = $rss->get_item_quantity(5); 
		$rss1 = $rss->get_items(0, $rss5); 
	}
?>
<ul>
<?php if (!$rss5 == 0)foreach ( $rss1 as $item ){?>
<li style="list-style-type:circle">
<a target="_blank" href='<?php echo $item->get_permalink(); ?>'><?php echo $item->get_title(); ?></a>
</li>
<?php } ?>
</ul>
<?php
}
//===================================================================================//
function disp_ssharebar_func()
{
global $post;
if (get_option('ssbar_mob', false )==true && ssharebar_check_mobile())return $content;
if(is_home()||is_archive())
{
	$plink=get_home_url();
	$ptitle = get_bloginfo('name').' - '.get_bloginfo ( 'description' );
}
else
{
	$plink = get_permalink($post->ID);
	$ptitle = get_the_title($post->ID);
}
$eplink = urlencode($plink);
$eptitle=str_replace(array(">","<"),"",$ptitle);
$twsc='';$flsc='';$gpsc='';$fssc='';
$via=get_option('ssbar_twittervia','');

$sharelinks.='<div class="scrollbarbox" id="scrollbarbox"><table class="tssbar" align="center" width="60" cellspacing="1" border="0">';

if(get_option('ssbar_fblike',true)==true)
$sharelinks.= '<tr><td align="center" ><div style="height:64px;width:48px;margin:0pt auto;" class="sharebarbtn sbarfblike"><iframe src="http://www.facebook.com/plugins/like.php?app_id=126788060742161&amp;href='.$eplink.'&amp;send=false&amp;layout=box_count&amp;width=48&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=64" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:48px; height:64px;" allowTransparency="true"></iframe></div></td></tr>';

if (get_option( 'ssbar_twitter',true ) == true)
$sharelinks.= '<tr><td align="center" ><div class="sharebarbtn sbartwitter"><a href="http://twitter.com/share" data-url="'.$plink.'" data-counturl="'.$plink.'" data-text="'.$eptitle.'" class="twitter-share-button" data-count="vertical" data-via="'.$via.'"></a>'.$twsc.'</div></td></tr>';

if(get_option('ssbar_plusone',true)==true)
$sharelinks.='<tr><td align="center"><div class="sharebarbtn sbarplusone" >'.$gpsc.'<g:plusone size="tall" href="'.$plink.'" count="true"></g:plusone></div></td></tr>';

if(get_option('ssbar_linkedin',false)==true)
$sharelinks.='<tr><td align="center" ><div class="sharebarbtn sbarlinkedin" ><script type="in/share" data-url="'.$plink.'" data-counter="top"></script></div></td></tr>';

if(get_option('ssbar_stumble',false)==true)
$sharelinks.= '<tr><td align="center" ><div class="sharebarbtn sbarstumble"><script src="http://www.stumbleupon.com/hostedbadge.php?s=5&r='.$plink.'"></script></div></td></tr>';

if(get_option('ssbar_fbshare',false)==true)
$sharelinks.= '<tr><td align="center" ><div class="sharebarbtn sbarfbshare"><div class="fb-share-button" data-href="'.$plink.'" data-width="450" data-type="box_count"></div></div></td></tr>';

if (get_option( 'ssbar_cbtn', false ) == true)$sharelinks.=ssharebar_get_custom_button();

if(get_option('ssbar_addthis',true)==true)
$sharelinks.='<tr><td align="center" ><div class="sharebarbtn sbaraddthis"><div class="addthis_toolbox addthis_default_style " style="width: 50px; padding-top: 5px;"><a class="addthis_counter"></a><script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-4ed3df145c76db9e"></script></div></div></td></tr>';

if(get_option('ssbar_addcredit',true)==true)
$sharelinks.='<tr><td align="center" ><center><small><a href="http://techxt.com/?" target="_blank" style="color:#aaa;font: 10px arial;">share</a></small></center></td></tr>';

$sharelinks.= '</table></div>';
return $sharelinks;
}
function ssharebar_get_custom_button()
{
	global $post;
	$desc = "";
	if(is_home()||is_archive())
	{
		$plink=get_home_url();
		$ptitle = get_bloginfo('name');
		$desc =get_bloginfo ( 'description' );
	}
	else
	{
		$plink = get_permalink($post->ID);
		$ptitle = get_the_title($post->ID);
		if (has_excerpt($post->ID)) {
			$desc = esc_attr(strip_tags(get_the_excerpt($post->ID)));
		}else{
			$desc = esc_attr(str_replace("\r\n",' ',substr(strip_tags(strip_shortcodes($post->post_content)), 0, 160)));
		}
	}
	$eplink = urlencode($plink);
	$pimg = ssharebar_post_img_link();	
	$cbtn=get_option('ssbar_cblarge','');
	if(trim($cbtn==''))return '';
	
	$cbtn=str_replace("%%URL%%", $plink, $cbtn);
	$cbtn=str_replace("%%EURL%%", $eplink, $cbtn);
	$cbtn=str_replace("%%TITLE%%", $ptitle, $cbtn);
	$cbtn=str_replace("%%PIMAGE%%", $pimg, $cbtn);
	$cbtn=str_replace("%%DESC%%", $desc, $cbtn);
		
	$allbtns=explode("[BUTTON]",$cbtn);
	$cnt=1;
	$buttoncode='';
	foreach($allbtns as $btn)
	{
		if(trim($btn==''))continue;
		$buttoncode.='<tr><td align="center" ><div class="sharebarbtn sbarcbtn-'.$cnt.'">'.$btn.'</div></td></tr>';
		$cnt=$cnt+1;
	} 
	return $buttoncode;

} 
function ssharebar_get_first_image() {
global $post, $posts;
$first_img = '';
ob_start();
ob_end_clean();
$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
$first_img = $matches[1][0];
return $first_img;
}
function ssharebar_post_img_link()
{
$thumb = false;
$default_img = get_option('ssbar_defthumb',''); 
if(is_home()||is_archive())return $default_img;
if(function_exists('get_post_thumbnail_id')&&function_exists('wp_get_attachment_image_src'))
{
	$image_id = get_post_thumbnail_id();
	$image_url = wp_get_attachment_image_src($image_id,'large');
	$thumb = $image_url[0];
}
if($thumb=='')$thumb=ssharebar_get_first_image();
if ( $thumb == false || $thumb=='') 
	$thumb=$default_img; 
return $thumb;
}

function ssharebar_check_mobile()
{
$ismob=false;
switch(TRUE)
{	
	case (preg_match('/(iphone|ipod)/i', $_SERVER['HTTP_USER_AGENT']) && preg_match('/mobile/i', $_SERVER['HTTP_USER_AGENT'])):
		$ismob="true";
		break; 
	case (preg_match('/ipad/i', $_SERVER['HTTP_USER_AGENT']) && preg_match('/mobile/i', $_SERVER['HTTP_USER_AGENT'])):
		$ismob=false;
		break;	
	case (preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT'])):
		$ismob=true;
		break; 
	case (((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'text/vnd.wap.wml') > 0) || (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml')>0)) || ((isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])))):
		$ismob=true;
		break; 
	case (in_array(strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,3)),array('lg '=>'lg ','lg-'=>'lg-','lg_'=>'lg_','lge'=>'lge'))):
		$ismob=true;
		break; 
	case (in_array(strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4)),array('acs-'=>'acs-','amoi'=>'amoi','doco'=>'doco','eric'=>'eric','huaw'=>'huaw','lct_'=>'lct_','leno'=>'leno','mobi'=>'mobi','mot-'=>'mot-','moto'=>'moto','nec-'=>'nec-','phil'=>'phil','sams'=>'sams','sch-'=>'sch-','shar'=>'shar','sie-'=>'sie-','wap_'=>'wap_','zte-'=>'zte-'))):
		$ismob=true;
		break;
	case (preg_match('/Googlebot-Mobile/i', $_SERVER['HTTP_USER_AGENT']) || preg_match('/YahooSeeker\/M1A1-R2D2/i', $_SERVER['HTTP_USER_AGENT'])):
		$ismob=true;
		break;
}
return $ismob;
}
?>