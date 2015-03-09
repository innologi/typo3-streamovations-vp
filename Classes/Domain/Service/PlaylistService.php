<?php
namespace Innologi\StreamovationsVp\Domain\Service;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
/**
 * Playlist Domain Service class
 *
 * Currently not a singleton, because it is used in only 1 place. If it ever does
 * get useful as a singleton, I needs to get rid of __construct() though.
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class PlaylistService {

	/**
	 * @var string
	 */
	protected $extensionName;

	/**
	 * Class constructor
	 *
	 * @param array $settings
	 * @param string $extensionName
	 * @return void
	 */
	public function __construct($extensionName) {
		$this->extensionName = $extensionName;
	}

	/**
	 * Creates a jwplayer setup array from the playlist object
	 *
	 * Note that $playlist ordinarily is a Playlist object, but could also be MagicResponse,
	 * hence ResponseInterface: the interface they both implement
	 *
	 * @param \Innologi\StreamovationsVp\Library\RestRepository\ResponseInterface $playlist
	 * @param array $settings
	 * @return array
	 * @see http://support.jwplayer.com/customer/portal/articles/1413113-configuration-options-reference
	 */
	public function createJwplayerSetup(\Innologi\StreamovationsVp\Library\RestRepository\ResponseInterface $playlist, array $settings) {
		/* @var $playlist \Innologi\StreamovationsVp\Domain\Model\Playlist */
		$ports = $playlist->getPorts();
		$application = $playlist->getApplication();
		$urlParts = array(
			0 => 'rtmp',
			1 => '://' . $playlist->getServer() . ':',
			2 => $ports['rtmp'],
			3 => '/' . $application . '/'
		);

		$playlistData = array(
			'playlist' => array(),
			// used by SVPS, not by jwplayer
			'application' => $application
		);
		if (isset($settings['width'][0])) {
			$playlistData['width'] = $settings['width'];
		}
		if (isset($settings['height'][0])) {
			$playlistData['height'] = $settings['height'];
		}

		$playlistItems = $playlist->getPlaylistItems();
		foreach ($playlistItems as $playlistItem) {
			/* @var $playlistItem \Innologi\StreamovationsVp\Domain\Model\Playlist\PlaylistItem */
			$sourceFile = $this->getSourceFile($urlParts, $ports, $playlistItem->getSource(), $settings);

			$playlistData['playlist'][] = array(
				// @LOW 'image' => ''
				'sources' => array(
					0 => array(
						'file' => $sourceFile
					)
				),
				// used by SVPS, not by jwplayer
				'streamfileId' => $playlistItem->getStreamfileId()
			);
		}

		return $playlistData;
	}

	/**
	 * Returns the source file URL
	 *
	 * Note that $source ordinarily is a Source object, but could also be MagicResponse,
	 * hence ResponseInterface: the interface they both implement
	 *
	 * @param array $urlParts
	 * @param array $ports
	 * @param \Innologi\StreamovationsVp\Library\RestRepository\ResponseInterface $source
	 * @param array $settings
	 * @return string
	 */
	protected function getSourceFile(array $urlParts, array $ports, \Innologi\StreamovationsVp\Library\RestRepository\ResponseInterface $source, array $settings) {
		/* @var $source \Innologi\StreamovationsVp\Domain\Model\Playlist\Source */

		$useSmil = (bool) $settings['smilSupport'];
		$smil = $source->getSmil();

		// best case scenario: smil is available, provides quality selection
		if ($useSmil && $smil !== NULL) {
			$urlParts[0] = 'http';
			$urlParts[2] = $ports['http'];
			$sourceFile = $this->getSourceFromSmil($smil, $settings['smilWrap']);

			// worst case scenario: no smil and I'm not bothering with creating quality selection
		} else {
			$sourceFile = $this->getSourceFromQualities($source);

			// when livestreaming, $source is an array/object containing a stream for each available language
			if ($application === 'rtplive' && !is_string($sourceFile)) {
				$sourceFile = $this->getSourceFromLiveLanguage($sourceFile, $settings['liveLanguage']);
			}
		}

		return join('', $urlParts) . $sourceFile;
	}

	/**
	 * Returns the source filename from the given smil file.
	 *
	 * @param string $smil
	 * @param string $wrap
	 * @return string
	 */
	protected function getSourceFromSmil($smil, $wrap) {
		return isset($wrap[0])
			? str_replace('|', $smil, $wrap)
			: $smil;
	}

	/**
	 * Returns the source filename from $source[qualities]
	 *
	 * Note that $source ordinarily is a Source object, but could also be MagicResponse,
	 * hence ResponseInterface: the interface they both implement
	 *
	 * @param \Innologi\StreamovationsVp\Library\RestRepository\ResponseInterface $source
	 * @return mixed
	 */
	protected function getSourceFromQualities(\Innologi\StreamovationsVp\Library\RestRepository\ResponseInterface $source) {
		/* @var $source \Innologi\StreamovationsVp\Domain\Model\Playlist\Source */
		$qualities = $source->getQualities();
		// there is no validator support right now, but we assume $qualities !== NULL
		$defaultQuality = $source->getDefaultQuality();
		return $defaultQuality !== NULL && isset($qualities[$defaultQuality])
			? $qualities[$defaultQuality]
			: current($qualities);
	}

	/**
	 * Returns the source filename from an array-structure containing live-languages.
	 *
	 * @param mixed $source Could be an array or an object implementing array-interfaces
	 * @param string $liveLanguage
	 * @return string
	 */
	protected function getSourceFromLiveLanguage($source, $liveLanguage) {
		$languageFound = FALSE;

		// @LOW are we sure the response does not produce a 'language' root-property during livestream?
		// livestream does not produce available languages, hence we use a configured csv list
		$languages = GeneralUtility::trimExplode(',', $liveLanguage);
		foreach ($languages as $lang) {
			if (isset($source[$lang])) {
				$source = $source[$lang];
				$languageFound = TRUE;
				break;
			}
		}
		// if configured language is not found, log the issue and just get the first element
		if (!$languageFound) {
			GeneralUtility::devLog(
				sprintf(
					LocalizationUtility::translate('language_not_found', $this->extensionName),
					// tried languages
					$liveLanguage,
					// used language (first one)
					key($source)
				),
				$this->extensionName,
				2
			);
			// use first available source
			$source = current($source);
		}

		return $source;
	}

}
