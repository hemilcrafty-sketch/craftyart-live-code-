<?php
namespace App\Http\Controllers\Utils;

use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Format\Video\X264;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class AudioVideoManager extends Controller
{

 
    private static function formatTime($duration): string //as hh:mm:ss
    {
        //return sprintf("%d:%02d", $duration/60, $duration%60);
        $hours = floor($duration / 3600);
        $minutes = floor( ($duration - ($hours * 3600)) / 60);
        $seconds = $duration - ($hours * 3600) - ($minutes * 60);
        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }

    public static function getVideoMeta($videoPath, $isCompress = true,$compressVideoPath = null,$isThumbnailCreate=false,$thumbnailPath = null): array
    {
        $meta = [
            'width' => null,
            'height' => null,
            'duration' => null,
            'compress_vdo' => null
        ];

        if (!file_exists($videoPath)) {
            return $meta;
        }

        try {
            $config = [
                'ffmpeg.binaries' => '/usr/bin/ffmpeg',
                'ffprobe.binaries' => '/usr/bin/ffprobe',
                'timeout' => 60,
                'ffmpeg.threads' => 12,
            ];

            $ffmpeg = FFMpeg::create($config);
            $video = $ffmpeg->open($videoPath);
            $videoStream = $video->getStreams()->videos()->first();

            if ($videoStream) {
                $dimensions = $videoStream->getDimensions();
                $meta['width'] = $dimensions->getWidth();
                $meta['height'] = $dimensions->getHeight();
            }

            $ffprobe = FFProbe::create($config);
            $meta['duration'] = $ffprobe->format($videoPath)->get('duration') * 1000;

            if ($isCompress) {
                $aspectWidth = 150;
                $aspectHeight = 150;
                if($meta['width'] > $meta['height']) {
                    $aspectHeight = self::getNewHeight($meta['width'],$meta['height'],$aspectWidth);
                } else {
                    $aspectWidth = self::getNewWidth($meta['width'],$meta['height'],$aspectHeight);
                }
                $outputPath = storage_path('app/public/'.$compressVideoPath );
                $video->filters()
                    ->clip(TimeCode::fromSeconds(0), TimeCode::fromSeconds(4))
                    ->resize(new Dimension($aspectWidth, $aspectHeight))
                    ->synchronize();
                $video->save(new X264(), $outputPath);
                $meta['compress_vdo'] = $compressVideoPath;

                if ($isThumbnailCreate) {
//                    $compressedVideo = $ffmpeg->open($outputPath); // reopen compressed video
                    $frame = $video->frame(TimeCode::fromSeconds(1));
                    $localThumbnailPath = storage_path('app/temp/thumb_' . uniqid() . '.jpg');
                    $frame->save($localThumbnailPath);
                    Storage::disk('cloudflare_r2')->put($thumbnailPath, file_get_contents($localThumbnailPath));
                    $meta['thumbnail'] = $thumbnailPath;
                    File::delete($localThumbnailPath);
                }
            }
            return $meta;
        } catch (\Exception $e) {
            throw new \Exception('Something went wrong');
        }
    }

    public static function getNewWidth($originalWidth, $originalHeight, $newHeight): float|int
    {
        return ($originalWidth / $originalHeight) * $newHeight;
    }

    public static function getNewHeight($originalWidth, $originalHeight, $newWidth): float|int
    {
        return ($originalHeight / $originalWidth) * $newWidth;
    }

    public static function getDuration($filePath,$use_cbr_estimate=false): float|int
    {
        $fd = fopen($filePath, "rb");
 
        $duration=0;
        $block = fread($fd, 100);
        $offset = self::skipID3v2Tag($block);
        fseek($fd, $offset, SEEK_SET);
        while (!feof($fd))
        {
            $block = fread($fd, 10);
            if (strlen($block)<10) { break; }
            //looking for 1111 1111 111 (frame synchronization bits)
            else if ($block[0]=="\xff" && (ord($block[1])&0xe0) )
            {
                $info = self::parseFrameHeader(substr($block, 0, 4));
                if (empty($info['Framesize'])) { return $duration; } //some corrupt mp3 files
                fseek($fd, $info['Framesize']-10, SEEK_CUR);
                $duration += ( $info['Samples'] / $info['Sampling Rate'] );
            }
            else if (str_starts_with($block, 'TAG'))
            {
                fseek($fd, 128-10, SEEK_CUR);//skip over id3v1 tag size
            }
            else
            {
                fseek($fd, -9, SEEK_CUR);
            }
            if ($use_cbr_estimate && !empty($info))
            { 
                return self::estimateDuration($info['Bitrate'],$offset,$filePath);
            }
        }
        return $duration * 1000;
    }
 
    private static function estimateDuration($bitrate,$offset,$filePath): float
    {
        $kbps = ($bitrate*1000)/8;
        $datasize = filesize($filePath) - $offset;
        return round($datasize / $kbps);
    }
 
    private static function skipID3v2Tag(&$block): float|int
    {
        if (str_starts_with($block, "ID3"))
        {
            $id3v2_major_version = ord($block[3]);
            $id3v2_minor_version = ord($block[4]);
            $id3v2_flags = ord($block[5]);
            $flag_unsynchronisation  = $id3v2_flags & 0x80 ? 1 : 0;
            $flag_extended_header    = $id3v2_flags & 0x40 ? 1 : 0;
            $flag_experimental_ind   = $id3v2_flags & 0x20 ? 1 : 0;
            $flag_footer_present     = $id3v2_flags & 0x10 ? 1 : 0;
            $z0 = ord($block[6]);
            $z1 = ord($block[7]);
            $z2 = ord($block[8]);
            $z3 = ord($block[9]);
            if ( (($z0&0x80)==0) && (($z1&0x80)==0) && (($z2&0x80)==0) && (($z3&0x80)==0) )
            {
                $header_size = 10;
                $tag_size = (($z0&0x7f) * 2097152) + (($z1&0x7f) * 16384) + (($z2&0x7f) * 128) + ($z3&0x7f);
                $footer_size = $flag_footer_present ? 10 : 0;
                return $header_size + $tag_size + $footer_size;//bytes to skip
            }
        }
        return 0;
    }

    private  static function parseFrameHeader($fourbytes): array
    {
        static $versions = array(
            0x0=>'2.5',0x1=>'x',0x2=>'2',0x3=>'1', // x=>'reserved'
        );
        static $layers = array(
            0x0=>'x',0x1=>'3',0x2=>'2',0x3=>'1', // x=>'reserved'
        );
        static $bitrates = array(
            'V1L1'=>array(0,32,64,96,128,160,192,224,256,288,320,352,384,416,448),
            'V1L2'=>array(0,32,48,56, 64, 80, 96,112,128,160,192,224,256,320,384),
            'V1L3'=>array(0,32,40,48, 56, 64, 80, 96,112,128,160,192,224,256,320),
            'V2L1'=>array(0,32,48,56, 64, 80, 96,112,128,144,160,176,192,224,256),
            'V2L2'=>array(0, 8,16,24, 32, 40, 48, 56, 64, 80, 96,112,128,144,160),
            'V2L3'=>array(0, 8,16,24, 32, 40, 48, 56, 64, 80, 96,112,128,144,160),
        );
        static $sample_rates = array(
            '1'   => array(44100,48000,32000),
            '2'   => array(22050,24000,16000),
            '2.5' => array(11025,12000, 8000),
        );
        static $samples = array(
            1 => array( 1 => 384, 2 =>1152, 3 =>1152, ), //MPEGv1,     Layers 1,2,3
            2 => array( 1 => 384, 2 =>1152, 3 => 576, ), //MPEGv2/2.5, Layers 1,2,3
        );
        $b1=ord($fourbytes[1]);
        $b2=ord($fourbytes[2]);
        $b3=ord($fourbytes[3]);
 
        $version_bits = ($b1 & 0x18) >> 3;
        $version = $versions[$version_bits];
        $simple_version =  ($version=='2.5' ? 2 : $version);
 
        $layer_bits = ($b1 & 0x06) >> 1;
        $layer = $layers[$layer_bits];
 
        $protection_bit = ($b1 & 0x01);
        $bitrate_key = sprintf('V%dL%d', $simple_version , $layer);
        $bitrate_idx = ($b2 & 0xf0) >> 4;
        $bitrate = $bitrates[$bitrate_key][$bitrate_idx] ?? 0;
 
        $sample_rate_idx = ($b2 & 0x0c) >> 2;//0xc => b1100
        $sample_rate = isset($sample_rates[$version][$sample_rate_idx]) ? $sample_rates[$version][$sample_rate_idx] : 0;
        $padding_bit = ($b2 & 0x02) >> 1;
        $private_bit = ($b2 & 0x01);
        $channel_mode_bits = ($b3 & 0xc0) >> 6;
        $mode_extension_bits = ($b3 & 0x30) >> 4;
        $copyright_bit = ($b3 & 0x08) >> 3;
        $original_bit = ($b3 & 0x04) >> 2;
        $emphasis = ($b3 & 0x03);
 
        $info = array();
        $info['Version'] = $version;//MPEGVersion
        $info['Layer'] = $layer;
        //$info['Protection Bit'] = $protection_bit; //0=> protected by 2 byte CRC, 1=>not protected
        $info['Bitrate'] = $bitrate;
        $info['Sampling Rate'] = $sample_rate;
        //$info['Padding Bit'] = $padding_bit;
        //$info['Private Bit'] = $private_bit;
        //$info['Channel Mode'] = $channel_mode_bits;
        //$info['Mode Extension'] = $mode_extension_bits;
        //$info['Copyright'] = $copyright_bit;
        //$info['Original'] = $original_bit;
        //$info['Emphasis'] = $emphasis;
        $info['Framesize'] = self::framesize($layer, $bitrate, $sample_rate, $padding_bit);
        $info['Samples'] = $samples[$simple_version][$layer];
        return $info;
    }
 
    private static function framesize($layer, $bitrate,$sample_rate,$padding_bit): int
    {
        if ($layer==1)
            return intval(((12 * $bitrate*1000 /$sample_rate) + $padding_bit) * 4);
        else //layer 2, 3
            return intval(((144 * $bitrate*1000)/$sample_rate) + $padding_bit);
    }
}