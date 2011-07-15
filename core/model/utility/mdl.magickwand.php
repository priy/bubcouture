<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_magickwand
{

    var $src_image_name = "";
    var $jpeg_quality = 90;
    var $save_file = "";
    var $wm_image_name = "";
    var $magickwand_loaded = false;
    var $wm_image_pos = 1;
    var $wm_image_transition = 80;
    var $emboss = false;
    var $wm_text = "";
    var $wm_text_size = 20;
    var $wm_text_angle = 4;
    var $wm_text_font = "";
    var $wm_text_color = "#FF0000";
    var $wm_angle;

    function mdl_magickwand( )
    {
        if ( function_exists( "NewMagickWand" ) )
        {
            $this->magickwand_loaded = true;
        }
    }

    function filecheck( )
    {
        $font_dir = PUBLIC_DIR."/fonts/";
        if ( !is_file( $this->wm_image_name ) )
        {
            $this->wm_image_name = "";
        }
        if ( !is_file( $font_dir.$this->wm_text_font ) )
        {
            $this->wm_text = "";
        }
        else
        {
            $this->wm_text_font = $font_dir.$this->wm_text_font;
        }
    }

    function makethumb( $objWidth = 128, $objHeight = 128 )
    {
        $image_info = $this->getinfo( $this->src_image_name );
        if ( !$image_info )
        {
            return false;
        }
        $src_image_type = $image_info['type'];
        $res = $this->createthumb( $objWidth, $objHeight );
        $this->savefile( $src_image_type, $res );
        clearmagickwand( $res );
        return true;
    }

    function makethumbwatermark( $objWidth = 128, $objHeight = 128 )
    {
        $this->filecheck( );
        $image_info = $this->getinfo( $this->src_image_name );
        if ( !$image_info )
        {
            return false;
        }
        $src_image_type = $image_info['type'];
        $objWidth = $objWidth == 0 ? $image_info['width'] : $objWidth;
        $objHeight = $objHeight == 0 ? $image_info['height'] : $objHeight;
        $objWidth = $image_info['width'] < $objWidth ? $image_info['width'] : $objWidth;
        $objHeight = $image_info['height'] < $objHeight ? $image_info['height'] : $objHeight;
        $thumb = $this->createthumb( $objWidth, $objHeight );
        $thumbwm = $this->createwatermark( $thumb );
        $this->savefile( $src_image_type, $thumbwm );
        clearmagickwand( $thumbwm );
        return true;
    }

    function createwatermark( $src_image = "" )
    {
        if ( !ismagickwand( $src_image ) )
        {
            $src_image = newmagickwand( );
            magickreadimage( $src_image, $this->src_image_name );
        }
        if ( !$src_image )
        {
            return false;
        }
        $src_image_w = magickgetimagewidth( $src_image );
        $src_image_h = magickgetimageheight( $src_image );
        if ( $this->wm_image_name )
        {
            $wm_image_info = $this->getinfo( $this->wm_image_name );
            if ( !$wm_image_info )
            {
                return false;
            }
            $wm_image = newmagickwand( );
            magickreadimage( $wm_image, $this->wm_image_name );
            $wm_image_w = magickgetimagewidth( $wm_image );
            $wm_image_h = magickgetimageheight( $wm_image );
            $temp_wm_image = $this->getpos( $src_image_w, $src_image_h, $this->wm_image_pos, $wm_image );
            $wm_image_x = $temp_wm_image['dest_x'];
            $wm_image_y = $temp_wm_image['dest_y'];
            $opacity0 = magickgetquantumrange( );
            $opacity100 = 0;
            $opacitypercent = $this->wm_image_transition;
            $opacity = $opacity0 - $opacity0 * $opacitypercent / 100;
            if ( $opacity0 < $opacity )
            {
                $opacity = $opacity0;
            }
            else if ( $opacity < 0 )
            {
                $opacity = 0;
            }
            magicksetimageindex( $wm_image, 0 );
            magicksetimagetype( $wm_image, MW_TrueColorMatteType );
            magickevaluateimage( $wm_image, MW_SubtractEvaluateOperator, $opacity, MW_OpacityChannel );
            magickcompositeimage( $src_image, $wm_image, MW_OverCompositeOp, $wm_image_x, $wm_image_y );
        }
        if ( $this->wm_text )
        {
            $this->wm_text = $this->wm_text;
            $temp_wm_text = $this->getpos( $src_image_w, $src_image_h, $this->wm_image_pos );
            $wm_text_x = $temp_wm_text['dest_x'];
            $wm_text_y = $temp_wm_text['dest_y'];
            $drawing_wand = newdrawingwand( );
            if ( $this->wm_text_font != "" )
            {
                drawsetfont( $drawing_wand, $this->wm_text_font );
            }
            drawsetfontsize( $drawing_wand, $this->wm_text_size );
            switch ( $this->wm_image_pos )
            {
            case 0 :
                drawsetgravity( $drawing_wand, MW_CenterGravity );
                break;
            case 1 :
                drawsetgravity( $drawing_wand, MW_NorthWestGravity );
                break;
            case 2 :
                drawsetgravity( $drawing_wand, MW_NorthEastGravity );
                break;
            case 3 :
                drawsetgravity( $drawing_wand, MW_SouthEastGravity );
                break;
            case 4 :
                drawsetgravity( $drawing_wand, MW_SouthWestGravity );
                break;
            case 5 :
                drawsetgravity( $drawing_wand, MW_NorthGravity );
                break;
            case 6 :
                drawsetgravity( $drawing_wand, MW_EastGravity );
                break;
            case 7 :
                drawsetgravity( $drawing_wand, MW_SouthGravity );
                break;
            case 8 :
                drawsetgravity( $drawing_wand, MW_WestGravity );
                break;
            default :
                drawsetgravity( $drawing_wand, MW_CenterGravity );
            }
            $pixel_wand = newpixelwand( );
            if ( preg_match( "/([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])/i", $this->wm_text_color, $color ) )
            {
                $red = hexdec( $color[1] );
                $green = hexdec( $color[2] );
                $blue = hexdec( $color[3] );
                pixelsetcolor( $pixel_wand, "rgb(".$red.",{$green},{$blue})" );
            }
            else
            {
                pixelsetcolor( $pixel_wand, "rgb(255,255,255)" );
            }
            drawsetfillcolor( $drawing_wand, $pixel_wand );
            magickannotateimage( $src_image, $drawing_wand, 0, 0, $this->wm_angle, $this->wm_text );
        }
        return $src_image;
    }

    function createthumb( $objWidth, $objHeight, $nmw = "" )
    {
        $srcImage = $this->src_image_name;
        if ( !ismagickwand( $nmw ) )
        {
            $nmw = newmagickwand( );
            magickreadimage( $nmw, $srcImage );
        }
        $srcImageWidth = magickgetimagewidth( $nmw );
        $srcImageHeight = magickgetimageheight( $nmw );
        if ( $objWidth == 0 || $objHeight == 0 )
        {
            $objWidth = $srcImageWidth;
            $objHeight = $srcImageHeight;
        }
        if ( $objWidth < $objHeight )
        {
            $mu = $srcImageWidth / $objWidth;
            $objHeight = ceil( $srcImageHeight / $mu );
        }
        else
        {
            $mu = $srcImageHeight / $objHeight;
            $objWidth = ceil( $srcImageWidth / $mu );
        }
        magickscaleimage( $nmw, $objWidth, $objHeight );
        $ndw = newdrawingwand( );
        drawcomposite( $ndw, MW_AddCompositeOp, 0, 0, $objWidth, $objHeight, $nmw );
        $res = newmagickwand( );
        magicknewimage( $res, $objWidth, $objHeight );
        magickdrawimage( $res, $ndw );
        magicksetimageformat( $res, magickgetimageformat( $nmw ) );
        return $res;
    }

    function savefile( $src_image_type, $src_image )
    {
        if ( $this->save_file )
        {
            magickwriteimage( $src_image, $this->save_file );
        }
        else
        {
            switch ( $src_image_type )
            {
            case 1 :
                header( "Content-type: image/gif" );
                magickechoimageblob( $src_image );
                return;
            case 2 :
                header( "Content-type: image/jpeg" );
                magickechoimageblob( $src_image );
                return;
            case 3 :
                header( "Content-type: image/png" );
                magickechoimageblob( $src_image );
                return;
            case 6 :
                header( "Content-type: image/bmp" );
                magickechoimageblob( $src_image );
                return;
            }
            header( "Content-type: image/jpeg" );
            magickechoimageblob( $src_image );
        }
    }

    function getpos( $sourcefile_width, $sourcefile_height, $pos, $wm_image = "" )
    {
        if ( $wm_image )
        {
            $insertfile_width = magickgetimagewidth( $wm_image );
            $insertfile_height = magickgetimageheight( $wm_image );
        }
        switch ( $pos )
        {
        case 0 :
            $dest_x = $sourcefile_width / 2 - $insertfile_width / 2;
            $dest_y = $sourcefile_height / 2 - $insertfile_height / 2;
            break;
        case 1 :
            $dest_x = 0;
            if ( $this->wm_text )
            {
                $dest_y = $insertfile_height;
            }
            else
            {
                $dest_y = 0;
            }
            break;
        case 2 :
            $dest_x = $sourcefile_width - $insertfile_width;
            if ( $this->wm_text )
            {
                $dest_y = $insertfile_height;
            }
            else
            {
                $dest_y = 0;
            }
            break;
        case 3 :
            $dest_x = $sourcefile_width - $insertfile_width;
            $dest_y = $sourcefile_height - $insertfile_height;
            break;
        case 4 :
            $dest_x = 0;
            $dest_y = $sourcefile_height - $insertfile_height;
            break;
        case 5 :
            $dest_x = ( $sourcefile_width - $insertfile_width ) / 2;
            if ( $this->wm_text )
            {
                $dest_y = $insertfile_height;
            }
            else
            {
                $dest_y = 0;
            }
            break;
        case 6 :
            $dest_x = $sourcefile_width - $insertfile_width;
            $dest_y = $sourcefile_height / 2 - $insertfile_height / 2;
            break;
        case 7 :
            $dest_x = ( $sourcefile_width - $insertfile_width ) / 2;
            $dest_y = $sourcefile_height - $insertfile_height;
            break;
        case 8 :
            $dest_x = 0;
            $dest_y = $sourcefile_height / 2 - $insertfile_height / 2;
            break;
            $dest_x = $sourcefile_width - $insertfile_width;
            $dest_y = $sourcefile_height - $insertfile_height;
        }
        return array(
            "dest_x" => $dest_x,
            "dest_y" => $dest_y
        );
    }

    function getinfo( $file )
    {
        if ( !file_exists( $file ) )
        {
            return false;
        }
        $data = getimagesize( $file );
        $imageInfo['width'] = $data[0];
        $imageInfo['height'] = $data[1];
        $imageInfo['type'] = $data[2];
        $imageInfo['name'] = basename( $file );
        return $imageInfo;
    }

}

?>
