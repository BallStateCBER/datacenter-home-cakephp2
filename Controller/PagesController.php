<?php
/**
 * Static content controller.
 *
 * This file will render views from views/pages/
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

App::uses('AppController', 'Controller');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Pages';

/**
 * Default helper
 *
 * @var array
 */
	public $helpers = array('Html', 'Session');

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array();

/**
 * Displays a view
 *
 * @param mixed What page to display
 * @return void
 */
	public function display() {
		$path = func_get_args();

		$count = count($path);
		if (!$count) {
			$this->redirect('/');
		}
		$page = $subpage = $title_for_layout = null;

		if (!empty($path[0])) {
			$page = $path[0];
		}
		if (!empty($path[1])) {
			$subpage = $path[1];
		}
		if (!empty($path[$count - 1])) {
			$title_for_layout = Inflector::humanize($path[$count - 1]);
		}
		$this->set(compact('page', 'subpage', 'title_for_layout'));
		$this->render(implode('/', $path));
	}

	public function home() {
		$this->set(array(
			'title_for_layout' => ''
		));
	}

	public function phpinfo() {
		$this->set('title_for_layout', 'PHP Info');
	}

	public function commentaries_redirect() {
		$url = 'http://commentaries.cberdata.org/';
		if (! empty($this->params['pass'])) {
			if ($this->params['pass'][0] == 'view') {
				$url .= 'commentary/'.$this->params['pass'][1];
			} else {
				$url .= 'commentaries/'.implode('/', $this->params['pass']);
			}
		}
		$this->redirect($url);
	}

	/* This simply refreshes the cached information about the latest
	 * release from the Projects and Publications page.
	 * AppController::__getLatestRelease() is called in
	 * AppController::beforeRender(), so just removing what's cached
	 * will cause the data to be re-imported automatically. */
	public function refresh_latest_release() {
		Cache::write('latest_release', array());
		// So ReleasesController::__updateDataCenterHome() in Projects and Publications returns TRUE
		echo 1;
		$this->layout = 'DataCenter.blank';
		$this->render('DataCenter.Common/blank');
	}

	public function overview() {
		require_once('../Vendor/php-github-api/lib/Github/Client.php');
		require_once('../Vendor/php-github-api/vendor/autoload.php');

		$client = new \Github\Client();

		$token = Configure::read('github_api_token');
		$method = Github\Client::AUTH_HTTP_TOKEN;
		$client->authenticate($token, '', $method);

		$repositories = $client->api('user')->repositories('BallStateCBER');
		//pr($repositories);
		foreach ($repositories as &$repository) {
			$master_branch = $client->api('repo')->branches('BallStateCBER', 'brownfield', 'master');
			$dev_branch = $client->api('repo')->branches('BallStateCBER', 'brownfield', 'master');
			if ($master_branch && $dev_branch) {
				$repository['master_synced'] = ($master_branch['commit']['sha'] == $dev_branch['commit']['sha']) ? 1 : -1;
			} else {
				$repository['master_synced'] = 0;
			}
		}

		$sites = array(
			'brownfield' => 'http://brownfield.cberdata.org/',
			'commentaries' => 'http://commentaries.cberdata.org/',
			'communityAssetInventory' => 'http://asset.cberdata.org/',
			'conexus' => 'http://conexus.cberdata.org/',
			'countyProfiles' => 'http://profiles.cberdata.org/',
			'cri' => 'http://cri.cberdata.org/',
			'dataCenterHome' => 'http://cberdata.org/',
			'economicIndicators' => 'http://indicators.cberdata.org/',
			'ice_miller' => 'http://icemiller.cberdata.org/',
			'muncieMusicFest' => 'http://munciemusicfest.com',
			'muncie_events' => 'http://muncieevents.com',
			'muncie_musicfest2' => 'http://munciemusicfest.com',
			'projects' => 'http://projects.cberdata.org/',
			'roundtable' => 'http://roundtable.cberdata.org/',
			'taxCalculator' => 'http://tax-comparison.cberdata.org/'
		);

		$this->set(array(
			'title_for_layout' => 'Data Center Overview',
			'repositories' => $repositories,
			'sites' => $sites
		));
	}
}
