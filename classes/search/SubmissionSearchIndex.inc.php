<?php

/**
 * @file classes/search/SubmissionSearchIndex.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionSearchIndex
 * @ingroup search
 *
 * @brief Class to maintain a submission search index.
 */

import('lib.pkp.classes.search.SearchFileParser');
import('lib.pkp.classes.search.SearchHTMLParser');
import('lib.pkp.classes.search.SearchHelperParser');

define('SEARCH_STOPWORDS_FILE', 'lib/pkp/registry/stopwords.txt');

// Words are truncated to at most this length
define('SEARCH_KEYWORD_MAX_LENGTH', 40);

class SubmissionSearchIndex {
	/**
	 * Split a string into a clean array of keywords
	 * @param $text string
	 * @param $allowWildcards boolean
	 * @return array of keywords
	 */
	static function filterKeywords($text, $allowWildcards = false) {
		$minLength = Config::getVar('search', 'min_word_length');
		$stopwords = self::_loadStopwords();

		// Join multiple lines into a single string
		if (is_array($text)) $text = join("\n", $text);

		$cleanText = Core::cleanVar($text);

		// Remove punctuation
		$cleanText = String::regexp_replace('/[!"\#\$%\'\(\)\.\?@\[\]\^`\{\}~]/', '', $cleanText);
		$cleanText = String::regexp_replace('/[\+,:;&\/<=>\|\\\]/', ' ', $cleanText);
		$cleanText = String::regexp_replace('/[\*]/', $allowWildcards ? '%' : ' ', $cleanText);
		$cleanText = String::strtolower($cleanText);

		// Split into words
		$words = String::regexp_split('/\s+/', $cleanText);

		// FIXME Do not perform further filtering for some fields, e.g., author names?

		// Remove stopwords
		$keywords = array();
		foreach ($words as $k) {
			if (!isset($stopwords[$k]) && String::strlen($k) >= $minLength && !is_numeric($k)) {
				$keywords[] = String::substr($k, 0, SEARCH_KEYWORD_MAX_LENGTH);
			}
		}
		return $keywords;
	}

	/**
	 * Return list of stopwords.
	 * FIXME: Should this be locale-specific?
	 * @return array with stopwords as keys
	 */
	static function _loadStopwords() {
		static $searchStopwords;

		if (!isset($searchStopwords)) {
			// Load stopwords only once per request
			$searchStopwords = array_count_values(
				array_filter(
					file(SEARCH_STOPWORDS_FILE),
					create_function('&$a', 'return ($a = trim($a)) && !empty($a) && $a[0] != \'#\';')
				)
			);
			$searchStopwords[''] = 1;
		}

		return $searchStopwords;
	}
}

?>
