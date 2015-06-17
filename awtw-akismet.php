<?php 
/************************************************************************************/
 /*	Akismet Spam Check	*/		
 /************************************************************************************/		
 Class Awtw_Akismet {
 	/**
 	 * Checks FeedBack through Akismet API for spams
 	 *
 	 * @version 1.0
 	 * @since   AWTW 1.0
 	 * @author Ritesh Sanap <riteshsanap@gmail.com>
 	 *
 	 * @param   integer   $id Id of the feedback
 	 * @return  boolean       Checks if it is spam or not
 	 */
 	public static function check($id) {
 		/**
 		 * The feedback is retrieved from the database and stored in the $item variable.
 		 */
 		$item = Awtw_DB::get($id);

 		/**
 		 * Check if Akismet Plugin is Installed and that required functions are their.
 		 */
 		 if ( ( is_callable( array( 'Akismet', 'http_post' ) )
 		  || function_exists( 'akismet_http_post' ) ) 
 		  && get_option( 'wordpress_api_key' ) ) {

 		  // Data to be sent to Akismet for Checking	
 		  	$data = array (
 		  		'blog'=> get_option( 'home' ),
 		  		'user_ip'=> $item->ip_address,
 		  		'user_agent'=> $item->agent,
 		  		'referrer'=> $item->source_url,
 		  		'comment_type'=>'feedback',
 		  		'comment_content'=> $item->feedback,
 		  		'comment_date_gmt'=>$item->created_at,
 		  		'blog_charset'=> get_option( 'blog_charset' ),
 		  		'blog_lang'=> get_locale(),
 		  		//'is_test'=>1, 
 		  		
 		  		/**
 		  		 * remove comment( forward slashes) below to check if Akismet API is working properly,
 		  		 * it will always return the feedback as spam when enabled.
 		  		 */
 		  		  
 		  		//'comment_author'=>'viagra-test-123'
 		  		);

 		  $query = '';

 		  /**
 		   * Data is prepared for sending to Akismet servers and is stored in $query.
 		   */
 		  foreach ( $data as $key => $data ) {
			if ( is_string( $data ) )
				$query .= $key . '=' . urlencode( stripslashes( $data ) ) . '&';
			}

			/**
			 * Check if Akismet http_post is available and accessible.
			 * if it is then send the data from $query to Akismet for checking.
			 *
			 * http_post function is available in only Akismet v3.0+
			 */
 		 	if ( is_callable( array( 'Akismet', 'http_post' ) ) ) {
 		 	        $response = Akismet::http_post( $query, 'comment-check' );
            } 

            if('true' == $response[1]) {
            	// The FeedBack has been determined as Spam by Akismet
            	Awtw_DB::statusUpdate($id,1);
            	return 'spam';
				} else {
					// The FeedBack is NOT a spam
					Awtw_DB::statusUpdate($id,2);
					return true;
				}   
 		 } else {
 		 	/**
 		 	 * you can run a custom function when akismet is not installed.
 		 	 */
 		 	return 'false';
 		 }
 	}
 /************************************************************************************/
 /*	Akismet Submit Spam	or HAM (False Positive) */		
 /************************************************************************************/	
 /**
  * Submit FeedBack data to Akismet for improving quality for future spam checks.
  *
  * Most of this function is similar to the check() function above, refer to it. 
  * The only changes are that data is sent for Submitting it as a Spam or HAM, by
  * taking the value through $type.
  * 
  * @version 1.0
  * @since   AWTW 1.0
  * @author Ritesh Sanap <riteshsanap@gmail.com>
  *
  * @param   integer   $id   ID of the feedback
  * @param   string   $type  Spam or HAM (False positive)
  * @return  boolean         
  */
 	public static function submit($id, $type = 'spam') {

 		$item = Awtw_DB::get($id);
 		 if ( ( is_callable( array( 'Akismet', 'http_post' ) )
 		  || function_exists( 'akismet_http_post' ) ) 
 		  && get_option( 'wordpress_api_key' ) ) {

 		  // Data to be sent to Akismet for Checking	
 		  	$data = array (
 		  		'blog'=> get_option( 'home' ),
 		  		'user_ip'=> $item->ip_address,
 		  		'user_agent'=> $item->agent,
 		  		'referrer'=> $item->source_url,
 		  		'comment_type'=>'feedback',
 		  		'comment_content'=> $item->feedback,
 		  		'comment_date_gmt'=>$item->created_at,
 		  		'blog_charset'=> get_option( 'blog_charset' ),
 		  		'blog_lang'=> get_locale(),
 		  		//'is_test'=>1,
 		  		//'comment_author'=>'viagra-test-123'
 		  		);
 		  $query = '';

 		  foreach ( $data as $key => $data ) {
			if ( is_string( $data ) )
				$query .= $key . '=' . urlencode( stripslashes( $data ) ) . '&';
			}

			if ( is_callable( array( 'Akismet', 'http_post' ) ) ) {
				/**
				 * Only change in the function is from here on below.
				 *
				 * Instead of sending the $query as comment-check which is used for check if the feedback is spam or not, 
				 * it instead sends $query as submit-spam or submit-ham which is taken through $type.
				 */
				 $response = Akismet::http_post( $query, 'submit-'.$type );
            } 
            /**
             * Checks the response, it will mostly be positive, 
             * it will return false only when their is connection error.
             */
			if ( 'Thanks for making the web a better place.' == $response[1] ){
				if ($type == 'ham') {
					// Updates the status of FeedBack to Approved.
					Awtw_DB::statusUpdate($id, 2);
				} else {
					// Updates the status of FeedBack to Spam.
					Awtw_DB::statusUpdate($id, 1);	
				}
			
 	       		return true;

				}  	else {
					/**
					 * Same as above, you can run a custom function when akismet is not installed.
					 */
        		return false;
				}
		}

 	} 		
 }
 ?>