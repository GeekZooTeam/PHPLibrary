<?php

/*
 * This file is part of the Geek-Zoo Projects.
 *
 * @copyright (c) 2010 Geek-Zoo Projects More info http://www.geek-zoo.com
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License
 * @author xuanyan <xuanyan@geek-zoo.com>
 *
 */

class Pager
{
    private $total =  0;
    private $page = null;
    private $per_page = 1;
    private $pages = 1;
    private $link = '';

    private function getDefaultPage($page = null)
    {
        if ($page === null) {
            $page = intval(@$_GET['page_no']);
            $page < 1 && $page = 1;
        }

        return $page;
    }

    public function __construct($per_page)
    {
        $this->per_page = $per_page;
    }

    public function getLimit($page = null)
    {
        if ($this->page === null) {
            $page = $this->getDefaultPage($page);
            return 'LIMIT '.($page - 1) * $this->per_page.', '.($this->per_page + 1);
        }

        return 'LIMIT '.($this->page - 1) * $this->per_page.', '.$this->per_page;
    }

    public function setPage($page = null)
    {
        $this->page = $this->getDefaultPage($page);
        
        return $this;
    }

    public function generate(&$total, &$page = null, $link = '')
    {
        $page = $this->getDefaultPage($page);

        $this->link = $link;
        if (empty($this->link) && !empty($_SERVER['REQUEST_URI'])) {// 自动处理分页链接
            $data = parse_url($_SERVER['REQUEST_URI']);
            if (isset($data['query'])) {
                parse_str($data['query'], $data['query']);
                $data['query']['page_no'] = '__PAGE__';
                $data['query'] = http_build_query($data['query']);
                $this->link = $data['path'].'?'.$data['query'];
            } else {
                $this->link = $data['path'].'?page_no=__PAGE__';
            }
        }

        if (is_array($total)) {
            $this->total = count($total);
            if ($this->total > $this->per_page) {
                array_pop($total);
                $this->pages = $page + 1;
            } else {
                $this->pages = $page;
            }
        } else {
            $this->total = $total;
            $this->pages = ceil($this->total / $this->per_page);
            $this->pages < 1 && $this->pages = 1;
        }

        $page > $this->pages && $page = $this->pages;
        $this->page = $page;
        
        return $this->pages > 1;
    }

    public function getPagesArray($num = null)
    {
        if ($num === null) {
            return range(1, $this->pages);
        }
        $per = floor($num / 2);
        $min = $this->page - $per;

        if ($num % 2) {
            $max = $this->page + ceil($num / 2) - 1;
        } else {
            $max = $this->page + $per - 1;
        }

        if ($max > $this->pages) {
            $min -= $max - $this->pages;
            $max = $this->pages;
        } elseif ($min < 1) {
            $max += 1 - $min;
            $min = 1;
        }

        $max > $this->pages && $max = $this->pages;
        $min < 1 && $min = 1;

        return range($min, $max);
    }

    public function link($page)
    {
        return str_replace("__PAGE__", $page, $this->link);
    }

    public function next()
    {
        return ($this->page + 1) > $this->pages ? $this->pages : ($this->page + 1);
    }

    public function prev()
    {
        return ($this->page - 1) > 0 ? ($this->page - 1) : 1;
    }

    public function begin()
    {
        return 1;
    }

    public function getNum()
    {
        if ($this->page < $this->pages) {
            return $this->per_page;
        } else {
            return $this->total % $this->per_page;
        }
    }

    public function end()
    {
        return $this->pages;
    }

    public function getPage()
    {
        return $this->page;
    }


    public function getPages()
    {
        return $this->pages;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function getPerPage()
    {
        return $this->per_page;
    }
}

?>