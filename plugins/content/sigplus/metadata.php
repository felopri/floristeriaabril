<?php
/**
* @file
* @brief    sigplus Image Gallery Plus metadata extraction
* @author   Levente Hunyadi
* @version  1.4.2
* @remarks  Copyright (C) 2009-2011 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

/*
* sigplus Image Gallery Plus plug-in for Joomla
* Copyright 2009-2010 Levente Hunyadi
*
* sigplus is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* sigplus is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class SIGPlusIPTCServices {
	private static $enveloperecord = array(
		0=>'Envelope Record Version',
		5=>'Destination',
		20=>'File Format',
		22=>'File Version',
		30=>'Service Identifier',
		40=>'Envelope Number',
		50=>'Product ID',
		60=>'Envelope Priority',
		70=>'Date Sent',
		80=>'Time Sent',
		90=>'Coded Character Set',
		100=>'Unique Object Name',
		120=>'ARM Identifier',
		122=>'ARM Version');

	private static $applicationrecord = array(
		0=>'Application Record Version',
		3=>'Object Type Reference',
		4=>'Object Attribute Reference',
		5=>'Object Name',
		7=>'Edit Status',
		8=>'Editorial Update',
		10=>'Urgency',
		12=>'Subject Reference',
		15=>'Category',
		20=>'Supplemental Categories',
		22=>'Fixture Identifier',
		25=>'Keywords',
		26=>'Content Location Code',
		27=>'Content Location Name',
		30=>'Release Date',
		35=>'Release Time',
		37=>'Expiration Date',
		38=>'Expiration Time',
		40=>'Special Instructions',
		42=>'Action Advised',
		45=>'Reference Service',
		47=>'Reference Date',
		50=>'Reference Number',
		55=>'Date Created',
		60=>'Time Created',
		62=>'Digital Creation Date',
		63=>'Digital Creation Time',
		65=>'Originating Program',
		70=>'Program Version',
		75=>'Object Cycle',
		80=>'By-line',
		85=>'By-line Title',
		90=>'City',
		92=>'Sub-location',
		95=>'Province-State',
		100=>'Country-Primary Location Code',
		101=>'Country-Primary Location Name',
		103=>'Original Transmission Reference',
		105=>'Headline',
		110=>'Credit',
		115=>'Source',
		116=>'Copyright Notice',
		118=>'Contact',
		120=>'Caption-Abstract',
		121=>'Local Caption',
		122=>'Writer-Editor',
		125=>'Rasterized Caption',
		130=>'Image Type',
		131=>'Image Orientation',
		135=>'Language Identifier',
		150=>'Audio Type',
		151=>'Audio Sampling Rate',
		152=>'Audio Sampling Resolution',
		153=>'Audio Duration',
		154=>'Audio Outcue',
		184=>'JobID',
		185=>'Master Document ID',
		186=>'Short Document ID',
		187=>'Unique Document ID',
		188=>'Owner ID',
		200=>'Object Preview File Format',
		201=>'Object Preview File Version',
		202=>'Object Preview Data',
		221=>'Prefs',
		225=>'Classify State',
		228=>'Similarity Index',
		230=>'Document Notes',
		231=>'Document History',
		232=>'Exif Camera Info');

	private static $fileformats = array(
		0=>'No Object Data',
		1=>'IPTC-NAA Digital Newsphoto Parameter Record',
		2=>'IPTC7901 Recommended Message Format',
		3=>'Tagged Image File Format (Adobe/Aldus Image data)',
		4=>'Illustrator (Adobe Graphics data)',
		5=>'AppleSingle (Apple Computer Inc)',
		6=>'NAA 89-3 (ANPA 1312)',
		7=>'MacBinary II',
		8=>'IPTC Unstructured Character Oriented File Format (UCOFF)',
		9=>'United Press International ANPA 1312 variant',
		10=>'United Press International Down-Load Message',
		11=>'JPEG File Interchange (JFIF)',
		12=>'Photo-CD Image-Pac (Eastman Kodak)',
		13=>'Bit Mapped Graphics File [.BMP] (Microsoft)',
		14=>'Digital Audio File [.WAV] (Microsoft & Creative Labs)',
		15=>'Audio plus Moving Video [.AVI] (Microsoft)',
		16=>'PC DOS/Windows Executable Files [.COM][.EXE]',
		17=>'Compressed Binary File [.ZIP] (PKWare Inc)',
		18=>'Audio Interchange File Format AIFF (Apple Computer Inc)',
		19=>'RIFF Wave (Microsoft Corporation)',
		20=>'Freehand (Macromedia/Aldus)',
		21=>'Hypertext Markup Language [.HTML] (The Internet Society)',
		22=>'MPEG 2 Audio Layer 2 (Musicom), ISO/IEC',
		23=>'MPEG 2 Audio Layer 3, ISO/IEC',
		24=>'Portable Document File [.PDF] Adobe',
		25=>'News Industry Text Format (NITF)',
		26=>'Tape Archive [.TAR]',
		27=>'Tidningarnas Telegrambyra NITF version (TTNITF DTD)',
		28=>'Ritzaus Bureau NITF version (RBNITF DTD)',
		29=>'Corel Draw [.CDR]');

	/**
	* Canonicalizes the value of a metadata entry to ensure proper display.
	*/
	private static function canonicalizeMetadataValue($key, $value) {
		if (is_array($value)) {
			switch (count($value)) {
				case 0: return false;  // nothing to process
				case 1: $value = reset($value); break;  // extract the only element from single-entry array
			}
		}
		switch ($key) {
			case 'Coded Character Set':
				switch ($value) {  // see International Register of Coded Character Sets to be used with escape sequences
					case "\x1b%G": $value = 'utf-8'; break;
					case "\x1b.A": $value = 'iso-8859-1'; break;
					case "\x1b.B": $value = 'iso-8859-2'; break;
					case "\x1b.C": $value = 'iso-8859-3'; break;
					case "\x1b.D": $value = 'iso-8859-4'; break;
					case "\x1b.@": $value = 'iso-8859-5'; break;
					case "\x1b.F": $value = 'iso-8859-7'; break;
					case "\x1b.G": $value = 'iso-8859-6'; break;
					case "\x1b.H": $value = 'iso-8859-8'; break;
					default: $value = 'ascii';  // assume ASCII for unrecognized escape sequences
				}
				break;
			case 'Envelope Record Version':
			case 'File Version':
			case 'ARM Identifier':
			case 'ARM Version':
			case 'Application Record Version':
			case 'ObjectPreviewFileVersion':
				$value = (int) $value;
				break;
			case 'File Format':
			case 'ObjectPreviewFileFormat':
				$value = (int) $value;
				if (isset(self::$fileformats[$value])) {
					$value = self::$fileformats[$value];
				}
				break;
			case 'Image Orientation':
				switch ($value) {
					case 'L': $value = 'landscape'; break;
					case 'P': $value = 'portrait'; break;
					case 'S': $value = 'square'; break;
				}
				break;
		}
		return $value;
	}

	/**
	* Map keys from PHP function @c iptcparse.
	*/
	private static function mapMetadataKeys($array) {
		$metadata = array();
		if ($array === false) {
			return $metadata;
		}
		foreach ($array as $key => $value) {
			@list($recordid, $tagid) = explode('#', $key, 2);  // key = record number + # + tag ID
			$recordid = (int) $recordid;
			$tagid = (int) $tagid;
			switch ($recordid) {
				case 1:  // envelope record
					if (isset(self::$enveloperecord[$tagid])) {
						$tagname = self::$enveloperecord[$tagid];
						$metadata[$tagname] = self::canonicalizeMetadataValue($tagname, $value);
					}
					break;
				case 2:  // application record
					if (isset(self::$applicationrecord[$tagid])) {
						$tagname = self::$applicationrecord[$tagid];
						$metadata[$tagname] = self::canonicalizeMetadataValue($tagname, $value);
					}
					break;
			}
		}

		if (!isset($metadata['Coded Character Set'])) {  // assume cp1252 (Latin1) if no character set is specified
			$charset = 'cp1252';
			
			/*
			$encoding_list = array(
				'UTF-8',
				'CP1252',  // Latin1
				'CP1250',  // Latin2
				'CP1251',  // Cyrillic (Russian)
				'CP1253',  // Greek
				'CP1254',  // Turkish
				'CP1255',  // Hebrew
				'CP1256',  // Arabic
				'CP1257',  // Baltic
				'CP1258',  // Vietnam
				'CP874',   // Thai
				'MACROMAN',
				'MACCENTRALEUROPE',
				'MACICELAND',
				'MACCROATIAN',
				'MACROMANIA',
				'MACCYRILLIC',
				'MACUKRAINE',
				'MACGREEK',
				'MACTURKISH',
				'MACHEBREW',
				'MACARABIC',
				'MACTHAI'
			);
			$charset = mb_detect_encoding($str, $encoding_list);
			*/
		} else {
			$charset = $metadata['Coded Character Set'];
		}

		if ($charset != 'utf-8' && extension_loaded('iconv')) {
			foreach ($metadata as $key => &$value) {
				if (is_array($value)) {
					foreach ($value as &$elementvalue) {
						$elementvalue = iconv($charset, 'utf-8', $elementvalue);
					}
				} else {
					$value = iconv($charset, 'utf-8', $value);
				}
			}
		}

		unset($metadata['Envelope Record Version']);
		unset($metadata['Coded Character Set']);
		unset($metadata['Application Record Version']);
		return $metadata;
	}

	private static function getIptcData($imagefile) {
		$info = array();
		$size = getimagesize($imagefile, $info);
		if ($size !== false && isset($info["APP13"])) {
			return self::mapMetadataKeys(iptcparse($info["APP13"]));
		} else {
			return false;
		}
	}

	private static function getExifData($imagefile) {
		if (!function_exists('exif_read_data')) {
			return false;
		}

		if (($exifdata = @exif_read_data($imagefile, 'EXIF')) === false) {
			return false;
		} else {
			// ISOSpeed
			if (isset($exifdata['ISOSpeedRatings'])) {
				$iso = $exifdata['ISOSpeedRatings'];
			} elseif (isset($exifdata['MakerNote']) && isset($exifdata['ModeArray'])) {
				// add ISO for PowerShot cameras
				switch (@$exifdata['ModeArray'][16]) {
					case 15: $iso = 'auto'; break;
					case 16: $iso =   '50'; break;
					case 17: $iso =  '100'; break;
					case 18: $iso =  '200'; break;
					case 19: $iso =  '400'; break;
				}
			}
			if (isset($iso)) {
				$exifdata['ISOSpeed'] = $iso;
			}
			
			// ComponentsConfiguration
			if (isset($exifdata['ComponentsConfiguration'])) {
				$value = $exifdata['ComponentsConfiguration'];
				$components = array();
				for ($k = 0; $k < strlen($value); $k++) {
					switch (ord($value{$k})) {
						case 1: $components[] = 'Y'; break;
						case 2: $components[] = 'Cb'; break;
						case 3: $components[] = 'Cr'; break;
						case 4: $components[] = 'R'; break;
						case 5: $components[] = 'G'; break;
						case 6: $components[] = 'B'; break;
					}
				}
				$exifdata['ComponentsConfiguration'] = implode(', ', $components);
			}
			
			// FileSource
			if (isset($exifdata['FileSource']) && ord($exifdata['FileSource']) == 3) {
				$exifdata['FileSource'] = 'Digital Still Camera';
			}
			
			// SceneType
			if (isset($exifdata['SceneType']) && ord($exifdata['SceneType']) == 1) {
				$exifdata['SceneType'] = 'Directly photographed image';
			}
			
			// remove sections of little interest
			unset($exifdata['SectionsFound']);
			unset($exifdata['Exif_IFD_Pointer']);
			unset($exifdata['COMPUTED']);
			unset($exifdata['THUMBNAIL']);
			unset($exifdata['ImageInfo']);
			unset($exifdata['MakerNote']);  // proprietary data by camera manufacturers, no standardized format
			unset($exifdata['ModeArray']);
			foreach ($exifdata as $key => $value) {
				if (strpos($key, 'UndefinedTag:') === 0) {  // tags unrecognized by PHP
					unset($exifdata[$key]);
					continue;
				}
			}
			return $exifdata;
		}
	}

	/**
	* Returns IPTC metadata for an image
	*/
	public static function getImageMetadata($imagefile) {
		$iptcdata = self::getIptcData($imagefile);
		if (false) {  // whether to include EXIF information in metadata, change "false" to "true" to extract EXIF data
			$exifdata = self::getExifData($imagefile);
		} else {
			$exifdata = false;
		}
		if (is_array($iptcdata) && is_array($exifdata)) {
			return array_merge($iptcdata, $exifdata);
		} elseif (is_array($iptcdata)) {
			return $iptcdata;
		} elseif (is_array($exifdata)) {
			return $exifdata;
		} else {
			return false;
		}
	}
}