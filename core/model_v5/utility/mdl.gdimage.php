<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_gdimage
{

    public $src_image_name = "";
    public $jpeg_quality = 90;
    public $save_file = "";
    public $wm_image_name = "";
    public $gd_loaded = false;
    public $wm_image_pos = 1;
    public $wm_image_transition = 80;
    public $emboss = false;
    public $wm_text = "";
    public $wm_text_size = 20;
    public $wm_text_angle = 4;
    public $wm_text_font = "";
    public $wm_text_color = "#FF0000";
    public $gif_enable = NULL;
    public $wm_angle = NULL;

    public function mdl_gdimage( )
    {
        if ( function_exists( "imagegif" ) )
        {
            $this->gif_enable = true;
        }
        else
        {
            $this->gif_enable = false;
        }
        if ( function_exists( "imagecreate" ) )
        {
            $this->gd_loaded = true;
        }
    }

    public function fileCheck( )
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

    public function makeThumbWatermark( $width = 128, $height = 128 )
    {
        $this->fileCheck( );
        $image_info = $this->getInfo( $this->src_image_name );
        if ( !$image_info )
        {
            return false;
        }
        $src_image_type = $image_info['type'];
        $img = $this->createImage( $src_image_type, $this->src_image_name );
        if ( !$img )
        {
            return false;
        }
        $width = $width == 0 ? $image_info['width'] : $width;
        $height = $height == 0 ? $image_info['height'] : $height;
        $width = $image_info['width'] < $width ? $image_info['width'] : $width;
        $height = $image_info['height'] < $height ? $image_info['height'] : $height;
        $srcW = $image_info['width'];
        $srcH = $image_info['height'];
        if ( $srcW * $height < $srcH * $width )
        {
            $width = round( $srcW * $height / $srcH );
        }
        else
        {
            $height = round( $srcH * $width / $srcW );
        }
        $src_image = @imagecreatetruecolor( $width, $height );
        $white = @imagecolorallocate( $src_image, 255, 255, 255 );
        @imagecolortransparent( $src_image, $white );
        @imagefilltoborder( $src_image, 0, 0, $white, $white );
        if ( $src_image )
        {
            imagecopyresampled( $src_image, $img, 0, 0, 0, 0, $width, $height, $image_info['width'], $image_info['height'] );
        }
        else
        {
            $src_image = imagecreate( $width, $height );
            imagecopyresized( $src_image, $img, 0, 0, 0, 0, $width, $height, $image_info['width'], $image_info['height'] );
        }
        $src_image_w = imagesx( $src_image );
        $src_image_h = imagesy( $src_image );
        if ( $this->wm_image_name )
        {
            $wm_image_info = $this->getInfo( $this->wm_image_name );
            if ( !$wm_image_info )
            {
                return false;
            }
            $wm_image_type = $wm_image_info['type'];
            $wm_image = $this->createImage( $wm_image_type, $this->wm_image_name );
            $wm_image_w = imagesx( $wm_image );
            $wm_image_h = imagesy( $wm_image );
            $temp_wm_image = $this->getPos( $src_image_w, $src_image_h, $this->wm_image_pos, $wm_image );
            if ( $this->emboss && function_exists( "imagefilter" ) )
            {
                imagefilter( $wm_image, IMG_FILTER_EMBOSS );
                $bgcolor = imagecolorclosest( $wm_image, 127, 127, 127 );
                imagecolortransparent( $wm_image, $bgcolor );
            }
            if ( function_exists( "ImageAlphaBlending" ) && IMAGETYPE_PNG == $wm_image_info['type'] )
            {
                imagealphablending( $src_image, true );
            }
            $wm_image_x = $temp_wm_image['dest_x'];
            $wm_image_y = $temp_wm_image['dest_y'];
            if ( IMAGETYPE_PNG == $wm_image_info['type'] )
            {
                imagecopy( $src_image, $wm_image, $wm_image_x, $wm_image_y, 0, 0, $wm_image_w, $wm_image_h );
            }
            else
            {
                imagecopymerge( $src_image, $wm_image, $wm_image_x, $wm_image_y, 0, 0, $wm_image_w, $wm_image_h, $this->wm_image_transition );
            }
        }
        if ( $this->wm_text )
        {
            $this->wm_text = $this->wm_text;
            $temp_wm_text = $this->getPos( $src_image_w, $src_image_h, $this->wm_image_pos );
            $wm_text_x = $temp_wm_text['dest_x'];
            $wm_text_y = $temp_wm_text['dest_y'];
            if ( preg_match( "/([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])/i", $this->wm_text_color, $color ) )
            {
                $red = hexdec( $color[1] );
                $green = hexdec( $color[2] );
                $blue = hexdec( $color[3] );
                $wm_text_color = imagecolorallocate( $src_image, $red, $green, $blue );
            }
            else
            {
                $wm_text_color = imagecolorallocate( $src_image, 255, 255, 255 );
            }
            imagettftext( $src_image, $this->wm_text_size, $this->wm_angle, $wm_text_x, $wm_text_y, $wm_text_color, $this->wm_text_font, $this->wm_text );
        }
        if ( $this->save_file )
        {
            switch ( $src_image_type )
            {
            case 1 :
                if ( $this->gif_enable )
                {
                    $src_img = imagegif( $src_image, $this->save_file );
                }
                else
                {
                    $src_img = imagepng( $src_image, $this->save_file );
                }
                break;
            case 2 :
                $src_img = imagejpeg( $src_image, $this->save_file, $this->jpeg_quality );
                break;
            case 3 :
                $src_img = imagepng( $src_image, $this->save_file );
                break;
            default :
                $src_img = imagejpeg( $src_image, $this->save_file, $this->jpeg_quality );
                break;
            }
        }
        else
        {
            switch ( $src_image_type )
            {
            case 1 :
                if ( $this->gif_enable )
                {
                    header( "Content-type: image/gif" );
                    $src_img = imagegif( $src_image );
                }
                else
                {
                    header( "Content-type: image/png" );
                    $src_img = imagepng( $src_image );
                }
                break;
            case 2 :
                header( "Content-type: image/jpeg" );
                $src_img = imagejpeg( $src_image, "", $this->jpeg_quality );
                break;
            case 3 :
                header( "Content-type: image/png" );
                $src_img = imagepng( $src_image );
                break;
            case 6 :
                header( "Content-type: image/bmp" );
                $src_img = imagebmp( $src_image );
                break;
            default :
                header( "Content-type: image/jpeg" );
                $src_img = imagejpeg( $src_image, "", $this->jpeg_quality );
                break;
            }
        }
        imagedestroy( $src_image );
        imagedestroy( $img );
        return true;
    }

    public function createImage( $type, $img_name )
    {
        switch ( $type )
        {
        case 1 :
            if ( function_exists( "imagecreatefromgif" ) )
            {
                $tmp_img = @imagecreatefromgif( $img_name );
            }
            else
            {
                return false;
            }
        case 2 :
            $tmp_img = imagecreatefromjpeg( $img_name );
            break;
        case 3 :
            $tmp_img = imagecreatefrompng( $img_name );
            break;
        case 6 :
            $tmp_img = imagecreatefrombmp( $img_name );
            break;
        default :
            $tmp_img = imagecreatefromstring( $img_name );
            break;
        }
        return $tmp_img;
    }

    public function getPos( $sourcefile_width, $sourcefile_height, $pos, $wm_image = "" )
    {
        if ( $wm_image )
        {
            $insertfile_width = imagesx( $wm_image );
            $insertfile_height = imagesy( $wm_image );
        }
        else
        {
            $lineCount = explode( "\n", $this->wm_text );
            $fontSize = imagettfbbox( $this->wm_text_size, $this->wm_text_angle, $this->wm_text_font, $this->wm_text );
            $insertfile_width = $fontSize[2] - $fontSize[0];
            $insertfile_height = count( $lineCount ) * ( $fontSize[1] - $fontSize[7] );
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
        default :
            $dest_x = $sourcefile_width - $insertfile_width;
            $dest_y = $sourcefile_height - $insertfile_height;
            break;
        }
        return array(
            "dest_x" => $dest_x,
            "dest_y" => $dest_y
        );
    }

    public function getInfo( $file )
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
