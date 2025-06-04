<?php
/**
 * @package     JCE
 * @subpackage  Editor
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @copyright   Copyright (c) 2009-2024 Ryan Demmer. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

?>
<svg id="svg-fx" version="1.1" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <filter id="grayscale">
            <feColorMatrix type="matrix" values="0.33 0.33 0.33 0 0 0.33 0.33 0.33 0 0 0.33 0.33 0.33 0 0 0 0 0 1 0"/>
        </filter>
        <filter id="blur">
            <feGaussianBlur stdDeviation="0" />
        </filter>
        <filter id="invert">
            <!--feColorMatrix type="matrix" values="-1 0 0 0 1 0 -1 0 0 1 0 0 -1 0 1 0 0 0 1 0"/-->
            <feComponentTransfer>
		<feFuncR type="table" tableValues="1 0"/>
		<feFuncG type="table" tableValues="1 0"/>
		<feFuncB type="table" tableValues="1 0"/>
            </feComponentTransfer>
        </filter>
        <filter id="sepia">
            <feColorMatrix type="matrix" values="0.393 0.769 0.189 0 0 0.349 0.686 0.168 0 0 0.272 0.534 0.131 0 0 0 0 0 1 0"/>
        </filter>
        <filter id="saturate">
            <feColorMatrix type="matrix" values="1 0 0 0 0 0 1 0 0 0 0 0 1 0 0 0 0 0 1 0"/>
        </filter>
        <filter id="desaturate">
            <feColorMatrix type="matrix" values="1 0 0 0 0 0 1 0 0 0 0 0 1 0 0 0 0 0 1 0"/>
        </filter>
        <filter id="brightness">
            <feComponentTransfer>
                <feFuncR type="linear" slope="1"/>
                <feFuncG type="linear" slope="1"/>
                <feFuncB type="linear" slope="1"/>
            </feComponentTransfer>
        </filter>

        <filter id="Sharpen">
          <feConvolveMatrix order="3 3" preserveAlpha="true" kernelMatrix="0 -1 0 -1 5 -1 0 -1 0"/>
        </filter>

        <!--filter id="contrast">
            <feComponentTransfer>
              <feFuncR type="linear" slope="[amount]" intercept="-(0.5 * [amount]) + 0.5"/>
              <feFuncG type="linear" slope="[amount]" intercept="-(0.5 * [amount]) + 0.5"/>
              <feFuncB type="linear" slope="[amount]" intercept="-(0.5 * [amount]) + 0.5"/>
            </feComponentTransfer>
          </filter>
        <filter id="hue-rotate" >
          <feColorMatrix type="hueRotate" values="[angle]" />
        <filter /-->
    </defs>
</svg>
