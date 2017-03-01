<?php

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * gwolle_gb_pagination_frontend
 * Pagination of the entries for the guestbook frontend
 *
 * @args: $pageNum, int with the number of the requested page.
 *        $pages_total, int with the total number of pages.
 *  @return: $pagination, string with the html of the pagination.
 */
function gwolle_gb_pagination_frontend( $pageNum, $pages_total ) {

	$permalink = get_permalink(get_the_ID());
	$highDotsMade = false;
	$pages_done = array();

	$pagination = '<div class="page-navigation">';

	if ($pageNum > 1) {
		$pagination .= '<a href="' . add_query_arg( 'pageNum', round($pageNum - 1), $permalink ) . '" title="' . __('Previous page', 'gwolle-gb') . '" rel="prev">&laquo;</a>';
	}

	if ($pageNum < 5) {
		$showRange = 5;
		if ($pages_total < 6) {
			$showRange = $pages_total;
			$highDotsMade = true; // no need for highdots.
		}
		for ($i = 1; $i < ($showRange + 1); $i++) {
			if ($i == $pageNum) {
				if ( in_array( $i, $pages_done ) ) { continue; }
				$pagination .= '<span>' . $i . '</span>';
				$pages_done[] = $i;
			} else {
				if ( in_array( $i, $pages_done ) ) { continue; }
				$pagination .= '<a href="' . add_query_arg( 'pageNum', $i, $permalink ) . '" title="' . __('Page', 'gwolle-gb') . ' ' . $i . '">' . $i . '</a>';
				$pages_done[] = $i;
				if ( $i == $pages_total ) { break; }
			}
		}

		if ( ($pageNum + 4 < $pages_total) && ( ! $highDotsMade) ) {
			$pagination .= '<span class="page-numbers dots">...</span>';
			$highDotsMade = true;
		}
	} elseif ($pageNum > 4) {
		$pagination .= '<a href="' . add_query_arg( 'pageNum', 1, $permalink ) . '" title="' . __('Page', 'gwolle-gb') . ' 1">1</a>';
		if ($pages_total > 4) {
			$pagination .= '<span class="page-numbers dots">...</span>';
		}
		if ($pageNum + 2 < $pages_total) {
			$minRange = $pageNum - 2;
			$showRange = $pageNum + 2;
		} else {
			$minRange = $pageNum - 3;
			$showRange = $pages_total - 1;
		}
		for ($i = $minRange; $i <= $showRange; $i++) {
			if ($i == $pageNum) {
				$pagination .= '<span>' . $i . '</span>';
			} else {
				$pagination .= '<a href="' . add_query_arg( 'pageNum', $i, $permalink ) . '" title="' . __('Page', 'gwolle-gb') . ' ' . $i . '">' . $i . '</a>';
			}
		}
		if ($pageNum == $pages_total) {
			$pagination .= '<span class="page-numbers current">' . $pageNum . '</span>';
		}
	}

	if ($pageNum < $pages_total) {
		if ( ($pageNum + 3 < $pages_total) && ( ! $highDotsMade) ) {
			$pagination .= '<span class="page-numbers dots">...</span>';
			$highDotsMade = true;
		}
		if ( ! in_array( $pages_total, $pages_done ) ) {
			$pagination .= '<a href="' . add_query_arg( 'pageNum', $pages_total, $permalink ) . '" title="' . __('Page', 'gwolle-gb') . ' ' . $pages_total . '">' . $pages_total . '</a>';
		}
		$pagination .= '<a href="' . add_query_arg( 'pageNum', round($pageNum + 1), $permalink ) . '" title="' . __('Next page', 'gwolle-gb') . '" rel="next">&raquo;</a>';
	}

	// 'All' link
	if ( $pages_total >= 2 && get_option( 'gwolle_gb-paginate_all', 'false' ) === 'true' ) {
		if ( isset($_GET['show_all']) && $_GET['show_all'] == 'true' ) {
			$pagination .= '<span>' . __('All', 'gwolle-gb') . '</span>';
		} else {
			$pagination .= '<a href="' . add_query_arg( 'show_all', 'true', $permalink ) . '" title="' . __('All entries', 'gwolle-gb') . '">' . __('All', 'gwolle-gb') . '</a>';
		}
	}

	$pagination .= '</div>
		';

	if ($pages_total > 1) {
		return $pagination;
	}

}
