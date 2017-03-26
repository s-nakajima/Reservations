<?php
/**
 * BlogEntriesEdit
 */
App::uses('ReservationsAppController', 'Reservations.Controller');

/**
 * BlogEntriesEdit Controller
 *
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link     http://www.netcommons.org NetCommons Project
 * @license  http://www.netcommons.org/license.txt NetCommons License
 * @property NetCommonsWorkflow $NetCommonsWorkflow
 * @property PaginatorComponent $Paginator
 * @property ReservationLocation $ReservationLocation
 * @property BlogCategory $BlogCategory
 */
class ReservationLocationsController extends ReservationsAppController {

/**
 * layout
 *
 * @var array
 */
	public $layout = 'NetCommons.setting';	//PageLayoutHelperのafterRender()の中で利用。
	//
	//$layoutに'NetCommons.setting'があると
	//「Frame設定も含めたコンテンツElement」として
	//ng-controller='FrameSettingsController'属性
	//ng-init=initialize(Frame情報)属性が付与される。
	//
	//'NetCommons.setting'がないと、普通の
	//「コンテンツElement」として扱われる。
	//
	//ちなみに、使用されるLayoutは、Pages.default
	//

/**
 * @var array use models
 */
	public $uses = array(
		'Reservations.ReservationLocation',
		'Reservations.ReservationLocationsRoom',
		'Categories.Category',
		//'Workflow.WorkflowComment',
	);

/**
 * Components
 *
 * @var array
 */
	public $components = array(
		'NetCommons.Permission' => array(
			//アクセスの権限
			'allow' => array(
				'edit' => 'page_editable',
			),
		),
		//'Workflow.Workflow',

		'Categories.Categories',
		//'Blogs.ReservationLocationPermission',
		'NetCommons.NetCommonsTime',
		'Paginator',
		'Rooms.RoomsForm',

	);

/**
 * @var array helpers
 */
	public $helpers = array(
		'NetCommons.BackTo',
		'NetCommons.NetCommonsForm',
		'Workflow.Workflow',
		'NetCommons.NetCommonsTime',
		'NetCommons.TitleIcon',
		//'Blocks.BlockForm',

		'Blocks.BlockTabs' => array(
			//画面上部のタブ設定
			'mainTabs' => array(
				'category_settings' => [
					'label' => ['reservations', 'Location category setting'],
					'url' => array('controller' => 'reservation_settings', 'action' => 'edit')
				],
				'location_settings' => array(
					'label' => ['reservations', 'Location setting'],
					'url' => array('controller' => 'reservation_locations', 'action' => 'index')
				),
				'timeframe_settings' => array(
					'label' => ['reservations', 'TimeFrame setting'],
					'url' => array('controller' => 'reservation_timeframes', 'action' => 'index')
				),
				'import_reservations' => array(
					'label' => ['reservations', 'Import Reservations'],
					'url' => array('controller' => 'reservation_import', 'action' => 'edit')
				),
				'frame_settings' => array(	//表示設定変更
					'url' => array('controller' => 'reservation_frame_settings')
				),
				//'role_permissions' => array(
				//	'url' => array('controller' => 'reservation_block_role_permissions'),
				//),
				'mail_settings' => array(
					'url' => array('controller' => 'reservation_mail_settings'),
				),
			),
			'mainTabsOrder' => [
				'frame_settings', 'location_settings', 'category_settings', 'timeframe_settings',
				'mail_settings', 'import_reservations'

			],
		),
		'Rooms.RoomsForm',
		'Reservations.ReservationLocation',
	);

/**
 * index
 *
 * @return void
 */
	public function index() {
		//$data = $this->ReservationLocation->findById(1);
		// FAQの並び替え参考にしよう
		$query = array();

		//条件
		$conditions = array(
			'ReservationLocation.language_id' => Current::read('Language.id'),
		);
		if (isset($this->params['named']['category_id'])) {
			$conditions['ReservationLocation.category_id'] = $this->params['named']['category_id'];
		}
		$query['conditions'] = $conditions;
		//$query['conditions'] = $this->ReservationLocation->getWorkflowConditi?ons($conditions);

		//表示件数
		//if (isset($this->params['named']['limit'])) {
		//	$query['limit'] = (int)$this->params['named']['limit'];
		//} else {
		//	$query['limit'] = $this->viewVars['faqFrameSetting']['content_per_page'];
		//}

		$query['recursive'] = 0;
		$this->Paginator->settings = $query;
		$reservationLocations = $this->Paginator->paginate('ReservationLocation');
		$this->set('reservationLocations', $reservationLocations);

		// 施設を取得

		// ページング必要
		// 並び順に並べる
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		$this->set('isEdit', false);
		//$this->_prepare();

		//$blogEntry = $this->ReservationLocation->getNew();
		//$this->set('blogEntry', $blogEntry);

		if ($this->request->is('post')) {
			$this->ReservationLocation->create();
			//$this->request->data['ReservationLocation']['blog_key'] =
			//	$this->_blogSetting['BlogSetting']['blog_key'];

			// set status
			//$status = $this->Workflow->parseStatus();
			//$this->request->data['ReservationLocation']['status'] = $status;

			// set block_id
			//$this->request->data['ReservationLocation']['block_id'] = Current::read('Block.id');
			// set language_id
			$this->request->data['ReservationLocation']['language_id'] = Current::read('Language.id');
			$result = $this->ReservationLocation->saveLocation($this->request->data);
			if ($result) {
				$url = NetCommonsUrl::actionUrl(
					array(
						'controller' => 'reservation_locations',
						'action' => 'index',
						//'block_id' => Current::read('Block.id'),
						'frame_id' => Current::read('Frame.id'),
						//'key' => $result['ReservationLocation']['key']
						)
				);
				return $this->redirect($url);
			}

			$this->NetCommons->handleValidationError($this->ReservationLocation->validationErrors);

		} else {
			$newLocation = $this->ReservationLocation->create();
			$newLocation['ReservationLocation'] = [
				'start_time' => '09:00',
				'end_time' => '18:00',
				'time_table' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
				'use_all_rooms' => '1',
			];
			$this->request->data = $newLocation;
		}
		// プライベートルームは除外する
		$roomConditions = [
			//'Room.space_id !=' => Space::PRIVATE_SPACE_ID,
		];
		$this->RoomsForm->setRoomsForCheckbox($roomConditions);
		$this->render('form');
	}

/**
 * edit method
 *
 * @return void
 */
	public function edit() {
		$this->set('isEdit', true);
		//$key = $this->request->params['named']['key'];
		$key = $this->params['key'];

		//  keyのis_latstを元に編集を開始
		$this->ReservationLocation->recursive = 0;
		$options = [
			'conditions' => [
				'ReservationLocation.key' => $key,
				'ReservationLocation.language_id' => Current::read('Language.id')
			]
		];

		$reservationLocation = $this->ReservationLocation->find('first', $options);
		$timeTable = explode('|', $reservationLocation['ReservationLocation']['time_table']);
		$reservationLocation['ReservationLocation']['time_table'] = $timeTable;

		if (empty($reservationLocation)) {
			return $this->throwBadRequest();
		}

		//if ($this->ReservationLocation->canEditWorkflowContent($blogEntry) === false) {
		//	return $this->throwBadRequest();
		//}
		//$this->_prepare();

		if ($this->request->is(array('post', 'put'))) {

			$this->ReservationLocation->create();
			//$this->request->data['ReservationLocation']['blog_key'] =
			//	$this->_blogSetting['BlogSetting']['blog_key'];

			// set status
			//$status = $this->Workflow->parseStatus();
			//$this->request->data['ReservationLocation']['status'] = $status;

			// set block_id
			//$this->request->data['ReservationLocation']['block_id'] = Current::read('Block.id');
			// set language_id
			$this->request->data['ReservationLocation']['language_id'] = Current::read('Language.id');

			$data = $this->request->data;

			//unset($data['ReservationLocation']['id']); // 常に新規保存

			if ($this->ReservationLocation->saveLocation($data)) {
				$url = NetCommonsUrl::actionUrl(
					array(
						'controller' => 'reservation_locations',
						'action' => 'index',
						'frame_id' => Current::read('Frame.id'),
						//'block_id' => Current::read('Block.id'),
						//'key' => $data['ReservationLocation']['key']
					)
				);

				return $this->redirect($url);
			}

			$this->NetCommons->handleValidationError($this->ReservationLocation->validationErrors);

		} else {

			$this->request->data = $reservationLocation;

			//予約を受け付けるルームを取得
			$result = $this->ReservationLocationsRoom->find('list', array(
				'recursive' => -1,
				'fields' => array('id', 'room_id'),
				'conditions' => ['reservation_location_key' => $this->request->data['ReservationLocation']['key']],
			));
			$this->request->data['ReservationLocationsRoom']['room_id'] =
				array_unique(array_values($result));
		}

		$this->set('reservationLocation', $reservationLocation);
		//$this->set('isDeletable', $this->ReservationLocation->canDeleteWorkflowContent($blogEntry));
		$this->set('isDeletable', true);

		//$comments = $this->ReservationLocation->getCommentsByContentKey($blogEntry['ReservationLocation']['key']);
		//$this->set('comments', $comments);

		// プライベートルームは除外する
		$roomConditions = [
			//'Room.space_id !=' => Space::PRIVATE_SPACE_ID,
		];
		$this->RoomsForm->setRoomsForCheckbox($roomConditions);
		$this->render('form');
	}

/**
 * delete method
 *
 * @throws InternalErrorException
 * @return void
 */
	public function delete() {
		$this->request->allowMethod('post', 'delete');

		$key = $this->request->data['ReservationLocation']['key'];
		$blogEntry = $this->ReservationLocation->getWorkflowContents('first', array(
			'recursive' => 0,
			'conditions' => array(
				'ReservationLocation.key' => $key
			)
		));

		// 権限チェック
		if ($this->ReservationLocation->canDeleteWorkflowContent($blogEntry) === false) {
			return $this->throwBadRequest();
		}

		if ($this->ReservationLocation->deleteEntryByKey($key) === false) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		return $this->redirect(
			NetCommonsUrl::actionUrl(
				array(
					'controller' => 'reservation_locations',
					'action' => 'index',
					'frame_id' => Current::read('Frame.id'),
					//'block_id' => Current::read('Block.id')
				)
			)
		);
	}
}
