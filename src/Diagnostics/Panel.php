<?php

namespace FreezyBee\MailChimp\Diagnostics;

use FreezyBee\MailChimp\Api;
use FreezyBee\MailChimp\Http\Resource;
use Nette\SmartObject;
use Tracy\Debugger;
use Tracy\IBarPanel;

/**
 * Class Panel
 * @package FreezyBee\MailChimp\Diagnostics
 */
class Panel implements IBarPanel
{
    use SmartObject;

    /**
     * @var Resource[]
     */
    private $resources = [];

    /**
     * Renders HTML code for custom tab.
     * @return string
     */
    public function getTab()
    {
        $output = '<span><img width="16px" height="16px" style="float: none" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAB41BMVEUAAAAAAAEAAQAAAwYBAQICAgEDAwMTEhL///8GBwsAAAAAAAAAAAAAAAAEBAQAAAAAAAAAAAEAAAAAAAAdEAAAAAAAAAYDCxEAAAAAAAAHAwEEBgkfHx8XGBcNCQYMCgYCAAAAAAwDAAAHBxAEAAAGAQACAwMSCwUFEx9WRDIHEiJMPCoLGDVuZ1hhUUEWCgMUCgJ2cWcbHDKHfmx8cFxZQzBgSyMgEAgbRWcJEh+bmpYxGQg4GAsAAAAHDiwJBQINCQoNOXgPLXgPXKMUGDAWAAAWFxgcMnEeDgkfLl4jAgAlR4UmgMYoEgQsBQAtEAEvEQMvMkkvdbU0KiY4EQA5DQA9QEM/FABAFwZCHApEGgZEHwlEKjRHGARJFwBJIg9KJBJLXlpOKg1WqeJXLBFZJgpdJwhgLRRkNRRoLA1wTzFzOBaLX0aMTR6NWzuVSBGZprimhGaseVOteUyzf1jDlHPDmHDGi1vLsXPNoHfZonLZ6fHe1svfsonfzrvhtobjpW7nv5bopmzpxpvq6+Xr5+Psu4/vz6bv0K7w7t/40KT63rr64MH73rz87tz9zJf+ypP+7dL/2KP/47j/5bz/5rT/6Mb/6cH/78//8cv/89T/9t3/+9X//9T//+P///j///+41lW2AAAAPXRSTlMAAAAAAAAAAAABAwsQGBsfJi41OEBBRElKUVRYWV9gYXZ3eYCHj5GRlJ6jqKmytcXG1Nba3OLk5ejq9PX5j3TJCwAAANdJREFUeAFj4GAAAhYmMUUuVnY2BiAXLMAgbpEowinDBxfQ8EmuMLN0U4MJaDkHFYQ52NtKQAWUvR0Dg20jMgwYIALCnu4uHn45efnFsmABbh0nO9+40pldtXN1GRiBAoJGMdEh6e0TqqZP1gSrUApvKvKKLekv65hhzAsSUAhtK3SNym6cM3H2fBNRkKFSNgkBqZWzerp75xkKgwSYpf0jM+s7q1taF1jJQxymH59WPqWupm+angBEgN88Jbdh0tRmbbhfeNStk7JM5aAuBQNJVRUhEM0BAAcaM5lzJgfkAAAAAElFTkSuQmCC"> ';

        if ($this->resources) {
            $totalTime = 0;
            foreach ($this->resources as $resource) {
                $totalTime += $resource->getTime();
            }
            $output .= count($this->resources) . ' call' . (count($this->resources) > 1 ? 's' : '') .
                ' / ' . sprintf('%0.2f', $totalTime) . ' s';

        } else {
            $output .= 'Mailchimp';
        }

        return $output;
    }

    /**
     * Renders HTML code for custom panel.
     * @return string
     */
    public function getPanel()
    {
        if (!$this->resources) {
            return null;
        }

        $esc = \Nette\Utils\Callback::closure('Latte\Runtime\Filters::escapeHtml');
        $click = function ($o, $c = false) {
            return \Tracy\Dumper::toHtml($o, ['collapse' => $c]);
        };

        ob_start();
        include __DIR__ . '/panel.phtml';
        return ob_get_clean();
    }

    /**
     * @param Api $api
     */
    public function register(Api $api)
    {
        $api->onResponse[] = function (Resource $resource) {
            $this->resources[] = $resource;
        };

        Debugger::getBar()->addPanel($this);
    }
}
