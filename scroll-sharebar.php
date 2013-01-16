<?php
/*
Plugin Name: Scrolling Social Sharebar (Twitter Like Google +1 Linkedin and Stumbleupon)
Plugin URI: http://techxt.com/scrolling-social-sharebar-plugin/
Description: Scrolling Social Sharebar (Twitter Like Google +1 Linkedin and Stumbleupon)
Version: 1.5.1
Author: Sudipto Pratap Mahato
Author URI: http://techxt.com
*/



function disp_ssharebar($content) {

global $post;
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

if((is_single()&&get_option('ssbar_dpost','checked')=='checked')||(is_page()&&get_option('ssbar_dpage','checked')=='checked')){
	$sharelinks=disp_ssharebar_func();
	$content=$sharelinks.$content;
}

return $content;
}


function ssharebar_css() {
if(!is_single()&&!is_page())return;
$leftpad=get_option('ssbar_leftpadding','-80px');
$toppad=get_option('ssbar_toppadding','20');
$bottompad=get_option('ssbar_bottompadding','0');
wp_print_scripts( 'jquery' );
?>
<style type="text/css">
   #scrollbarbox
   {
    	<?php if(trim(get_option('ssbar_barbackground','#fff'))!='')echo 'background:'.get_option('ssbar_barbackground','#fff').';'; ?>
	<?php if(trim(get_option('ssbar_barborder','1px solid #000'))!='')echo 'border:'.get_option('ssbar_barborder','1px solid #000').';'; ?>
	<?php if(trim(get_option('ssbar_leftpadding','-80px'))!='')echo 'margin-left:'.get_option('ssbar_leftpadding','-80px').';'; ?>
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
<?php if(trim(get_option('ssbar_buttonpadding','0px'))!='')echo '.sharebarbtn{padding:'.get_option('ssbar_buttonpadding','5px').';}'; ?>    

    div.sbpinned 
    {
    	position: fixed !important;
   	top: <?php echo $toppad; ?>px;
    }
</style>
<?php if(get_option( 'ssbar_atype', 'scroll' )== "scroll" ){ ?>
<script type="text/javascript">
(function($) {
	$(function() {
            var offset = $("#scrollbarbox").offset();
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
            $(window).scroll(function() {
		
                if ($(window).scrollTop() > offset.top && $(window).scrollTop()<$(document).height()-bottomPadding-$("#scrollbarbox").height()) 
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
<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script><script type="text/javascript" src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script><script type="text/javascript" src="http://apis.google.com/js/plusone.js"></script><script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script><script type="text/javascript" src="http://platform.linkedin.com/in.js"></script>

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
	
	<h3 style="color: #cc0000;">Select Icons to display</h3>
<p><input type="checkbox" name="ssbar_fblike" id="ssbar_fblike" value="true"<?php if (get_option( 'ssbar_fblike', true ) == true) echo ' checked'; ?>> Display Facebook Like</p>
<p><input type="checkbox" name="ssbar_twitter" id="ssbar_twitter" value="true"<?php if (get_option( 'ssbar_twitter', true ) == true) echo ' checked'; ?>> Display Twitter&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;via @<input type="text" name="ssbar_twittervia" style="width: 150px;" value="<?php echo get_option('ssbar_twittervia',''); ?>" /></p>
<p><input type="checkbox" name="ssbar_plusone" id="ssbar_plusone" value="true"<?php if (get_option( 'ssbar_plusone', true ) == true) echo ' checked'; ?>> Display Google +1 </p>
<p><input type="checkbox" name="ssbar_linkedin" id="ssbar_linkedin" value="true"<?php if (get_option( 'ssbar_linkedin', false ) == true) echo ' checked'; ?>> Display Linkedin </p>
<p><input type="checkbox" name="ssbar_stumble" id="ssbar_stumble" value="true"<?php if (get_option( 'ssbar_stumble', false ) == true) echo ' checked'; ?>> Display Stumbleupon </p>
<p><input type="checkbox" name="ssbar_fbshare" id="ssbar_fbshare" value="true"<?php if (get_option( 'ssbar_fbshare', false ) == true) echo ' checked'; ?>> Display Facebook Share </p>
<p><input type="checkbox" name="ssbar_addthis" id="ssbar_addthis" value="true"<?php if (get_option( 'ssbar_addthis', true ) == true) echo ' checked'; ?>> Display Addthis Button </p>
<p><b>Default Thumbnail URL</b> <input type="text" name="ssbar_defthumb" style="width: 300px;" value="<?php echo get_option('ssbar_defthumb',''); ?>" /></p>
<h3 style="color: #cc0000;">Margins (Positioning the Bar)</h3>
	<p><b>Left Margin : </b><input style="width: 60px;" type="text" name="ssbar_leftpadding" value="<?php echo get_option('ssbar_leftpadding','-80px'); ?>" /> <b>Include px</b> at the end of the value<br />(Negative value will shift Icon Bar towards Left and Positive value will move it towards Right)</p>
	<p><b>Top Margin : </b><input style="width: 60px;" type="text" name="ssbar_toppadding" value="<?php echo get_option('ssbar_toppadding','20'); ?>" /> <b>Do not Include px</b> at the end of the value<br />(Increasing the value will move the bar Down)</p>
	<p><b>Bottom Margin : </b><input style="width: 60px;" type="text" name="ssbar_bottompadding" value="<?php echo get_option('ssbar_bottompadding','0'); ?>" /> <b>Do not Include px</b> at the end of the value<br />(The margin from bottom of the page where the bar will stop scrolling)</p>

<h3 style="color: #cc0000;">Choose Animation</h3>
<input type="radio" name="ssbar_atype" value="scroll" <?php if (get_option( 'ssbar_atype', 'scroll' ) == "scroll" ) echo ' checked'; ?>></input><label for="ssbar_atype">Animated&nbsp;&nbsp;&nbsp;&nbsp;</label>
<input type="radio" name="ssbar_atype" value="fixed" <?php if (get_option( 'ssbar_atype', 'scroll' ) == "fixed" ) echo ' checked'; ?>></input><label for="ssbar_atype">Fixed</label>	

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
	<input type="hidden" name="page_options" value="ssbar_leftpadding,ssbar_toppadding,ssbar_dpost,ssbar_dpage,ssbar_fblike,ssbar_twitter,ssbar_plusone,ssbar_linkedin,ssbar_stumble,ssbar_fbshare,ssbar_addthis,ssbar_barbackground,ssbar_barborder,ssbar_barpadding,ssbar_barshadow,ssbar_barradius,ssbar_defthumb,ssbar_atype,ssbar_bottompadding,ssbar_excludecat,ssbar_excludeid,ssbar_buttonpadding,ssbar_twittervia,ssbar_addog,ssbar_addcredit" />
	<p class="submit">
		<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	</p>
	</form>
</td><td width="2%">&nbsp;</td><td width="20%"><b>Follow us on</b><br/><a href="http://twitter.com/techxt" target="_blank"><img src="http://a0.twimg.com/a/1303316982/images/twitter_logo_header.png" /></a><br/><a href="http://facebook.com/techxt" target="_blank"><img src="https://secure-media-sf2p.facebook.com/ads3/creative/pressroom/jpg/b_1234209334_facebook_logo.jpg" height="38px" width="118px"/></a><p></p><b>Feeds and News</b><br /><?php ssharebar_get_feed() ?>
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
$plink = get_permalink($post->ID);
$eplink = urlencode($plink);
$ptitle = get_the_title($post->ID);
$eptitle=str_replace(array(">","<"),"",$ptitle);
$twsc='';$flsc='';$gpsc='';$fssc='';
$via=get_option('ssbar_twittervia','');

$sharelinks.='<div class="scrollbarbox" id="scrollbarbox"><table align="center" width="60" cellspacing="1" border="0">';

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
$sharelinks.= '<tr><td align="center" ><div style="position: relative; height: 60px;width:45px;"><iframe src="//www.facebook.com/plugins/like.php?href='.$eplink.'&amp;send=false&amp;layout=box_count&amp;width=450&amp;show_faces=false&amp;font=arial&amp;colorscheme=light&amp;action=like&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:45px; height:41px;" allowTransparency="true"></iframe>
<div style="background: url(&quot;http://lh3.googleusercontent.com/-TuITveepO2g/UOvRrWHqsaI/AAAAAAAAAnw/VrVfnRoLfio/s45/fbshare.jpg&quot;) repeat scroll 0px 0px transparent; width: 45px; height: 18px; position: absolute; bottom: 1px; cursor: pointer;"  onclick="window.open(&#39;https://www.facebook.com/sharer/sharer.php?u='.$eplink.'&#39;,&#39;popUpWindow&#39;,&#39;height=500,width=400,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=no,directories=no, status=yes&#39;);"></div></div></td></tr>';



if(get_option('ssbar_addthis',true)==true)
$sharelinks.='<tr><td align="center" ><div class="addthis_toolbox addthis_default_style " style="width: 50px; padding-top: 5px;"><a class="addthis_counter"></a><script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4e3d994a059e1110"></script></div></td></tr>';

if(get_option('ssbar_addcredit',true)==true)
$sharelinks.='<tr><td align="center" ><small><a href="http://techxt.com/?" target="_blank" style="color:#888;">share</a></small></td></tr>';

$sharelinks.= '</table></div>';
return $sharelinks;
}

?>