<?php 

class UserManager
{
	private static $user_meta_list = array();
	private static $store_meta_list = array();

	public function __construct(){
		add_action( 'wp_enqueue_scripts', array( $this, 'user_manager_add_styles'));
		add_action( 'wp_enqueue_scripts', array( $this, 'user_manager_add_scripts'));
		add_action('wp_ajax_resocosp_get_users_slug', array($this, 'get_users_slug'));
		add_action('wp_ajax_nopriv_resocosp_get_users_slug', array($this, 'get_users_slug'));
		add_action('wp_ajax_resocosp_get_users_email', array($this, 'get_users_email'));
		add_action('wp_ajax_nopriv_resocosp_get_users_email', array($this, 'get_users_email'));
		add_action('wp_ajax_resocosp_cerca_email', array($this, 'cerca_email'));
		add_action('wp_ajax_nopriv_resocosp_cerca_email', array($this, 'cerca_email'));
		add_action('wp_ajax_resocosp_registra_utente', array($this, 'registra_utente'));
		add_action('wp_ajax_resocosp_aggiorna_password', array($this, 'aggiorna_password'));
		add_action('wp_ajax_nopriv_resocosp_aggiorna_password', array($this, 'aggiorna_password'));
		add_action('wp_ajax_resocosp_crea_pagina_cliente', array($this, 'crea_pagina_cliente'));
		add_action('wp_ajax_resocosp_crea_pagina_azienda', array($this, 'crea_pagina_azienda'));
		UserManager::set_user_meta_list();
		UserManager::set_store_meta_list();
	}

	public static function activation(){}

	public static function deactivation(){}

	public function user_manager_add_styles()
	{
		wp_enqueue_style( 'user_manager_style', plugin_dir_url(__FILE__). 'static/css/main.f59490f9.css', array('bootstrap') );
	}

	public function user_manager_add_scripts()
	{
		wp_enqueue_script( 'user_manager_script', plugin_dir_url(__FILE__). 'static/js/main.91bc8f61.js', array(), '0.0.1', true );
	}

	public static function resocosp_user_registration()
	{
		echo '<div id="root"></div>';
	}

	public static function send_confirmation_mail( $username, $user_mail, $user_pwd, $user_role )
	{
		$subject = "Benvenuto in ".get_bloginfo();;
		$message = null;

		$message = self::generate_mail( $username, $user_mail, $user_pwd, $user_role );
		if($message == null) return false;
		$admin_email = get_bloginfo( 'admin_email', 'raw' );
		$headers[] = "Content-Type: text/html; charset=UTF-8";
		$sent_mail = wp_mail( $user_mail, $subject, $message, $headers );
		return $sent_mail;
	}

	public static function generate_mail( $username, $user_mail, $user_pwd, $user_role )
	{
		$mail = '<div>';
		$mail .= '<h1>Requisiti di accesso</h1>';
		$mail .= '<p><strong>Username: </strong>'.$username.'</p>';
		$mail .= '<p><strong>Email: </strong>'.$user_mail.'</p>';
		$mail .= '<p><strong>Password: </strong>'.$user_pwd.'</p>';
		$mail .= '<p><strong>Ruolo: </strong>'.$user_role.'</p>';
		$mail .= '</div>';

		return $mail;
	}

	private static function set_user_meta_list(){
		array_push(self::$user_meta_list, 
						'is_public', 
						'data_nascita', 
						'sesso', 
						'indirizzo', 
						'cap', 
						'provincia', 
						'comune', 
						'id_pagina',
						'categorie_preferite', 
						'utenti_amici', 
						'negozi_seguiti', 
						'prodotti_preferiti');
	}

	public static function get_user_meta_list(){
		return self::$user_meta_list;
	}

	private static function set_store_meta_list(){
		array_push(self::$store_meta_list, 
						'is_public', 
						'indirizzo', 
						'cap', 
						'provincia', 
						'comune', 
						'partita_iva', 
						'codice_fiscale', 
						'id_pagina', 
						'prodotti', 
						'notizie', 
						'promozioni');
	}

	public static function get_store_meta_list(){
		return self::$store_meta_list;
	}

	public static function initialize_user_meta($user_id){

		foreach(self::$user_meta_list as $meta_key){
			add_user_meta( $user_id, $meta_key, null, false );
		}
	}

	public static function initialize_store_meta($user_id){

		foreach(self::$store_meta_list as $meta_key){
			add_user_meta( $user_id, $meta_key, null, false );
		}
	}

	public static function  set_user_meta_values($user_id, $meta_values){
		$chiavi = UserManager::get_user_meta_list();
		for($i = 0; $i < sizeof($meta_values); $i++) {
			$chiave = $chiavi[$i];
			update_user_meta( $user_id, $chiave, $meta_values[$chiave] );
		}

	}

	public static function set_store_meta_values($user_id, $meta_values){
		$chiavi = UserManager::get_store_meta_list();
		for($i = 0; $i < sizeof($meta_values); $i++) {
			$chiave = $chiavi[$i];
			update_user_meta( $user_id, $chiave, $meta_values[$chiave] );
		}
	}

	public static function create_user_page($user_id){

		$parent = get_page_by_title( 'Utente', OBJECT, 'page' );
		$user = get_userdata($user_id);
		$postarr = array(
				'post_title' => $user->display_name,
				'post_name' => $user->user_login,
				'post_content' => '',
				'post_type' => 'page',
				'post_parent' => $parent->ID,
				'post_author' => $user_id,
				'post_status' =>'publish'
				);

		$id_page = wp_insert_post( $postarr, false );
		return $id_page;
	}

	public static function create_store_page($user_id){

		$parent = get_page_by_title( 'Negozio', OBJECT, 'page' );
		$user = get_userdata($user_id);
		$postarr = array(
				'post_title' => $user->display_name,
				'post_name' => $user->user_login,
				'post_content' => '',
				'post_type' => 'page',
				'post_parent' => $parent->ID,
				'post_author' => $user_id,
				'post_status' =>'publish'
				);

		$id_page = wp_insert_post( $postarr, false );
		return $id_page;
	}

	public static function delete_user($user_id){
		require_once (ABSPATH . 'wp-admin/includes/user.php');

		foreach(self::$user_meta_list as $meta_key){
			delete_user_meta( $user_id, $meta_key );
		}
		$deleted = wp_delete_user( $user_id, null );
		return $deleted;
	}

	public static function delete_store($user_id){
		require_once (ABSPATH . 'wp-admin/includes/user.php');

		foreach(self::$store_meta_list as $meta_key){
			delete_user_meta( $user_id, $meta_key );
		}
		$deleted = wp_delete_user( $user_id, null );
		return $deleted;
	}

	public function get_users_slug(){
		$args = array(
			'role' => ''
		);

		$user_query = new WP_User_Query($args);
		if( !empty($user_query->results)){
			$users_slug = array();
			foreach($user_query->results as $user){
				array_push($users_slug, $user->user_login);
			}
			echo json_encode($users_slug);
		}else{
			$var["message"] = "no users";
			echo json_encode($var);
		}
		die();
	}

	public function get_users_email(){
		$args = array(
			'role' => ''
		);

		$user_query = new WP_User_Query($args);
		if( !empty($user_query->results)){
			$users_email = array();
			foreach($user_query->results as $user){
				array_push($users_email, $user->user_email);
			}
			echo json_encode($users_email);
		}else{
			$var["message"] = "no users";
			echo json_encode($var);
		}
		die();
	}

	public function cerca_email(){

		$email = $_GET['email'];

		$user = get_user_by( 'email', $email );

		if(is_wp_error($user)){
			echo false;
		}
		else {
			$response['sent-mail'] = send_restore_password_mail($user->data, $email);
			echo json_encode($response);
		}
		die();
	}

	public function registra_utente(){
		$password = wp_generate_password( 10, false, false );
		$username = $_POST['username'];
		$email = $_POST["email"];
		$ruolo = $_POST["ruolo"];
		$new_user = wp_create_user( $username, $password, $email );

		if(is_wp_error( $new_user )){
			echo json_encode($new_user);
		}
		else{

			$set_user_role = wp_update_user(array(
				'ID' => $new_user,
				'role' => $ruolo
				)
			);

			if(is_wp_error( $set_user_role )){
				echo json_encode($set_user_role);
			}
			else{
				//imposta i meta dati del profilo
				if($ruolo == 'cliente') UserManager::initialize_user_meta($new_user);
				if($ruolo == 'admin-azienda') UserManager::initialize_store_meta($new_user);
				$msg['messaggio'] = false;
				$msg['messaggio'] = UserManager::send_confirmation_mail($username, $email, $password, $ruolo);
				echo json_encode($msg);
			}
		}
		die();
	}

	public function aggiorna_password(){

		$response["passwordSet"] = false;
		if(wp_verify_nonce( $_GET["nonce"]))
		{
			wp_set_password( $_GET['password'], $_GET['id_user'] );
			$response["passwordSet"] = true;
			echo json_encode($response);
		}
		else{
			$response["passwordSet"] = false;
			echo json_encode($response);
		}
		die();
	}

	public function crea_pagina_cliente(){

		if(isset($_POST['id'])){

			$display_name = $_POST['nome'].' '.$_POST['cognome'];

			//aggiorna utente
			$user_updated = wp_update_user( array(
				'ID' 			=> $_POST['id'],
				'first_name' 	=> $_POST['nome'],
				'last_name' 	=> $_POST['cognome'],
				'description' 	=> $_POST['descrizione'],
				'display_name'	=> $display_name
			));
			if(is_wp_error( $user_updated )){
				$response["response"] = "Errore aggiornamento cliente";
				echo json_encode($response);
			}
			else{
			// crea pagina utente
				$id_pagina = UserManager::create_user_page($_POST['id']);

			// salva immagine del profilo come immagine del post
				if(isset($_POST['id_immagine'])){
					// recupera dati immagine default
					set_post_thumbnail( $id_pagina, $_POST['id_immagine']);
				}
				else{
					// inserisci l'immagine caricata
					$this->user_image_upload($_FILES['immagine'], $id_pagina, true);
				}

				// inserisci meta dati utente
				$meta_keys = array(
					'is_public' 	=> $_POST['pubblico'],
					'data_nascita'	=> $_POST['dataNascita'],
					'sesso'			=> $_POST['sesso'],
					'indirizzo'		=> $_POST['indirizzo'],
					'cap'			=> $_POST['cap'],
					'provincia'		=> $_POST['provincia'],
					'comune'		=> $_POST['comune'],
					'categorie_preferite'	=> $_POST['categoriePreferite'],
					'id_pagina'		=> $id_pagina
				);
				UserManager::set_user_meta_values($_POST['id'],  $meta_keys );

				$response["response"] = true;
				echo json_encode($response);
			}
		}
		else{
			$response["response"] = "No data";
			echo json_encode($response);
		}
		die();
	}

	public function crea_pagina_azienda(){
		if(isset($_POST['dati'])){
			
			$display_name = $_POST['dati']['ragSociale'];

			//aggiorna utente
			$user_updated = wp_update_user( array(
				'ID' 			=> $_POST['dati']['id'],
				'first_name' 	=> $_POST['dati']['nomeTitolare'],
				'last_name' 	=> $_POST['dati']['cognomeTitolare'],
				'display_name'	=> $display_name
			));
			if(is_wp_error( $user_updated )){
				$response["response"] = "Errore aggiornamento cliente";
				echo json_encode($response);
			}
			else{
			// crea pagina utente
				$id_pagina = UserManager::create_store_page($_POST['dati']['id']);

			// salva immagine del profilo come immagine del post
				if(isset($_POST['id_immagine'])){
					// recupera dati immagine default
					set_post_thumbnail( $id_pagina, $_POST['id_immagine']);
				}
				else{
					// inserisci l'immagine caricata
					$this->user_image_upload($_FILES['immagine'], $id_pagina, true);
				}

			// inserisci meta dati utente
				$meta_keys = array(
					'is_public' 	=> $_POST['dati']['pubblico'],
					'indirizzo'		=> $_POST['dati']['indirizzo'],
					'cap'			=> $_POST['dati']['cap'],
					'provincia'		=> $_POST['dati']['provincia'],
					'comune'		=> $_POST['dati']['comune'],
					'codice_fiscale'=> $_POST['dati']['codiceFiscale'],
					'partita_iva'	=> $_POST['dati']['partitaIva'],
					'id_pagina'		=> $id_pagina
				);
				UserManager::set_user_meta_values($_POST['dati']['id'],  $meta_keys );

				$response["response"] = true;
				echo json_encode($response);
			}
		}
		else{
			$response["response"] = "No data";
			echo json_encode($response);
		}
		die();
	}

	public function user_image_upload($file, $post_id = 0 , $set_as_featured = false){

	    $upload = wp_upload_bits( $file['name'], null, file_get_contents( $file['tmp_name'] ) );

	    $wp_filetype = wp_check_filetype( basename( $upload['file'] ), null );

	    $wp_upload_dir = wp_upload_dir();

	    $attachment = array(
	        'guid' => $wp_upload_dir['baseurl'] . _wp_relative_upload_path( $upload['file'] ),
	        'post_mime_type' => $wp_filetype['type'],
	        'post_title' => preg_replace('/\.[^.]+$/', '', basename( $upload['file'] )),
	        'post_content' => '',
	        'post_status' => 'inherit'
	    );
	    
	    $attach_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );

	    require_once(ABSPATH . 'wp-admin/includes/image.php');

	    $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
	    wp_update_attachment_metadata( $attach_id, $attach_data );

	    if( $set_as_featured == true ) {
	        update_post_meta( $post_id, '_thumbnail_id', $attach_id );
	    }
	}
}

?>