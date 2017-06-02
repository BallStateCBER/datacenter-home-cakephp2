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
		// Connect to GitHub API
		require_once('../Vendor/php-github-api/lib/Github/Client.php');
		require_once('../Vendor/php-github-api/vendor/autoload.php');
		$client = new \Github\Client();
		$token = Configure::read('github_api_token');
		$method = Github\Client::AUTH_HTTP_TOKEN;
		$username = 'BallStateCBER';
		$client->authenticate($token, '', $method);

		// Loop through all of BallStateCBER's repos
		$repositories = $client->api('user')->repositories($username);
		foreach ($repositories as $i => $repository) {

			// Figure out what branches this repo has
			$branches = $client->api('repo')->branches($username, $repository['name']);
			$has_master_branch = false;
			$has_dev_branch = false;
			$extra_branches = array();
			foreach ($branches as $branch) {
				if ($branch['name'] == 'master') {
					$has_master_branch = true;
					$master_sha = $branch['commit']['sha'];
				} elseif ($branch['name'] == 'development') {
					$has_dev_branch = true;
					$dev_sha = $branch['commit']['sha'];
				} else {
					$extra_branches[$branch['name']] = $branch['commit']['sha'];
				}
				$repositories[$i]['branches'][] = $branch['name'];
			}

			// Determine which branch the master branch should be compared to
			$base_branch = $has_dev_branch ? 'development' : null;
			if ($has_master_branch && ! empty($extra_branches)) {
				$freshest_branch = null;
				$updated = null;
				if ($has_dev_branch) {
					$dev_commit = $client->api('repo')->commits()->show($username, $repository['name'], $dev_sha);
					$freshest_branch = 'development';
					$updated = $dev_commit['commit']['committer']['date'];
				}
				foreach ($extra_branches as $branch_name => $branch_sha) {
					$commit = $client->api('repo')->commits()->show($username, $repository['name'], $branch_sha);
					if ($commit['commit']['committer']['date'] > $updated) {
						$freshest_branch = $branch_name;
						$updated = $commit['commit']['committer']['date'];
					}
				}
				$base_branch = $freshest_branch;
			}

			// Determine how ahead/behind master is vs. most recently-updated non-master branch
			$can_compare = $has_master_branch && $base_branch;
			if ($can_compare) {
				$compare = $client->api('repo')->commits()->compare($username, $repository['name'], $base_branch, 'master');
				switch ($compare['status']) {
					case 'identical':
						$repositories[$i]['master_status'] = '<span class="glyphicon glyphicon-ok-sign" title="Identical"></span>';
						break;
					case 'ahead':
						$ahead_branch = $base_branch ? " of $base_branch" : '';
						$repositories[$i]['master_status'] = '<span class="glyphicon glyphicon-circle-arrow-right" title="Ahead'.$ahead_branch.' for some reason"></span> ';
						$repositories[$i]['master_status'] .= $compare['ahead_by'];
						break;
					case 'behind':
						$behind_branch = $base_branch ? " $base_branch" : '';
						$repositories[$i]['master_status'] = '<span class="glyphicon glyphicon-circle-arrow-left" title="Behind'.$behind_branch.'"></span> ';
						$repositories[$i]['master_status'] .= $compare['behind_by'];
						break;
					default:
						$repositories[$i]['master_status'] = '<span class="glyphicon glyphicon-question-sign" title="Unexpected status"></span>';
				}
			} else {
				$repositories[$i]['master_status'] = '<span class="na">N/A</a>';
			}
		}

		// Sort by last push
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

		$sites = array(
			'brownfield' => array(
				'title' => 'Brownfield Grant Writers\' Tool',
				'production' => 'http://brownfield.cberdata.org',
				'development' => 'http://brownfield.localhost/'
			),
            'brownfields-updater' => array(
                'title' => 'Brownfield Grant Writers\' Tool Data Importer'
            ),
            'cber-data-grabber' => array(
                'title' => 'CBER Data Grabber'
            ),
			'commentaries' => array(
				'title' => 'Weekly Commentaries',
				'production' => 'http://commentaries.cberdata.org',
				'development' => 'http://commentaries.localhost'
			),
			'communityAssetInventory' => array(
				'title' => 'Community Asset Inventory',
				'production' => 'http://asset.cberdata.org',
				'development' => 'http://qop.localhost'
			),
			'conexus' => array(
				'title' => 'Conexus Indiana Report Card',
				'production' => 'http://conexus.cberdata.org',
				'development' => 'http://conexus.localhost'
			),
			'countyProfiles' => array(
				'title' => 'County Profiles',
				'production' => 'http://profiles.cberdata.org',
				'development' => 'http://profiles.localhost'
			),
            'county-profiles-updater' => array(
                'title' => 'County Profiles Updater'
            ),
			'dataCenterHome' => array(
				'title' => 'CBER Data Center Home',
				'production' => 'http://cberdata.org',
				'development' => 'http://dchome.localhost'
			),
            'datacenter-plugin-cakephp3' => array(
                'title' => 'Data Center Plugin (CakePHP 3)'
            ),
			'economicIndicators' => array(
				'title' => 'Economic Indicators',
				'production' => 'http://indicators.cberdata.org',
				'development' => 'http://indicators.localhost'
			),
			'ice_miller' => array(
				'title' => 'Ice Miller / EDGE Articles',
				'production' => 'http://icemiller.cberdata.org',
				'development' => 'http://icemiller.localhost'
			),
			'mfg-scr-crd' => array(
			    'title' => 'Manufacturing Scorecard'
            ),
			'muncieMusicFest2' => array(
				'title' => 'Muncie MusicFest (CakePHP 3)',
				'production' => 'http://munciemusicfest.com',
				'development' => 'http://mmf.localhost'
			),
			'muncie_events' => array(
				'title' => 'Muncie Events (CakePHP 2)',
				'production' => 'http://muncieevents.com',
				'development' => 'http://muncie-events.localhost'
			),
            'muncie_events3' => array(
                'title' => 'Muncie Events (CakePHP 3)'
            ),
			'projects' => array(
				'title' => 'CBER Projects and Publications',
				'production' => 'http://projects.cberdata.org',
				'development' => 'http://projects.localhost'
			),
			'roundtable' => array(
				'title' => 'BSU Roundtable (CakePHP 2)'
			),
			'taxCalculator' => array(
				'title' => 'Tax Savings Calculator',
				'production' => 'http://tax-comparison.cberdata.org',
				'development' => 'http://tax-calculator.localhost'
			),
			'dataCenterPlugin' => array(
				'title' => 'Data Center Plugin (CakePHP 2)'
			),
			'dataCenterTemplate' => array(
				'title' => 'Data Center Template'
			),
			'GoogleCharts' => array(
				'title' => 'Google Charts Plugin for CakePHP (fork)'
			),
			'cri' => array(
			    'title' => 'Community Readiness Initiative',
                'production' => 'https://cri.cberdata.org',
                'development' => 'https://cri.localhost'
            ),
            'utilities' => array(
                'title' => 'CBER Utilities'
            ),
            'whyarewehere' => array(
                'title' => 'Why Are We Here?'
            )
		);

		$pos = stripos(env('SERVER_NAME'), 'localhost');
		$sn_len = strlen(env('SERVER_NAME'));
		$lh_len = strlen('localhost');
		$is_localhost = ($pos !== false && $pos == ($sn_len - $lh_len));

		$this->set(array(
			'title_for_layout' => 'CBER Website Panopticon',
			'repositories' => $repositories,
			'sites' => $sites,
			'is_localhost' => $is_localhost,
			'servers' => $is_localhost ? array('development', 'production') : array('production')
		));
	}

	public function terms() {
		$this->set('title_for_layout', 'Terms of Service');
	}

	private function __getSiteStatus($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // ignore SSL errors
		curl_setopt($ch, CURLOPT_HEADER, 1);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	public function statuscheck() {
		$result = $this->__getSiteStatus($_GET['url']);
        $this->set('result', array(
			'status' => substr($result, 0, strpos($result, "\n")),
			'debug' => stripos($result, 'debug-kit-toolbar') !== false
		));
        $this->layout = 'json';
    }
}
