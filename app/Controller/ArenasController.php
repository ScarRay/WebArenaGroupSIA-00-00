<?php 

App::uses('AppController', 'Controller');

/**
 * Main controller of our small application
 *
 * @author ...
 */
/*require_once("../../vendor/autoload.php");

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\GraphUser;
use Facebook\GraphSessionInfo;
use Facebook\Helpers\FacebookCanvasHelper;*/


class ArenasController extends AppController
{
	public $uses = array('User', 'Fighter', 'Event');
    /*
     *Page d'accueil
	 *
	 *Permet l'accès à la connexion et l'inscription hors connexion
     * 
	 *Permet la déconnexion et la navigation vers les autres pages en temps que connecté
     */
    public function index(){	
	
        if($this->Auth->loggedIn())$this->set('myname', strtok($this->User->findById($this->Auth->user('id'))['User']['email'],'@'));
		else $this->set('myname', "toi petit troll");


		//POUR CE CONNECTER, ERREUR SDK Facebook Pouet
		/*
		if (session_status() == PHP_SESSION_NONE){
			session_start();
		}
		$fb = new \Facebook\Facebook([
			'app_id' => '1720702151482399',
			'app_secret' => '498d1d995ef2a182e5f1760734ad57b6',
			'default_graph_version' => 'v2.4',
		]);


		$helper = $fb->getRedirectLoginHelper();
		try {
			$accessToken = $helper->getAccessToken();
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			echo $e;
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			echo $e;
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}

		if (isset($accessToken)) {
			// Logged in!
			$_SESSION['facebook_access_token'] = (string) $accessToken;
	}*/


		//POUR UTILISER LE MAIL LE NOM ETC
			/*$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);

			try {
				$response = $fb->get('/me');
				$userNode = $response->getGraphUser();
			} catch(Facebook\Exceptions\FacebookResponseException $e) {
				// When Graph returns an error
				echo 'Graph returned an error: ' . $e->getMessage();
				exit;
			} catch(Facebook\Exceptions\FacebookSDKException $e) {
				// When validation fails or other local issues
				echo 'Facebook SDK returned an error: ' . $e->getMessage();
				exit;
			}

			$this->set('fb_name',$userNode->getName());

			echo 'Logged in as ' . $userNode->getName();*/
			// Now you can redirect to another page and use the
			// access token from $_SESSION['facebook_access_token']

    }
	
	/*
	 *Page Combattant
	 *
	 *Permet de créer de nouveau combattant et de voir les caractéristiques des combattants liés au compte
	 *
	 */
	
	public function fighter(){
		
		//Récupération de la liste des noms des Fighter du User connecté
		$this->set('fighters',$this->Fighter->getFighterNameByUser($this->Auth->user('id')));
		$this->set('raw','Séléctioner un Combattant.');
		$this->set('canLevelUp',false);
		if ($this->request->is('post')){
			//Affichage des charactéristique du Fighter Séléctioné
			if(array_key_exists('FighterChoose',$this->request->data)){
				$fighter = $this->Fighter->getFighterByUserAndName($this->Auth->user('id'),$this->request->data['FighterChoose']['Combattant']);
				$this->set('raw',$fighter);
				//Détermination de la possibilité de passer un niveau
				if($this->Fighter->canLevelUp($fighter)){
					$this->set('canLevelUp',true);
					$this->set('fighter',$fighter);
				}
				else $this->set('canLevelUp',false);
			}
			//Création d'un nouveau Fighter avec un nom fournis par le User
			else if (array_key_exists('FighterCreate',$this->request->data)){
				//Création de l'Event d'arrivée dans l'arène
				$event = $this->Fighter->spawn($this->Auth->user('id'),$this->request->data['FighterCreate']['Nom']);
				//Message si l'arène est pleine et le Fighter n'a pas été créé
				if($event != null)$this->Event->record($event);
				else echo('Désolé, l\'arène est pleine ! Vous ne pouvez pas créer de nouveau combattant.');
			}
			//Passage de niveau du Fighter séléctionné
			else if (array_key_exists('FighterLevelUp',$this->request->data)){
				//Récupération du Fighter à partir de son nom et de son User
				$fighter = $this->Fighter->getFighterByUserAndName($this->Auth->user('id'),$this->request->data['FighterLevelUp']['Combattant']);
								
				// Méthode de passage de niveau avec le skill renseigné
				$fighter = $this->Fighter->levelUp($fighter,$this->request->data['FighterLevelUp']['Skill']);
				$this->set('raw',$fighter);
				
				//Détermination de la possibilité de passer un niveau
				if($this->Fighter->canLevelUp($fighter)){
					$this->set('canLevelUp',true);
					$this->set('fighter',$fighter);
				}
				else $this->set('canLevelUp',false);
			}
			// pr($this->request->data);
		}	
	}
	
	/*
	 *Page Vue
	 *
	 *Permet d'effectuer des actions avec les combattants liés au compte
	 *
	 * Déplacement - Attaque
	 *
	 */
	public function sight(){
		//Récupération de la liste des noms des Fighter du User connecté
		$this->set('fighters',$this->Fighter->getFighterNameByUser($this->Auth->user('id')));
		
		if ($this->request->is('post')){
			if(array_key_exists('FighterMove',$this->request->data))
				//Action de déplacement, création de l'Event correspondant
				$this->Event->record($this->Fighter->doMove(
										$this->Fighter->getFighterByUserAndName($this->Auth->user('id'),$this->request->data['FighterMove']['Combattant']),
										$this->request->data['FighterMove']['direction'])
									);
			else if(array_key_exists('FighterAttack',$this->request->data))
				//Action d'attaque, création de l'Event correspondant
					$this->Event->record($this->Fighter->doAttack(
												$this->Fighter->getFighterByUserAndName($this->Auth->user('id'),$this->request->data['FighterAttack']['Combattant']),
												$this->request->data['FighterAttack']['direction'])
											);
			pr($this->request->data);
		}
		 // $this->set('raw',$this->Fighter->getFightersByUser($this->Auth->user('id')));
		 // pr($this->Fighter->getFightersByUser($this->Auth->user('id')));
	}
	
	/*
	 *Page de Journal
	 *
	 *Permet de voir les événements passés dans l'arène
	 *
	 */
	public function diary(){
		$this->set('raw',$this->Event->find());
	}
}
?>
