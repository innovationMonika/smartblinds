<?php

namespace Smartblinds\PreloadLinks\Model;

class Resources
{
    private $data = [];

    public function add(string $url, string $as, ?int $minWidth = null, ?int $maxWidth = null)
    {
        $data = ['rel' => 'preload'];
        if ($minWidth || $maxWidth) {
            $mediaParts = ['screen and'];
            if ($minWidth) {
                $mediaParts[] = "(min-width: {$minWidth}px)";
            }
            if ($minWidth && $maxWidth) {
                $mediaParts[] = 'and';
            }
            if ($maxWidth) {
                $mediaParts[] = "(max-width: {$maxWidth}px)";
            }
            $media = implode(' ', $mediaParts);
            $data['media'] = $media;
        }
        $data['as'] = $as;
        $data['href'] = $url;
        $this->data[] = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
