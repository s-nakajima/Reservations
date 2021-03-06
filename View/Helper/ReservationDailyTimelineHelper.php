<?php
/**
 * Reservation DailyTimeline Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
/**
 * Reservation daily Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\View\Helper
 */
class ReservationDailyTimelineHelper extends ReservationMonthlyHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'NetCommonsForm',
		'NetCommonsHtml',
		'Form',
		'Reservations.ReservationButton',
		'Reservations.ReservationCommon',
		'Reservations.ReservationUrl',
		'Reservations.ReservationWorkflow',
		'Users.DisplayUser',
	);

/**
 * Timeline plan data
 *
 * @var array
 */
	protected $_timelineData = array();

/**
 * TimelineDataの取得
 *
 * @return array
 */
	public function getTimelineData() {
		return $this->_timelineData;
	}

/**
 * getPlanSummariesHtml
 *
 * 予定概要群html取得
 *
 * @param array &$vars 施設予約情報
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param string $fromTime この日の１日のスタート時刻
 * @param string $toTime この日の１日のエンド時刻
 * @param array $plans この日の予定群
 * @return string HTML
 */
	public function getPlanSummariesHtml(&$vars, $year, $month, $day, $fromTime, $toTime, $plans) {
		$html = '';
		$cnt = 0;
		foreach ($plans as $plan) {
			if ($vars['currentLocationKey'] !== $plan['ReservationEvent']['location_key']) {
				continue;
			}
			//仕様
			//予定が１件以上あるとき）
			//$html .= "<div class='row'><div class='col-xs-12'>"; //１プランの開始

			$html .= $this->getPlanTitleHtml($vars, $year, $month, $day, $fromTime, $toTime, $plan, $cnt);

			// 1プランの終了
			//$html .= "</div></div>";
			//$cnt++;
		}
		return $html;
	}

/**
 * getPlanTitleHtml
 *
 * 予定（タイトル）html取得
 *
 * @param array &$vars 施設予約情報
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param string $fromTime この日の１日のスタート時刻
 * @param string $toTime この日の１日のエンド時刻
 * @param array $plan 予定
 * @param int &$cnt タイムライン表示対象数
 * @return string HTML
 */
	public function getPlanTitleHtml(&$vars, $year, $month, $day, $fromTime, $toTime, $plan, &$cnt) {
		$url = '';
		$html = '';
		//$vars['reservationTimelinePlan'] = array();
		$id = 'plan' . (string)$cnt;
		//print_r($id);

		//if ($fromTime !== $plan['ReservationEvent']['fromTime'] ||
		//		$toTime !==	$plan['ReservationEvent']['toTime']) {
			$reservationPlanMark = $this->ReservationCommon->getPlanMarkClassName($vars, $plan);
			$url = $this->ReservationUrl->makePlanShowUrl($year, $month, $day, $plan);

			$htmlClass = 'reservation-daily-timeline-slit-deco ' . $reservationPlanMark;
			$html .= '<div class="' . $htmlClass . '"' .
							' id="' . $id . '"' .
							' data-event-id="' . $plan['ReservationEvent']['id'] . '">';

			$html .= '<div class="reservation-common-margin-padding">';

			if ($this->ReservationWorkflow->canRead($plan)) {
				$htmlClass = 'reservation-plan-clickable text-left reservation-plan-show';
				$html .= '<div><p class="' . $htmlClass . '" data-url="' . $url . '">';
			} else {
				//$htmlClass = 'reservation-plan-clickable text-left reservation-plan-show';
				$htmlClass = '';
				$html .= '<div><p class="' . $htmlClass . '" >';
			}
			// 時間表示なくてもタイムラインならわかるので時間非表示
			//$html .= '<small>';
			//$html .= h($plan['ReservationEvent']['fromTime']) . '-' . h($plan['ReservationEvent']['toTime']);
			//$html .= '</small>';

			// ワークフロー（一時保存/承認待ち、など）のマーク
			$html .= $this->ReservationCommon->makeWorkFlowLabel($plan['ReservationEvent']['status']);

			if ($this->ReservationWorkflow->canRead($plan)) {
				$html .= '<small>' . h($plan['ReservationEvent']['title']) . '</small>';
			} else {
				$html .= $this->_getNotRedablePlanHtml($plan);
			}

			$html .= '</div></div>';
			$html .= '</p></div>';

			$this->_timelineData[$cnt]['eventId'] = $plan['ReservationEvent']['id'];
			$this->_timelineData[$cnt]['locationKey'] = $plan['ReservationEvent']['location_key'];
			$this->_timelineData[$cnt]['fromTime'] = $plan['ReservationEvent']['fromTime'];
			$this->_timelineData[$cnt]['toTime'] = $plan['ReservationEvent']['toTime'];
			$cnt++;
		//}

		return $html;
	}
/**
 * makeDailyBodyHtml
 *
 * (日表示)本体html生成
 *
 * @param array $vars コントローラーからの情報
 * @return string HTML
 */
	public function makeDailyBodyHtml($vars) {
		$html = '';
		$nctm = new NetCommonsTime();

		$html .= $this->_makePlanSummariesHtml($vars, $nctm, $vars['year'], $vars['month'], $vars['day']);
		return $html;
	}

/**
 * getTimelineTdsHtml
 *
 * タイムラインテーブルhtml取得
 *
 * @param array $vars 施設予約情報
 * @return string HTML
 */
	/* 座標がずれるので未使用
	public function getTimelineTdsHtml($vars) {

		$hour = "";
		$html = '';
		for ($i=2; $i < 22 ; $i++) { //2時から22時まで
			$html .= '<tr>';
			$html .= '<td class="reservation-vertical-timeline-periodtime reservation-tbl-td-pos">';
			$html .= '<div class="row">';
			$html .= '<div class="col-xs-12">';

			$hour = str_pad($i, 2, 0, STR_PAD_LEFT);

			$html .= "<p class='text-right'><span>" . $hour . ":00</span></p>";

			$html .= "</div>";
			$html .= "<div class='clearfix'></div>";
			$html .= "<div class='col-xs-12'>";
			$html .= "<p class='reservation-plan-clickable text-right'><small>";
			$html .= "<span class='glyphicon glyphicon-plus'></span></small></p>";
			$html .= "</div>";
			$html .= "<div class='clearfix'></div>";
			$html .= "</div>";
			$html .= "</td>";
			$html .= "<td class='reservation-daily-timeline-col-slit reservation-tbl-td-pos'>";
			$html .= "</td>";
			$html .= "</tr>";

		}
		return $html;
	}
 */

/**
 * makeDailyTimlineHeaderHtml
 *
 * (日表示)ヘッダ部分html生成
 *
 * @param array &$vars コントローラーからの情報
 * @return string HTML
 */
	public function makeDailyTimlineHeaderHtml(&$vars) {
		$html = '';
		$html .= '<tr>';

		//時間タイムライン表示
		for ($hour = 0; $hour < 24; $hour++) {
			$timeIndex = sprintf('%02d00', $hour);
			$timeString = sprintf('%02d:00', $hour);

			$tdClass = 'reservation-vertical-timeline-periodtime reservation-col-head' .
						' reservation-daily-timeline-' . $timeIndex;
			$html .= '<td class="text-left ' . $tdClass . '">';

			$html .= '<div class="pull-left">' .
						'<span>' . $timeString . '</span>' .
					'</div>';
			$html .= '<div class="reservation-plan-clickable pull-right">' .
						'<small>' .
							$this->ReservationButton->makeGlyphiconPlusWithTimeUrl(
								$vars['year'], $vars['month'], $vars['day'], $hour, $vars
							) .
						'</small>' .
					'</div>';
			$html .= '</td>';
		}
		$html .= '</tr>';
		return $html;
	}

}
