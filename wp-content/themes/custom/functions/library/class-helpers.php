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

	public static function filter_categories($taxonomy){
		$terms = get_the_terms( $post, $taxonomy);
		if( $terms ):
			foreach ($terms as $term) :
				echo '';
				echo $term->slug;
				echo ' ';
			endforeach;
		endif;
	}

} ?>