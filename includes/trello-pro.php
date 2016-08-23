<?php

/**
 * Trello OAuth class
 */
class trello_pro_oauth extends trello_oauth {

	/**
	 * Get Checklists on a card
	 *
	 * @param int $card
	 *
	 * @return API|array|mixed|object
	 */
	public function getChecklists( $card ) {
		$params    = array();
		$all_checklists = $this->get( 'cards/' . $card . '/checklists/', $params, 0 );

		return $all_checklists;
	}

	/**
	 * Get a Checklist
	 *
	 * @param int $id
	 *
	 * @return API|array|mixed|object
	 */
	public function getChecklist( $id ) {
		$params    = array();
		$checklist = $this->get( 'checklists/' . $id, $params, 0 );

		return $checklist;
	}
}