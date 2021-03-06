<?php

/**
 * @file classes/notification/managerDelegate/AllRevisionsInNotificationManager.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AllRevisionsInNotificationManager
 * @ingroup managerDelegate
 *
 * @brief All revisions in notification types manager delegate.
 */

import('lib.pkp.classes.notification.managerDelegate.RevisionsNotificationManager');

class AllRevisionsInNotificationManager extends RevisionsNotificationManager {

	/**
	 * Constructor.
	 * @param $notificationType int NOTIFICATION_TYPE_...
	 */
	function AllRevisionsInNotificationManager($notificationType) {
		parent::RevisionsNotificationManager($notificationType);
	}

	/**
	 * @see NotificationManagerDelegate::updateNotification()
	 */
	public function updateNotification($request, $userIds, $assocType, $assocId) {
		$reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO');
		$reviewRound = $reviewRoundDao->getById($assocId);
		$submissionId = $reviewRound->getSubmissionId();

		$pendingRevisionDecision = $this->findValidPendingRevisionsDecision($submissionId, $reviewRound->getStageId());
		$removeNotifications = false;

		if ($pendingRevisionDecision) {
			if ($this->responseExists($pendingRevisionDecision, $submissionId)) {
				// Some user already uploaded a revision.
				$this->_addAllRevisionsIn($request, $reviewRound);
			} else {
				// No revision response, remove notification.
				$removeNotifications = true;
			}
		} else {
			$removeNotifications = true;
		}

		if ($removeNotifications) {
			$this->_removeAllRevisionsIn($request, $reviewRound);
		}
	}


	//
	// Private helper methods.
	//
	/**
	 * Add a task notification to remember editors that they need to make an
	 * editorial decision after author sent a revision.
	 * @param $request Request
	 * @param $reviewRound ReviewRound
	 */
	private function _addAllRevisionsIn($request, $reviewRound) {
		$context = $request->getContext();
		$contextId = $context->getId();

		$this->_removeAllRevisionsIn($request, $reviewRound);

		$stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO');
		$stageAssignments = $stageAssignmentDao->getEditorsAssignedToStage($reviewRound->getSubmissionId(), $reviewRound->getStageId());
		foreach ($stageAssignments as $stageAssignment) {
			$userId = $stageAssignment->getUserId();
			$this->createNotification($request, $userId, NOTIFICATION_TYPE_ALL_REVISIONS_IN, $contextId,
				ASSOC_TYPE_REVIEW_ROUND, $reviewRound->getId(), NOTIFICATION_LEVEL_TASK);
		}
	}

	/**
	 * Deletes all notifications that were created to tell editors that
	 * they have to make an editorial decision because user sent revisions.
	 * @param $request Request
	 * @param $reviewRound ReviewRound
	 */
	private function _removeAllRevisionsIn($request, $reviewRound) {
		$context = $request->getContext();
		$contextId = $context->getId();
		$notificationDao = DAORegistry::getDAO('NotificationDAO'); /* @var $notificationDao NotificationDAO */

		$stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO');
		$stageAssignments = $stageAssignmentDao->getEditorsAssignedToStage($reviewRound->getSubmissionId(), $reviewRound->getStageId());
		foreach ($stageAssignments as $stageAssignment) {
			$userId = $stageAssignment->getUserId();

			// Get any existing notification.
			$notificationFactory = $notificationDao->getByAssoc(
				ASSOC_TYPE_REVIEW_ROUND,
				$reviewRound->getId(), $userId,
				NOTIFICATION_TYPE_ALL_REVISIONS_IN,
				$contextId
			);

			if (!$notificationFactory->wasEmpty()) {
				$notification = $notificationFactory->next();
				$notificationDao->deleteObject($notification);
			}
		}
	}
}

?>
