<?php
namespace UWTest;

class AppTwigFilters extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_Filter('links', [$this, 'links'], ['is_safe' => ['all']]),
        ];
    }

    public function links($value)
    {
        $value = htmlspecialchars($value);
        // links
        preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $value, $match);
        if (!empty($match[0])) {
            foreach ($match[0] as $url) {
                if (strstr($url, 'youtube') !== false && $title = $title = $this->getYoutubeVideoTitle($url)) {
                    $value = str_replace(
                        $url,
                        sprintf(
                            "<a href='%s' target='_blank'><span class='glyphicon glyphicon-film'></span> %s</a>",
                            $url,
                            $title
                        ),
                        $value
                    );
                } else {
                    $value = str_replace($url, "<a href='" . $url . "' target='_blank'>" . $url . "</a>", $value);
                }
            }
        }
        // uploads
        preg_match_all('#\/upload/[0-9]{4}-[0-9]{2}-[0-9]{2}/[0-9a-z]*\.(jpg|png|jpe|jpeg)#', $value, $match);
        if (!empty($match[0])) {
            foreach ($match[0] as $img) {
                $value = str_replace($img, "<img src='" . $img . "' class='img-thumbnail'>", $value);
            }
        }

        return $value;
    }

    private function getYoutubeVideoTitle($url)
    {
        $result = '';
        if (strstr($url, 'youtube') !== false) {
            $url = explode("?", $url);
            $video_id = '';
            foreach ($url as $key => $part) {
                $url[$key] = explode("=", $part);
                if ($url[$key][0] == 'v') {
                    $video_id = $url[$key][1];
                    break;
                }
            }
            if ($video_id) {
                $response = file_get_contents('http://www.youtube.com/oembed?url=http://www.youtube.com/watch?v=' . $video_id . '&format=json');
                if ($response && $response = json_decode($response, true)) {
                    $result = $response['title'];
                }
            }
        }

        return $result;
    }
}