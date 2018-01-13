<?php

$parentColor = 'glass';

/* SETTINGS START */

$themeSettings['bar_background'] = setColorValue('bar_background','#424D5D',$parentColor);
$themeSettings['bar_gradient_start'] = setColorValue('bar_gradient_start','#FFFFFF',$parentColor);
$themeSettings['bar_gradient_end'] = setColorValue('bar_gradient_end','#CCCCCC',$parentColor);
$themeSettings['bar_border'] = setColorValue('bar_border','#707070',$parentColor);
$themeSettings['bar_color'] = setColorValue('bar_color','#333333',$parentColor);
$themeSettings['bar_color_disabled'] = setColorValue('bar_color_disabled','#A7A7A7',$parentColor);
$themeSettings['bar_border_light'] = setColorValue('bar_border_light','#E5E5E5',$parentColor);
$themeSettings['bar_font_family'] = setColorValue('bar_font_family','"Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif',$parentColor);
$themeSettings['bar_font_size'] = setColorValue('bar_font_size','12px',$parentColor);
$themeSettings['bar_text_background'] = setColorValue('bar_text_background','#FFFFFF',$parentColor);
$themeSettings['tab_background'] = setColorValue('tab_background','#FFFFFF',$parentColor);
$themeSettings['tab_color'] = setColorValue('tab_color','#111111',$parentColor);
$themeSettings['tab_border'] = setColorValue('tab_border','#CCCCCC',$parentColor);
$themeSettings['tab_font_family'] = setColorValue('tab_font_family','"Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif',$parentColor);
$themeSettings['tab_font_size'] = setColorValue('tab_font_size','12px',$parentColor);
$themeSettings['tab_font_size_small'] = setColorValue('tab_font_size_small','11px',$parentColor);
$themeSettings['tab_color_self'] = setColorValue('tab_color_self','#333333',$parentColor);
$themeSettings['tab_title_color'] = setColorValue('tab_title_color','#FFFFFF',$parentColor);
$themeSettings['tab_title_backgroud_light'] = setColorValue('tab_title_backgroud_light','#E5E5E5',$parentColor);
$themeSettings['tab_title_background'] = setColorValue('tab_title_background','#424D5D',$parentColor);
$themeSettings['tab_title_gradient_start'] = setColorValue('tab_title_gradient_start','#424D5D',$parentColor);
$themeSettings['tab_title_gradient_center'] = setColorValue('tab_title_gradient_center','#424D5D',$parentColor);
$themeSettings['tab_title_gradient_end'] = setColorValue('tab_title_gradient_end','#424D5D',$parentColor);
$themeSettings['tab_title_border'] = setColorValue('tab_title_border','#707070',$parentColor);
$themeSettings['tab_title_font_family'] = setColorValue('tab_title_font_family','"Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif',$parentColor);
$themeSettings['tab_title_font_size'] = setColorValue('tab_title_font_size','13px',$parentColor);
$themeSettings['tab_title_font_size_large'] = setColorValue('tab_title_font_size_large','12px',$parentColor);
$themeSettings['tab_title_text_background'] = setColorValue('tab_title_text_background','#424D5D',$parentColor);
$themeSettings['tab_sub_background'] = setColorValue('tab_sub_background','#E7E7E7',$parentColor);
$themeSettings['tab_sub_color'] = setColorValue('tab_sub_color','#666666',$parentColor);
$themeSettings['tab_border_light'] = setColorValue('tab_border_light','#CCCCCC',$parentColor);
$themeSettings['tab_border_lighter'] = setColorValue('tab_border_lighter','#FFFFFF',$parentColor);
$themeSettings['tooltip_background'] = setColorValue('tooltip_background','#333333',$parentColor);
$themeSettings['tooltip_color'] = setColorValue('tooltip_color','#FFFFFF',$parentColor);
$themeSettings['tooltip_color_light'] = setColorValue('tooltip_color_light','#EEEEEE',$parentColor);
$themeSettings['tooltip_font_family'] = setColorValue('tooltip_font_family','"Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif',$parentColor);
$themeSettings['tooltip_font_size'] = setColorValue('tooltip_font_size','11px',$parentColor);
$themeSettings['tooltip_break'] = setColorValue('tooltip_break','#666666',$parentColor);
$themeSettings['tab_link_color'] = setColorValue('tab_link_color','#424D5D',$parentColor);
$themeSettings['messagecount_background'] = setColorValue('messagecount_background','#96281B',$parentColor);

/* SETTINGS END */

global $color;
if(!defined('CCADMIN')){
	$themeSettings = setNewColorValue($themeSettings,$color);
}