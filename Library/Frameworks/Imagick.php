<?php

Class Imagick
{
    private $im = null;

    public function readImage($file)
    {
        $this->im = new Gd($file);

        return true;
    }
    
    public function cropThumbnailImage($w, $h)
    {
        $this->im->scale_fill($w, $h);
    }

    public function thumbnailImage($w, $h, $fit = false)
    {
        if ($fit) {
            $this->im->scale($w, $h);
        } else {
            if ($h) {
                $this->im->resize($w, $h);
            } else {
                $this->im->scale($w, $h);
            }
        }
    }

    public function getImageFormat()
    {
        return $this->im->type;
    }

    public function __toString()
    {
        ob_start();
        $this->im->output($this->im->type);

        return ob_get_clean();
    }
}

?>
