<?php
/*
Plugin Name: Migrador de Link
Plugin URI: http://www.seutec.com.br/contato
Description: Este plug redireciona links permanentes antigos para a sua nova estrutura de links sem quebrar.
Author: Seutec Inc.
Version: 1.0
Author URI: http://www.seutec.com.br
*/ 

/*
Este plug foi desenvolvido pelo Portal Seutec sob a Creative Commons Atribuição 4.0 Brasil
This plugin was developed by Portal Seutec under the Creative Commons Attribution 4.0 Brazil
Copyright 2014 Portal Seutec. All rights reserved.

This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

global $dean_pm_config;
$dean_pm_config =array();
$dean_pm_config ['highpriority'] = true ;
$dean_pm_config['rewrite'] = array();
$dean_pm_config['oldstructure'] = NULL;
$dean_pm_storedoptions=get_option("dean_pm_options");
if($dean_pm_storedoptions) {
	foreach($dean_pm_storedoptions AS $k=>$v) {
		$dean_pm_config[$k]=$v;	
	}
} 
else 
{
	$dean_pm_config['oldstructure'] = get_settings('permalink_structure');
}


function dean_pm_the_posts($post)
{
	global $wp;
	global $wp_rewrite;
	global $dean_pm_config;
	if ($post != NULL && is_single() && $dean_pm_config['oldstructure'] != $wp_rewrite->permalink_structure)
	{
		if (array_key_exists($wp->matched_rule, $dean_pm_config['rewrite']))
		{
			// ok, we need to generate a 301 Permanent redirect here.
			header("HTTP/1.1 301 Moved Permanently", TRUE, 301);
			header('Status: 301 Moved Permanently');
			$permalink = get_permalink($post[0]->ID);
			if (is_feed())
			{
				$permalink = trailingslashit($permalink) . 'feed/';
			}
			header("Location: ". $permalink);
			exit();
		}
	}
	return $post;
}

function dean_pm_post_rewrite_rules($rules)
{
	global $wp_rewrite;
	global $dean_pm_config;
	$oldstruct = $dean_pm_config['oldstructure'];
	if ($oldstruct != NULL && $oldstruct != $wp_rewrite->permalink_structure)
	{
		$dean_pm_config['rewrite'] = $wp_rewrite->generate_rewrite_rule($oldstruct, false, false, false, true);
		update_option("dean_pm_options",$dean_pm_config);
		if ($dean_pm_config ['highpriority'] == true)
		{
			return array_merge($dean_pm_config['rewrite'],$rules);
		}
		else
		{
			return array_merge($rules, $dean_pm_config['rewrite']);
		}
	}
	return $rules;
}

function dean_pm_options_page()
{
	global $dean_pm_config;
	//All output should go in this var which get printed at the end
	$message="";
	if (!empty($_POST['info_update'])) 
	{
		$old_permalink_structure =(string) $_POST['old_struct'];	
		$old_permalink_structure = preg_replace('#/+#', '/', '/' . $old_permalink_structure);
/*		global $wp_rewrite;
		$wp_rewrite->matches = 'matches';*/
		//$wp_rewrite->generate_rewrite_rule($old_permalink_structure, false, false, false, true);
		$dean_pm_config['oldstructure'] = $old_permalink_structure;
		update_option("dean_pm_options",$dean_pm_config);
		$message.=__('Configurações atualizadas', 'migrador_link');

		//Print out the message to the user, if any
		if($message!="") {
			?>
			<div class="updated"><strong><p><?php
			echo $message;
			?></p></strong></div><?php
		}
	}
	?>
		<div class=wrap>
			<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
				<h2><?php _e('Migrador de Link', 'migrador_link') ?> 0.1</h2>
					<fieldset name="sm_basic_options"  class="options">
					<legend><?php _e('Configurações', 'migrador_link') ?></legend>
					<ul>
					<li><strong>Antigo Permalink Structure</strong>:<input type="text" size="50" name="old_struct" value="<?php echo $dean_pm_config['oldstructure'];?>"/>
						</li>
						</ul>
					</fieldset>
					<div class="submit"><input type="submit" name="info_update" value="<?php _e('Atualizar Configurações', 'migrador_link') ?>" /></div>
					<fieldset class="options">
					<legend><?php _e('Informações e Suporte', 'permalinks_migration') ?></legend>
					<p><?php echo str_replace("%s","<a href=\"http://www.seutec.com.br/contato/\">http://www.seutec.com.br/contato/</a>",__("Procurar novas atualizações e melhorias.",'migrador_link')); ?></p>
				</fieldset>
				</form></div>
<p>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- 728x90 -->
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-6865424275321467"
     data-ad-slot="4420145038"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
<p>
<?php

}

function dean_pm_reg_admin() {
	if (function_exists('add_options_page')) 
	{
		add_options_page('Migrador de Link', 'Migrador de Link', 8, basename(__FILE__), 'dean_pm_options_page');	

	}
}

add_action('admin_menu', 'dean_pm_reg_admin');
add_filter('the_posts', 'dean_pm_the_posts', 20);
add_filter('post_rewrite_rules', 'dean_pm_post_rewrite_rules');

?>