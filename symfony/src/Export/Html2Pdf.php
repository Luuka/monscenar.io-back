<?php


namespace App\Export;


use Spipu\Html2Pdf\Exception\ImageException;

class Html2Pdf extends \Spipu\Html2Pdf\Html2Pdf
{
    /**
     * draw a rectangle
     *
     * @access protected
     * @param  float $x
     * @param  float $y
     * @param  float $w
     * @param  float $h
     * @param  array $border
     * @param  float $padding - internal margin of the rectangle => not used, but...
     * @param  float $margin  - external margin of the rectangle
     * @param  array $background
     * @return boolean
     */
    protected function _drawRectangle($x, $y, $w, $h, $border, $padding, $margin, $background)
    {
        // if we are in a subpart or if height is null => return false
        if ($this->_subPart || $this->_isSubPart || $h === null) {
            return false;
        }

        // add the margin
        $x+= $margin;
        $y+= $margin;
        $w-= $margin*2;
        $h-= $margin*2;

        // get the radius of the border
        $outTL = $border['radius']['tl'];
        $outTR = $border['radius']['tr'];
        $outBR = $border['radius']['br'];
        $outBL = $border['radius']['bl'];

        // prepare the out radius
        $outTL = ($outTL[0] && $outTL[1]) ? $outTL : null;
        $outTR = ($outTR[0] && $outTR[1]) ? $outTR : null;
        $outBR = ($outBR[0] && $outBR[1]) ? $outBR : null;
        $outBL = ($outBL[0] && $outBL[1]) ? $outBL : null;

        // prepare the in radius
        $inTL = $outTL;
        $inTR = $outTR;
        $inBR = $outBR;
        $inBL = $outBL;

        if (is_array($inTL)) {
            $inTL[0]-= $border['l']['width'];
            $inTL[1]-= $border['t']['width'];
        }
        if (is_array($inTR)) {
            $inTR[0]-= $border['r']['width'];
            $inTR[1]-= $border['t']['width'];
        }
        if (is_array($inBR)) {
            $inBR[0]-= $border['r']['width'];
            $inBR[1]-= $border['b']['width'];
        }
        if (is_array($inBL)) {
            $inBL[0]-= $border['l']['width'];
            $inBL[1]-= $border['b']['width'];
        }

        if ($inTL == NULL || $inTL[0]<=0 || $inTL[1]<=0) {
            $inTL = NULL;
        }
        if ($inTR == NULL || $inTR[0]<=0 || $inTR[1]<=0) {
            $inTR = null;
        }
        if ($inBR == NULL || $inBR[0]<=0 || $inBR[1]<=0) {
            $inBR = null;
        }
        if ($inBL == NULL || $inBL[0]<=0 || $inBL[1]<=0) {
            $inBL = null;
        }

        // prepare the background color
        $pdfStyle = '';
        if ($background['color']) {
            $this->pdf->SetFillColorArray($background['color']);
            $pdfStyle.= 'F';
        }

        // if we have a background to fill => fill it with a path (because of the radius)
        if ($pdfStyle) {
            $this->pdf->clippingPathStart($x, $y, $w, $h, $outTL, $outTR, $outBL, $outBR);
            $this->pdf->Rect($x, $y, $w, $h, $pdfStyle);
            $this->pdf->clippingPathStop();
        }

        // prepare the background image
        if ($background['image']) {
            $iName      = $background['image'];
            $iPosition  = $background['position'] !== null ? $background['position'] : array(0, 0);
            $iRepeat    = $background['repeat'] !== null   ? $background['repeat']   : array(true, true);

            // size of the background without the borders
            $bX = $x;
            $bY = $y;
            $bW = $w;
            $bH = $h;

            if ($border['b']['width']) {
                $bH-= $border['b']['width'];
            }
            if ($border['l']['width']) {
                $bW-= $border['l']['width'];
                $bX+= $border['l']['width'];
            }
            if ($border['t']['width']) {
                $bH-= $border['t']['width'];
                $bY+= $border['t']['width'];
            }
            if ($border['r']['width']) {
                $bW-= $border['r']['width'];
            }

            // get the size of the image
            // WARNING : if URL, "allow_url_fopen" must turned to "on" in php.ini
            $imageInfos=@getimagesize($iName);

            // if the image can not be loaded
            if (!is_array($imageInfos) || count($imageInfos)<2) {
                if ($this->_testIsImage) {
                    $e = new ImageException('Unable to get the size of the image ['.$iName.']');
                    $e->setImage($iName);
                    throw $e;
                }
            } else {
                // convert the size of the image from pixel to the unit of the PDF
                $imageWidth    = 72./96.*$imageInfos[0]/$this->pdf->getK();
                $imageHeight    = 72./96.*$imageInfos[1]/$this->pdf->getK();

                // prepare the position of the backgroung
                if ($iRepeat[0]) {
                    $iPosition[0] = $bX;
                } elseif (preg_match('/^([-]?[0-9\.]+)%/isU', $iPosition[0], $match)) {
                    $iPosition[0] = $bX + $match[1]*($bW-$imageWidth)/100;
                } else {
                    $iPosition[0] = $bX+$iPosition[0];
                }

                if ($iRepeat[1]) {
                    $iPosition[1] = $bY;
                } elseif (preg_match('/^([-]?[0-9\.]+)%/isU', $iPosition[1], $match)) {
                    $iPosition[1] = $bY + $match[1]*($bH-$imageHeight)/100;
                } else {
                    $iPosition[1] = $bY+$iPosition[1];
                }

                $imageXmin = $bX;
                $imageXmax = $bX+$bW;
                $imageYmin = $bY;
                $imageYmax = $bY+$bH;

                if (!$iRepeat[0] && !$iRepeat[1]) {
                    $imageXmin =     $iPosition[0];
                    $imageXmax =     $iPosition[0]+$imageWidth;
                    $imageYmin =     $iPosition[1];
                    $imageYmax =     $iPosition[1]+$imageHeight;
                } elseif ($iRepeat[0] && !$iRepeat[1]) {
                    $imageYmin =     $iPosition[1];
                    $imageYmax =     $iPosition[1]+$imageHeight;
                } elseif (!$iRepeat[0] && $iRepeat[1]) {
                    $imageXmin =     $iPosition[0];
                    $imageXmax =     $iPosition[0]+$imageWidth;
                }

                // build the path to display the image (because of radius)
                $this->pdf->clippingPathStart($bX, $bY, $bW, $bH, $inTL, $inTR, $inBL, $inBR);

                // repeat the image
                for ($iY=$imageYmin; $iY<$imageYmax; $iY+=$imageHeight) {
                    for ($iX=$imageXmin; $iX<$imageXmax; $iX+=$imageWidth) {
                        $cX = null;
                        $cY = null;
                        $cW = $imageWidth;
                        $cH = $imageHeight;
                        if ($imageYmax-$iY<$imageHeight) {
                            $cX = $iX;
                            $cY = $iY;
                            $cH = $imageYmax-$iY;
                        }
                        if ($imageXmax-$iX<$imageWidth) {
                            $cX = $iX;
                            $cY = $iY;
                            $cW = $imageXmax-$iX;
                        }

                        $this->pdf->Image($iName, $iX, $iY, $imageWidth, $imageHeight, '', '');
                    }
                }

                // end of the path
                $this->pdf->clippingPathStop();
            }
        }

        // adding some loose (0.01mm)
        $loose = 0.01;
        $x-= $loose;
        $y-= $loose;
        $w+= 2.*$loose;
        $h+= 2.*$loose;
        if ($border['l']['width']) {
            $border['l']['width']+= 2.*$loose;
        }
        if ($border['t']['width']) {
            $border['t']['width']+= 2.*$loose;
        }
        if ($border['r']['width']) {
            $border['r']['width']+= 2.*$loose;
        }
        if ($border['b']['width']) {
            $border['b']['width']+= 2.*$loose;
        }

        // prepare the test on borders
        $testBl = ($border['l']['width'] && $border['l']['color'][0] !== null);
        $testBt = ($border['t']['width'] && $border['t']['color'][0] !== null);
        $testBr = ($border['r']['width'] && $border['r']['color'][0] !== null);
        $testBb = ($border['b']['width'] && $border['b']['color'][0] !== null);

        // draw the radius bottom-left
        if (is_array($outBL) && ($testBb || $testBl)) {
            if ($inBL) {
                $courbe = array();
                $courbe[] = $x+$outBL[0];
                $courbe[] = $y+$h;
                $courbe[] = $x;
                $courbe[] = $y+$h-$outBL[1];
                $courbe[] = $x+$outBL[0];
                $courbe[] = $y+$h-$border['b']['width'];
                $courbe[] = $x+$border['l']['width'];
                $courbe[] = $y+$h-$outBL[1];
                $courbe[] = $x+$outBL[0];
                $courbe[] = $y+$h-$outBL[1];
            } else {
                $courbe = array();
                $courbe[] = $x+$outBL[0];
                $courbe[] = $y+$h;
                $courbe[] = $x;
                $courbe[] = $y+$h-$outBL[1];
                $courbe[] = $x+$border['l']['width'];
                $courbe[] = $y+$h-$border['b']['width'];
                $courbe[] = $x+$outBL[0];
                $courbe[] = $y+$h-$outBL[1];
            }
            $this->_drawCurve($courbe, $border['l']['color']);
        }

        // draw the radius left-top
        if (is_array($outTL) && ($testBt || $testBl)) {
            if ($inTL) {
                $courbe = array();
                $courbe[] = $x;
                $courbe[] = $y+$outTL[1];
                $courbe[] = $x+$outTL[0];
                $courbe[] = $y;
                $courbe[] = $x+$border['l']['width'];
                $courbe[] = $y+$outTL[1];
                $courbe[] = $x+$outTL[0];
                $courbe[] = $y+$border['t']['width'];
                $courbe[] = $x+$outTL[0];
                $courbe[] = $y+$outTL[1];
            } else {
                $courbe = array();
                $courbe[] = $x;
                $courbe[] = $y+$outTL[1];
                $courbe[] = $x+$outTL[0];
                $courbe[] = $y;
                $courbe[] = $x+$border['l']['width'];
                $courbe[] = $y+$border['t']['width'];
                $courbe[] = $x+$outTL[0];
                $courbe[] = $y+$outTL[1];
            }
            $this->_drawCurve($courbe, $border['t']['color']);
        }

        // draw the radius top-right
        if (is_array($outTR) && ($testBt || $testBr)) {
            if ($inTR) {
                $courbe = array();
                $courbe[] = $x+$w-$outTR[0];
                $courbe[] = $y;
                $courbe[] = $x+$w;
                $courbe[] = $y+$outTR[1];
                $courbe[] = $x+$w-$outTR[0];
                $courbe[] = $y+$border['t']['width'];
                $courbe[] = $x+$w-$border['r']['width'];
                $courbe[] = $y+$outTR[1];
                $courbe[] = $x+$w-$outTR[0];
                $courbe[] = $y+$outTR[1];
            } else {
                $courbe = array();
                $courbe[] = $x+$w-$outTR[0];
                $courbe[] = $y;
                $courbe[] = $x+$w;
                $courbe[] = $y+$outTR[1];
                $courbe[] = $x+$w-$border['r']['width'];
                $courbe[] = $y+$border['t']['width'];
                $courbe[] = $x+$w-$outTR[0];
                $courbe[] = $y+$outTR[1];
            }
            $this->_drawCurve($courbe, $border['r']['color']);
        }

        // draw the radius right-bottom
        if (is_array($outBR) && ($testBb || $testBr)) {
            if ($inBR) {
                $courbe = array();
                $courbe[] = $x+$w;
                $courbe[] = $y+$h-$outBR[1];
                $courbe[] = $x+$w-$outBR[0];
                $courbe[] = $y+$h;
                $courbe[] = $x+$w-$border['r']['width'];
                $courbe[] = $y+$h-$outBR[1];
                $courbe[] = $x+$w-$outBR[0];
                $courbe[] = $y+$h-$border['b']['width'];
                $courbe[] = $x+$w-$outBR[0];
                $courbe[] = $y+$h-$outBR[1];
            } else {
                $courbe = array();
                $courbe[] = $x+$w;
                $courbe[] = $y+$h-$outBR[1];
                $courbe[] = $x+$w-$outBR[0];
                $courbe[] = $y+$h;
                $courbe[] = $x+$w-$border['r']['width'];
                $courbe[] = $y+$h-$border['b']['width'];
                $courbe[] = $x+$w-$outBR[0];
                $courbe[] = $y+$h-$outBR[1];
            }
            $this->_drawCurve($courbe, $border['b']['color']);
        }

        // draw the left border
        if ($testBl) {
            $pt = array();
            $pt[] = $x;
            $pt[] = $y+$h;
            $pt[] = $x;
            $pt[] = $y+$h-$border['b']['width'];
            $pt[] = $x;
            $pt[] = $y+$border['t']['width'];
            $pt[] = $x;
            $pt[] = $y;
            $pt[] = $x+$border['l']['width'];
            $pt[] = $y+$border['t']['width'];
            $pt[] = $x+$border['l']['width'];
            $pt[] = $y+$h-$border['b']['width'];

            $bord = 3;
            if (is_array($outBL)) {
                $bord-=1;
                $pt[3] -= $outBL[1] - $border['b']['width'];
                if ($inBL) {
                    $pt[11]-= $inBL[1];
                }
                unset($pt[0]);
                unset($pt[1]);
            }
            if (is_array($outTL)) {
                $bord-=2;
                $pt[5] += $outTL[1]-$border['t']['width'];
                if ($inTL) {
                    $pt[9] += $inTL[1];
                }
                unset($pt[6]);
                unset($pt[7]);
            }

            $pt = array_values($pt);
            $this->_drawLine($pt, $border['l']['color'], $border['l']['type'], $border['l']['width'], $bord);
        }

        // draw the top border
        if ($testBt) {
            $pt = array();
            $pt[] = $x;
            $pt[] = $y;
            $pt[] = $x+$border['l']['width'];
            $pt[] = $y;
            $pt[] = $x+$w-$border['r']['width'];
            $pt[] = $y;
            $pt[] = $x+$w;
            $pt[] = $y;
            $pt[] = $x+$w-$border['r']['width'];
            $pt[] = $y+$border['t']['width'];
            $pt[] = $x+$border['l']['width'];
            $pt[] = $y+$border['t']['width'];

            $bord = 3;
            if (is_array($outTL)) {
                $bord-=1;
                $pt[2] += $outTL[0] - $border['l']['width'];
                if ($inTL) {
                    $pt[10]+= $inTL[0];
                }
                unset($pt[0]);
                unset($pt[1]);
            }
            if (is_array($outTR)) {
                $bord-=2;
                $pt[4] -= $outTR[0] - $border['r']['width'];
                if ($inTR) {
                    $pt[8] -= $inTR[0];
                }
                unset($pt[6]);
                unset($pt[7]);
            }

            $pt = array_values($pt);
            $this->_drawLine($pt, $border['t']['color'], $border['t']['type'], $border['t']['width'], $bord);
        }

        // draw the right border
        if ($testBr) {
            $pt = array();
            $pt[] = $x+$w;
            $pt[] = $y;
            $pt[] = $x+$w;
            $pt[] = $y+$border['t']['width'];
            $pt[] = $x+$w;
            $pt[] = $y+$h-$border['b']['width'];
            $pt[] = $x+$w;
            $pt[] = $y+$h;
            $pt[] = $x+$w-$border['r']['width'];
            $pt[] = $y+$h-$border['b']['width'];
            $pt[] = $x+$w-$border['r']['width'];
            $pt[] = $y+$border['t']['width'];

            $bord = 3;
            if (is_array($outTR)) {
                $bord-=1;
                $pt[3] += $outTR[1] - $border['t']['width'];
                if ($inTR) {
                    $pt[11]+= $inTR[1];
                }
                unset($pt[0]);
                unset($pt[1]);
            }
            if (is_array($outBR)) {
                $bord-=2;
                $pt[5] -= $outBR[1] - $border['b']['width'];
                if ($inBR) {
                    $pt[9] -= $inBR[1];
                }
                unset($pt[6]);
                unset($pt[7]);
            }

            $pt = array_values($pt);
            $this->_drawLine($pt, $border['r']['color'], $border['r']['type'], $border['r']['width'], $bord);
        }

        // draw the bottom border
        if ($testBb) {
            $pt = array();
            $pt[] = $x+$w;
            $pt[] = $y+$h;
            $pt[] = $x+$w-$border['r']['width'];
            $pt[] = $y+$h;
            $pt[] = $x+$border['l']['width'];
            $pt[] = $y+$h;
            $pt[] = $x;
            $pt[] = $y+$h;
            $pt[] = $x+$border['l']['width'];
            $pt[] = $y+$h-$border['b']['width'];
            $pt[] = $x+$w-$border['r']['width'];
            $pt[] = $y+$h-$border['b']['width'];

            $bord = 3;
            if (is_array($outBL)) {
                $bord-=2;
                $pt[4] += $outBL[0] - $border['l']['width'];
                if ($inBL) {
                    $pt[8] += $inBL[0];
                }
                unset($pt[6]);
                unset($pt[7]);
            }
            if (is_array($outBR)) {
                $bord-=1;
                $pt[2] -= $outBR[0] - $border['r']['width'];
                if ($inBR) {
                    $pt[10]-= $inBR[0];
                }
                unset($pt[0]);
                unset($pt[1]);

            }

            $pt = array_values($pt);
            $this->_drawLine($pt, $border['b']['color'], $border['b']['type'], $border['b']['width'], $bord);
        }

        if ($background['color']) {
            $this->pdf->SetFillColorArray($background['color']);
        }

        return true;
    }
}