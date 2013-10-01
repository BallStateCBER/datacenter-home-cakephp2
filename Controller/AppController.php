<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
	public $helpers = array('Js' => array('Jquery'));
	public $components = array('DataCenter.Flash');
	
	// Pulls the latest release from the Projects and Publications site
	protected function __importLatestRelease() {
		// Development server
		if (stripos($_SERVER['SERVER_NAME'], 'localhost') !== false) {
			$url = 'http://projects.localhost/releases/latest';
		// Production server
		} else {
			$url = 'http://projects.cberdata.org/releases/latest';	
		}
		$results = file_get_contents($url);
		return unserialize($results);
	}
	
	protected function __getLatestRelease() {
		Cache::write('latest_release', array());
		$release = Cache::read('latest_release');
		if (empty($release['cached_time']) || $release['cached_time'] < strtotime('-1 day')) {
			$release = $this->__importLatestRelease();
			if (! empty($release)) {
				$release['cached_time'] = time();
				Cache::write('latest_release', $release);
			}
		}
		return $release;
	}
	
	public function beforeRender() {
		$this->set(array(
			'latest_release' => $this->__getLatestRelease()
		));	
	}
}
