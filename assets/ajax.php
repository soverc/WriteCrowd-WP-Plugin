<?php
	require_once(WP_ONEIGHTY_CLASS_PATH.'/xmlrpc/lib/xmlrpc.inc');
	$request = new xmlrpc_client(WP_ONEIGHTY_XMLRPC);
		$request->return_type = 'phpvals';
		
		switch ($_POST['action']) {
			case 'oneighty_submit_comment' : 
				$data    = new xmlrpcmsg('comments.post', array(
					new xmlrpcval(WP_ONEIGHTY_API_KEY, 'string'), // Param 0 (API Key)
					new xmlrpcval(rtrim(strip_tags($_POST['article_id'])), 'string'),   // Param 1 (Article ID)
					new xmlrpcval(rtrim(strip_tags($_POST['author_id'])), 'string'),    // Param 2 (Author ID)
					new xmlrpcval(rtrim(strip_tags($_POST['author_name'])), 'string'),  // Param 3 (Author Name)
					new xmlrpcval(rtrim(strip_tags($_POST['author_email'])), 'string'), // Param 4 (Author Email)
					new xmlrpcval(rtrim(strip_tags($_POST['author_url'])), 'string'),   // Param 5 (Author URL)
					new xmlrpcval(rtrim(strip_tags($_POST['author_ip'])), 'string'),    // Param 6 (Author IP)
					new xmlrpcval(rtrim(strip_tags($_POST['content'])), 'string'),      // Param 7 (Comment Content)
					new xmlrpcval('0', 'string'),                                       // Param 8 (Parent ID)
					new xmlrpcval('0', 'string')                                        // Param 9 (Site ID)
				));
						
				$results = $request->send($data);
				$results = $results->val;
						
				if (isset($results['error'])) {
					$ajax['data']         = $results['error'];
					$ajax['supplemental'] = array(
						'message_type' => 'error'
					);
				} else {
					$ajax['data'] = 'Article successfully submitted!';
					$ajax['supplemental'] = array(
						'message_type' => 'success'
					);
				}
			break;
		}
	print(json_encode($ajax));
?>