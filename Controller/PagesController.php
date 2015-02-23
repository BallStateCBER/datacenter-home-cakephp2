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
		$username = 'BallStateCBER';
		$client->authenticate($token, '', $method);

		$repositories = $client->api('user')->repositories($username);
		foreach ($repositories as &$repository) {
			$branches = $client->api('repo')->branches($username, $repository['name']);
			$has_master_branch = false;
			$has_dev_branch = false;
			foreach ($branches as $branch) {
				if ($branch['name'] == 'master') {
					$has_master_branch = true;
				}
				if ($branch['name'] == 'development') {
					$has_dev_branch = true;
				}
			}
			if ($has_master_branch && $has_dev_branch) {
				$compare = $client->api('repo')->commits()->compare($username, $repository['name'], 'development', 'master');
				switch ($compare['status']) {
					case 'identical':
						$repository['master_status'] = '<span class="glyphicon glyphicon-ok-sign" title="Identical"></span>';
						break;
					case 'ahead':
						$repository['master_status'] = '<span class="glyphicon glyphicon-circle-arrow-right" title="Ahead for some reason"></span> ';
						$repository['master_status'] .= $compare['ahead_by'];
						break;
					case 'behind':
						$repository['master_status'] = '<span class="glyphicon glyphicon-circle-arrow-left" title="Behind"></span> ';
						$repository['master_status'] .= $compare['behind_by'];
						break;
					default:
						$repository['master_status'] = '<span class="glyphicon glyphicon-question-sign" title="Unexpected status"></span>';
				}
			} else {
				$repository['master_status'] = '<span class="na">N/A</a>';
			}
		}

		$sorted_repos = array();
		foreach ($repositories as $i => $repository) {
			$key = $repository['pushed_at'];
			if (isset($sorted_repos[$key])) {
				$key .= $i;
			}
			$sorted_repos[$key] = $repository;
		}
		krsort($sorted_repos);
		$repositories = $sorted_repos;
		//pr($sorted_repos);

		$sites = array(
			'brownfield' => array(
				'production' => 'http://brownfield.cberdata.org',
				'development' => 'http://brownfield.localhost/'
			),
			'commentaries' => array(
				'production' => 'http://commentaries.cberdata.org',
				'development' => 'http://commentaries.localhost'
			),
			'communityAssetInventory' => array(
				'production' => 'http://asset.cberdata.org',
				'development' => 'http://qop.localhost'
			),
			'conexus' => array(
				'production' => 'http://conexus.cberdata.org',
				'development' => 'http://conexus.localhost'
			),
			'countyProfiles' => array(
				'production' => 'http://profiles.cberdata.org',
				'development' => 'http://profiles.localhost'
			),
			'cri' => array(
				'production' => 'http://cri.cberdata.org',
				'development' => 'http://cri.localhost'
			),
			'dataCenterHome' => array(
				'production' => 'http://cberdata.org',
				'development' => 'http://dchome.localhost'
			),
			'economicIndicators' => array(
				'production' => 'http://indicators.cberdata.org',
				'development' => 'http://indicators.localhost'
			),
			'ice_miller' => array(
				'production' => 'http://icemiller.cberdata.org',
				'development' => 'http://icemiller.localhost'
			),
			'muncieMusicFest' => array(
				'production' => 'http://munciemusicfest.com',
				'development' => 'http://mmf.localhost'
			),
			'muncie_events' => array(
				'production' => 'http://muncieevents.com',
				'development' => 'http://muncie_events.localhost'
			),
			'muncie_musicfest2' => array(
				'production' => 'http://munciemusicfest.com',
				'development' => 'http://mmf.localhost'
			),
			'projects' => array(
				'production' => 'http://projects.cberdata.org',
				'development' => 'http://projects.localhost'
			),
			'roundtable' => array(
				'production' => 'http://roundtable.cberdata.org',
				'development' => 'http://roundtable.localhost'
			),
			'taxCalculator' => array(
				'production' => 'http://tax-comparison.cberdata.org',
				'development' => 'http://tax_calculator.localhost'
			)
		);

		$pos = stripos(env('SERVER_NAME'), 'localhost');
		$sn_len = strlen(env('SERVER_NAME'));
		$lh_len = strlen('localhost');
		$is_localhost = ($pos !== false && $pos == ($sn_len - $lh_len));
		$this->set(array(
			'title_for_layout' => 'Data Center Overview',
			'repositories' => $repositories,
			'sites' => $sites,
			'is_localhost' => $is_localhost,
			'servers' => $is_localhost ? array('development', 'production') : array('production')
		));
	}
}
