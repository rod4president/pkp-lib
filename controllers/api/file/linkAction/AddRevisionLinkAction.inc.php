<?php

/**
 * @file controllers/api/file/linkAction/AddRevisionLinkAction.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AddRevisionLinkAction
 * @ingroup controllers_api_file_linkAction
 *
 * @brief An action to upload a revision of file currently under review.
 */

import('lib.pkp.controllers.api.file.linkAction.BaseAddFileLinkAction');

class AddRevisionLinkAction extends BaseAddFileLinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $reviewRound ReviewRound The review round to upload to.
	 * @param $uploaderRoles array The ids of all roles allowed to upload
	 *  in the context of this action.
	 */
	function AddRevisionLinkAction($request, $reviewRound, $uploaderRoles, $uploaderGroupIds = null) {
		// Bring in the submission file constants.
		import('lib.pkp.classes.submission.SubmissionFile');

		// Create the action arguments array.
		$actionArgs = array(
			'fileStage' => SUBMISSION_FILE_REVIEW_REVISION,
			'stageId' => $reviewRound->getStageId(),
			'reviewRoundId' => $reviewRound->getId(),
			'revisionOnly' => '1'
		);

		// Call the parent class constructor.
		parent::BaseAddFileLinkAction(
			$request, $reviewRound->getSubmissionId(), $reviewRound->getStageId(), $uploaderRoles, $uploaderGroupIds, $actionArgs,
			__('submission.review.uploadRevisionToRound', array('round' => $reviewRound->getRound())),
			__('submission.addFile')
		);
	}
}

?>
