<?php

class Helpers{

	public static function is_tree($pid) {  
		global $post;         
		if( ( $post->post_parent == $pid || is_page($pid) || get_the_ID() === $pid ) ) {
			// we're at the page or at a sub page
			return true;
		}  else{
			return false;  
		} 
	}

	public static function filter_categories(){
		$terms = get_the_terms( $post, 'category' );
		if( $terms ):
			foreach ($terms as $term) :
				echo 'filter-';
				echo $term->slug;
				echo ' ';
			endforeach;
		endif;
	}

} ?>