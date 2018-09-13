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

} ?>