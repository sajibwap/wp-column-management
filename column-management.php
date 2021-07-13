<?php 

/*
Plugin Name: Column Management
Plugin URI: http://msajib.com
Description: Our Column Management description
Version: 1.00
Author: Sajib
Author URI: http://msajib.com
Text Domain: textdomain
Domain Path: /languages/
*/


// Column Management
// =================
// 1. Remove a column
// 2. Change Column Order to the end
// 3. Add a new column and show data
// 4. Sortable admin column
// 5. Add filter by word post id, thumbnail, word count


function textdomain_bootstrap(){
	load_plugin_textdomain( 'textdomain', false, dirname(__FILE__) . '/languages');
}
add_action( 'plugins_loaded', 'textdomain_bootstrap' );

/*
** Add, Remove and Rename Column 
*/

function cm_manage_post_column($column){
	$column['author'] = 'Publisher';
	unset($column['comments']);
	unset($column['tags']);
	$column['post_id'] 	 = __('Post ID','textdomain');
	$column['wordcount'] = 'Word count';
	$column['thumbnail'] = 'Image';
	return $column;
}
add_filter( 'manage_posts_columns', 'cm_manage_post_column');

/*
** Show data to new column 
*/

function cm_manage_posts_custom_column($column,$post_id){
	if ($column=='post_id') {
		echo $post_id;
	}if ($column=='thumbnail') {
		echo get_the_post_thumbnail( $post_id, array(80,80) );
	}elseif ($column=='wordcount') {
		//$word_count = str_word_count(strip_tags(get_the_content( $post_id )));
		$word_count = get_post_meta( $post_id, 'wordn', true );
		echo $word_count;
	}
}
add_action( 'manage_posts_custom_column', 'cm_manage_posts_custom_column', 10, 2 );

/*
** Make a column sortable
*/

function cm_edit_post_sortable($columns){
	$columns['wordcount']='word_count';
	return $columns;
}
add_action( 'manage_edit-post_sortable_columns','cm_edit_post_sortable' );

/*
** add word count meta to existing post
*/
function cm_set_word_data_to_meta_val(){

	$posts = get_posts(array('post_type'=>'post','post_per_page'=>-1));
	foreach ($posts as $_post) {
		$word_count = str_word_count(strip_tags($_post->post_content));
		update_post_meta( $_post->ID, 'wordn', $word_count );
	}
}
add_action( 'init', 'cm_set_word_data_to_meta_val');

/*
 ** Sort Data based on orderby = wordn
 */

function cm_sort_column_data($wp_query){
	if (!is_admin()) {
		return;
	}

	$orderby = $wp_query->get('orderby');
	if ('wordn'==$orderby) {
		$wp_query->set('meta_key','word_count');
		$wp_query->set('orderby','meta_value_num');
	}

}
add_action( 'pre_get_posts', 'cm_sort_column_data' );

/*
** Update word count meta
*/

function cm_update_meta_val($post_id){
	$word_count = str_word_count(strip_tags(get_post($post_id)->post_content));
	update_post_meta( $post_id, 'wordn', $word_count );
}
add_action( 'save_post', 'cm_update_meta_val' );


/**************************
** Add Custom Filter Option
****************************/

/*
** Filter by perticuler post
*/

function cm_filter_option(){
	if (isset($_GET['post_type']) && $_GET['post_type'] !='post') {
		return;
	}

	$filter_value = isset($_GET['FILTER']) ? $_GET['FILTER'] : "";

	$filter_arr = array(
		'0'=>'Select a option',
		'1'=>'Option one',
		'2'=>'Option two'
	);

	$dropdown_html = '';

	foreach ($filter_arr as $key => $option) {
		$dropdown_html .= sprintf('<option value="%s" %s>%s</option>',$key,
			$key == $filter_value ? 'selected':'' , $option);
	}

	$filter_html = <<<EOD
	<select name="FILTER">{$dropdown_html}</select>
	EOD;

	echo $filter_html;
}
add_action( 'restrict_manage_posts', 'cm_filter_option' );

function cm_filter_post($wp_query){

	$filter_value = isset($_GET['FILTER']) ? $_GET['FILTER'] : "";

	if ('1'==$filter_value) {
		$wp_query->set('post__in',array(4,76));
	}elseif('2'==$filter_value) {
		$wp_query->set('post__in',array(82,89));
	}

}
add_action( 'pre_get_posts', 'cm_filter_post' );


/*
** Filter by Thumbnail
*/

function cm_filter_thumbnail(){
	if (isset($_GET['post_type']) && $_GET['post_type'] !='post') {
		return;
	}

	$filter_value = isset($_GET['IMGFILTER']) ? $_GET['IMGFILTER'] : "";

	$filter_arr = array(
		'0'=>'Select a status',
		'1'=>'Has Thumbnail',
		'2'=>'No Thumbnail'
	);

	$dropdown_html = '';

	foreach ($filter_arr as $key => $option) {
		$dropdown_html .= sprintf('<option value="%s" %s>%s</option>',$key,
			$key == $filter_value ? 'selected':'' , $option);
	}

	$filter_html = <<<EOD
	<select name="IMGFILTER">{$dropdown_html}</select>
	EOD;

	echo $filter_html;
}
add_action( 'restrict_manage_posts', 'cm_filter_thumbnail' );

function cm_filter_img($wp_query){

	$filter_value = isset($_GET['IMGFILTER']) ? $_GET['IMGFILTER'] : "";

	if ('1'==$filter_value) {
		$wp_query->set('meta_query',array(
			array(
				'key'=>'_thumbnail_id',
				'compare'=>'EXISTS'
			)
		));
	}elseif('2'==$filter_value) {
		$wp_query->set('meta_query',array(
			array(
				'key'=>'_thumbnail_id',
				'compare'=>'NOT EXISTS'
			)
		));
	}

}
add_action( 'pre_get_posts', 'cm_filter_img' );


/*
** Filter by Wordcount Limit
*/

function cm_filter_wordcount(){
	if (isset($_GET['post_type']) && $_GET['post_type'] !='post') {
		return;
	}

	$filter_value = isset($_GET['WCFILTER']) ? $_GET['WCFILTER'] : "";

	$filter_arr = array(
		'0'=>'Select a limit',
		'1'=>'1-10',
		'2'=>'10++'
	);

	$dropdown_html = '';

	foreach ($filter_arr as $key => $option) {
		$dropdown_html .= sprintf('<option value="%s" %s>%s</option>',$key,
			$key == $filter_value ? 'selected':'' , $option);
	}

	$filter_html = <<<EOD
	<select name="WCFILTER">{$dropdown_html}</select>
	EOD;

	echo $filter_html;
}
add_action( 'restrict_manage_posts', 'cm_filter_wordcount' );

function cm_filter_wc($wp_query){

	$filter_value = isset($_GET['WCFILTER']) ? $_GET['WCFILTER'] : "";

	if ('1'==$filter_value) {
		$wp_query->set('meta_query',array(
			array(
				'key'=>'wordn',
				'value'=>10,
				'compare'=>'<=',
				'type'=>'NUMERIC'
			)
		));
	}elseif('2'==$filter_value) {
		$wp_query->set('meta_query',array(
			array(
				'key'=>'wordn',
				'value'=>11,
				'compare'=>'>=',
				'type'=>'NUMERIC'
			)
		));
	}

}
add_action( 'pre_get_posts', 'cm_filter_wc' );