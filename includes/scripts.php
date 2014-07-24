<?php
/**
 * Scripts
 */

/**
 * Print scripts
 *
 * @since 2.0
*/
function edd_social_discounts_print_script() {
	global $edd_options;

	// if ( ! self::$add_script )
	// 	return;
	?>
	<script type="text/javascript">

	<?php 
	/**
	 * Twitter
	 *
	 * @since 2.0
	*/
	if ( edd_social_discounts_is_enabled( 'twitter' ) ) : 
		?>
	  	window.twttr = (function (d,s,id) {
		  var t, js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return; js=d.createElement(s); js.id=id;
		  js.src="https://platform.twitter.com/widgets.js"; fjs.parentNode.insertBefore(js, fjs);
		  return window.twttr || (t = { _e: [], ready: function(f){ t._e.push(f) } });
		}(document, "script", "twitter-wjs"));

		twttr.ready(function (twttr) {
		    twttr.events.bind('tweet', function (event) {

		    //	var selector = event.target.id;
		    //	var id = jQuery( selector ).next( 'input' );
		    	//console.log ( id );

		        jQuery.event.trigger({
		            type: "productShared",
		            url: event.target.baseURI,
		        //    id: jQuery( '#' + event.target.id ).parent().parent().next('input[name=edd_sd_download_id]').val() // get ID of widget, then find value of input
		            id: jQuery( '#' + event.target.id ).closest('.edd-sd-share').next('input[name=edd_sd_download_id]').val() // get ID of widget, then find value of input
		        });
		    });

		    // twttr.events.bind('click', function (ev) { 
		    // 	console.log(ev);
		    // 	console.log( ev.target.id ); 
		    // 	console.log( jQuery( '#' + ev.target.id ).parent().parent().next('input').val() );
		    // });

		    //twttr.events.bind('click', captureElement);
		});


		<?php
		/**
		 * Facebook
		 *
		 * @since 2.0
		*/
		if ( edd_social_discounts_is_enabled( 'facebook' ) ) : 
			// defaults to en_US if left blank
			$locale = isset( $edd_options['edd_sd_facebook_locale'] ) && ! empty( $edd_options['edd_sd_facebook_locale'] ) ? $edd_options['edd_sd_facebook_locale'] : 'en_US';
			?>

			(function(d, s, id) {
			     var js, fjs = d.getElementsByTagName(s)[0];
			     if (d.getElementById(id)) {return;}
			     js = d.createElement(s); js.id = id;
			     js.src = "//connect.facebook.net/<?php echo $locale; ?>/all.js";
			     fjs.parentNode.insertBefore(js, fjs);
			 }(document, 'script', 'facebook-jssdk'));

			window.fbAsyncInit = function() {
			    // init the FB JS SDK
			    FB.init({
			      status	: true,
			      cookie	: true,                               
			      xfbml		: true                              
			    });

			    FB.Event.subscribe('edge.create', function(href, widget) {
			        jQuery.event.trigger({
			            type: "productShared",
			            url: href,
			            id: jQuery( widget ).parent().parent().next('input[name=edd_sd_download_id]').val() // get ID of widget, then find value of input
			        });     
			    });
			};
		<?php endif; ?>
		

		function captureElement(event) {

		  var dom_element = event.target;

		  var dom_id = dom_element.id;

		  var dom_class_name = dom_element.className;

		  alert("The DOM element that was clicked had this id: " + dom_id + ". And class name: " + dom_class_name);

		}



		// twttr.ready(function (twttr) {
		//   twttr.events.bind('click', mycustom);
		// });

		<?php endif; ?>

		<?php 
		/**
		 * Google +
		 *
		 * @since 2.0
		*/
		if ( edd_social_discounts_is_enabled( 'googleplus' ) ) : 
			// defaults to en_US if left blank
			$locale = isset( $edd_options['edd_sd_googleplus_locale'] ) && ! empty( $edd_options['edd_sd_googleplus_locale'] ) ? $edd_options['edd_sd_googleplus_locale'] : 'en-US';
		?>
			window.___gcfg = {
			  lang: '<?php echo $locale; ?>',
			  parsetags: 'onload'
			};

			(function() {
			    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
			    po.src = 'https://apis.google.com/js/plusone.js';
			    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
			  })();

			function plusOned(obj) {
				console.log(obj);
				jQuery.event.trigger({
				    type: "productShared",
				    url: obj.href
				});
			}
		<?php endif; ?>

		<?php 
		/**
		 * LinkedIn
		 *
		 * @since 2.0
		*/
		if ( edd_social_discounts_is_enabled( 'linkedin' ) ) : ?>
			function share(url) {
				console.log(url);
			 	jQuery.event.trigger({
		            type: "productShared",
		            url: url
		        });
			}
		
		<?php endif; ?>

		

		<?php 
		/**
		 * Listen for the productShared event
		 *
		 * @since 2.0
		*/
		if ( edd_social_discounts_is_enabled() ) : 

			//$download_id = is_singular( 'download' ) ? get_the_ID() : 123;
			?>

		/* <![CDATA[ */
		var edd_social_discount_vars = {
			"ajaxurl": "<?php echo edd_get_ajax_url(); ?>",
			"edd_sd_nonce": "<?php echo wp_create_nonce( 'edd_sd_nonce' ); ?>"
		};
		/* ]]> */

		jQuery(document).ready(function ($) {

			jQuery(document).on( 'productShared', function(e) {
			//	console.log( e.url );
			//	console.log( 'id: ' + e.id );


			//	console.log( window.location.href );

				//if( e.url == window.location.href ) {

			    	var postData = {
			            action: 'share_product',
			            product_id: e.id, // post the ID
			            nonce: edd_social_discount_vars.edd_sd_nonce
			        };

			    	$.ajax({
		            type: "POST",
		            data: postData,
		            dataType: "json",
		            url: edd_social_discount_vars.ajaxurl,
		            success: function ( share_response ) {

		                if( share_response ) {

		                	// locked downloads
		                	if ( share_response.button ) {
		                		
								jQuery( '.edd-sd-share' ).each(function( index ) {

									if ( jQuery( this ).data('id') == e.id ) {
										console.log( this );
										console.log( 'ID ' + e.id );

										// replace empty div with share button
										// should only have to do this for locked downloads
										jQuery(this).find('.edd-sd-locked').replaceWith( share_response.button );
									//	jQuery(this).find('.edd-sd-locked').addClass('test');
									}
									
								});	




		                		// jQuery( '.edd-sd-locked' ).each(function( index ) {

		                		// 	if ( jQuery( this ).data('id') == e.id ) {

		                		// 		// replace empty div with share button
		                		// 		jQuery(this).replaceWith( share_response.button );
		                		// 	}
		                			
		                		// });

		         //        		if ( jQuery('.edd-sd-locked').data('id') == 173 ) {

			    				// }

		                	//	var correct = jQuery('div[data-id=173]');

		                	//	console.log( 'correct id: ' + correct );
		                	//	jQuery('.edd-sd-locked').data('id').replaceWith( share_response.button );
		                	//	#element[data-data1=1]

		                		//jQuery('.edd_download_purchase_form').replaceWith( share_response.button );
		                		jQuery('a.edd-add-to-cart').addClass('edd-has-js');
		                		jQuery('.edd-no-js').hide();
		                	}
		                	

		                	//console.log( share_response.button );

		                    if ( share_response.msg == 'valid' ) {
		                       console.log('successfully shared');
		                       console.log( share_response );


		                       jQuery( '.edd-sd-share' ).each(function( index ) {

		                       	if ( jQuery( this ).data('id') == e.id ) {

		                       		jQuery('.edd-sd-title', this).html( share_response.success_title );
		                       		jQuery('.edd-sd-message', this).html( share_response.success_message );

		                       		// add CSS class so the box can be styled
		                       		jQuery(this).addClass('shared');
		                       	}
		                       	
		                       });	



		                      
		                    } 
		                    else {
		                        console.log('failed to share');
		                        console.log( share_response );
		                    }
		                } 
		                else {
		                    console.log( share_response );
		                }
		            }
		        }).fail(function (data) {
		            console.log( data );
		        });

				//}
			});
		});
	<?php endif; ?>

	

	</script>
	<?php
}
add_action( 'wp_footer', 'edd_social_discounts_print_script' );

/**
 * Load CSS inline to avoid extra http request. There's very minimal CSS.
 *
 * @since 2.0
*/
function edd_social_discounts_load_css() {
if ( ! edd_social_discounts_is_enabled() )
	return;
?>
	<style>.edd-sd-service { display: inline-block; margin: 0 1em 1em 0; vertical-align: top; } .edd-sd-service iframe { max-width: none; }</style>
	<?php
}
// load CSS
add_action( 'wp_head', 'edd_social_discounts_load_css' );