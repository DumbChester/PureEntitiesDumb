<?php
declare(strict_types=1);

/**
 * PureEntitiesX: Mob AI Plugin for PMMP
 * Copyright (C)  2018 RevivalPMMP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace revivalpmmp\pureentities\data;

use LogLevel;
use revivalpmmp\pureentities\PureEntities;

class Color{

	const COLOR_DYE_INK_SAC = 0;//dye colors
	const COLOR_DYE_RED = 1;
	const COLOR_DYE_GREEN = 2;
	const COLOR_DYE_COCOA_BEAN = 3;
	const COLOR_DYE_LAPIS_LAZULI = 4;
	const COLOR_DYE_PURPLE = 5;
	const COLOR_DYE_CYAN = 6;
	const COLOR_DYE_LIGHT_GRAY = 7;
	const COLOR_DYE_GRAY = 8;
	const COLOR_DYE_PINK = 9;
	const COLOR_DYE_LIME = 10;
	const COLOR_DYE_YELLOW = 11;
	const COLOR_DYE_LIGHT_BLUE = 12;
	const COLOR_DYE_MAGENTA = 13;
	const COLOR_DYE_ORANGE = 14;
	const COLOR_DYE_BONE_MEAL = 15;
	const COLOR_DYE_BLACK = 16;
	const COLOR_DYE_BROWN = 17;
	const COLOR_DYE_BLUE = 18;
	const COLOR_DYE_WHITE = 19;


	const WHITE = 0;
	const ORANGE = 1;
	const MAGENTA = 2;
	const LIGHT_BLUE = 3;
	const YELLOW = 4;
	const LIME = 5;
	const PINK = 6;
	const GRAY = 7;
	const LIGHT_GRAY = 8;
	const CYAN = 9;
	const PURPLE = 10;
	const BLUE = 11;
	const BROWN = 12;
	const GREEN = 13;
	const RED = 14;
	const BLACK = 15;

	/** @var \SplFixedArray */
	public static $dyeColors = null;
	private static $convertedColors = null;
	private $red = 0;
	private $green = 0;
	private $blue = 0;

	public function __construct($r, $g, $b){
		$this->red = $r;
		$this->green = $g;
		$this->blue = $b;
	}

	public static function init(){
		if(self::$dyeColors === null){
			self::$dyeColors = new \SplFixedArray(20); //What's the point of making a 256-long array for 20 objects?
			self::$dyeColors[self::COLOR_DYE_INK_SAC] = self::getRGB(30, 27, 27);
			self::$dyeColors[self::COLOR_DYE_RED] = self::getRGB(179, 49, 44);
			self::$dyeColors[self::COLOR_DYE_GREEN] = self::getRGB(61, 81, 26);
			self::$dyeColors[self::COLOR_DYE_COCOA_BEAN] = self::getRGB(81, 48, 26);
			self::$dyeColors[self::COLOR_DYE_LAPIS_LAZULI] = self::getRGB(37, 49, 146);
			self::$dyeColors[self::COLOR_DYE_PURPLE] = self::getRGB(123, 47, 190);
			self::$dyeColors[self::COLOR_DYE_CYAN] = self::getRGB(40, 118, 151);
			self::$dyeColors[self::COLOR_DYE_LIGHT_GRAY] = self::getRGB(153, 153, 153);
			self::$dyeColors[self::COLOR_DYE_GRAY] = self::getRGB(67, 67, 67);
			self::$dyeColors[self::COLOR_DYE_PINK] = self::getRGB(216, 129, 152);
			self::$dyeColors[self::COLOR_DYE_LIME] = self::getRGB(65, 205, 52);
			self::$dyeColors[self::COLOR_DYE_YELLOW] = self::getRGB(222, 207, 42);
			self::$dyeColors[self::COLOR_DYE_LIGHT_BLUE] = self::getRGB(102, 137, 211);
			self::$dyeColors[self::COLOR_DYE_MAGENTA] = self::getRGB(195, 84, 205);
			self::$dyeColors[self::COLOR_DYE_ORANGE] = self::getRGB(235, 136, 68);
			self::$dyeColors[self::COLOR_DYE_BONE_MEAL] = self::getRGB(240, 240, 240);
			self::$dyeColors[self::COLOR_DYE_BLACK] = self::getRGB(30, 27, 27);
			self::$dyeColors[self::COLOR_DYE_BROWN] = self::getRGB(81, 48, 26);
			self::$dyeColors[self::COLOR_DYE_BLUE] = self::getRGB(37, 49, 146);
			self::$dyeColors[self::COLOR_DYE_WHITE] = self::getRGB(240, 240, 240);
		}

		if(self::$convertedColors === null){
			self::$convertedColors = new \SplFixedArray(20);
			self::$convertedColors[self::COLOR_DYE_INK_SAC] = self::BLACK;
			self::$convertedColors[self::COLOR_DYE_RED] = self::RED;
			self::$convertedColors[self::COLOR_DYE_GREEN] = self::GREEN;
			self::$convertedColors[self::COLOR_DYE_COCOA_BEAN] = self::BROWN;
			self::$convertedColors[self::COLOR_DYE_LAPIS_LAZULI] = self::BLUE;
			self::$convertedColors[self::COLOR_DYE_PURPLE] = self::PURPLE;
			self::$convertedColors[self::COLOR_DYE_CYAN] = self::CYAN;
			self::$convertedColors[self::COLOR_DYE_LIGHT_GRAY] = self::LIGHT_GRAY;
			self::$convertedColors[self::COLOR_DYE_GRAY] = self::GRAY;
			self::$convertedColors[self::COLOR_DYE_PINK] = self::PINK;
			self::$convertedColors[self::COLOR_DYE_LIME] = self::LIME;
			self::$convertedColors[self::COLOR_DYE_YELLOW] = self::YELLOW;
			self::$convertedColors[self::COLOR_DYE_LIGHT_BLUE] = self::LIGHT_BLUE;
			self::$convertedColors[self::COLOR_DYE_MAGENTA] = self::MAGENTA;
			self::$convertedColors[self::COLOR_DYE_ORANGE] = self::ORANGE;
			self::$convertedColors[self::COLOR_DYE_BONE_MEAL] = self::WHITE;
			self::$convertedColors[self::COLOR_DYE_BLACK] = self::BLACK;
			self::$convertedColors[self::COLOR_DYE_BROWN] = self::BROWN;
			self::$convertedColors[self::COLOR_DYE_BLUE] = self::BLUE;
			self::$convertedColors[self::COLOR_DYE_WHITE] = self::WHITE;
		}
	}

	public static function getRGB($r, $g, $b){
		return new Color((int) $r, (int) $g, (int) $b);
	}

	public static function averageColor(Color ...$colors){
		$tr = 0;//total red
		$tg = 0;//green
		$tb = 0;//blue
		$count = 0;
		foreach($colors as $c){
			$tr += $c->getRed();
			$tg += $c->getGreen();
			$tb += $c->getBlue();
			++$count;
		}
		return self::getRGB($tr / $count, $tg / $count, $tb / $count);
	}

	public function getRed(){
		return (int) $this->red;
	}

	public function getGreen(){
		return (int) $this->green;
	}

	public function getBlue(){
		return (int) $this->blue;
	}

	public static function getDyeColor($id){
		if(isset(self::$dyeColors[$id])){
			return clone self::$dyeColors[$id];
		}
		return Color::getRGB(0, 0, 0);
	}

	public function getColorCode(){
		return ($this->red << 16 | $this->green << 8 | $this->blue) & 0xffffff;
	}

	/**
	 * Converts dye color to "real" color that can be used e.g. for tamed wolves
	 *
	 * @param int $dyeColor
	 * @return int
	 */
	public static function convert(int $dyeColor) : int{
		if(!isset(self::$convertedColors[$dyeColor])){
			PureEntities::logOutput("Found invalid dye color code $dyeColor", LogLevel::WARNING);
			return self::WHITE;
		}
		return self::$convertedColors[$dyeColor];
	}

	public function __toString(){
		return "Color(red:" . $this->red . ", green:" . $this->green . ", blue:" . $this->blue . ")";
	}
}