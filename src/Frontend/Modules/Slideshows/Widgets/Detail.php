<?php

namespace Frontend\Modules\Slideshows\Widgets;

use Frontend\Core\Engine\Base\Widget;
use Frontend\Core\Engine\Theme;
use Frontend\Modules\Slideshows\Engine\Model;

/**
 * This is a widget for a slideshow
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 */
class Detail extends Widget
{
    /**
     * The item.
     *
     * @var    array
     */
    private $item;

    /**
     * Execute the extra
     */
    public function execute()
    {
        parent::execute();

        $this->loadData();
        $template = Theme::getPath($this->getSlideShowTemplate());
        $this->loadTemplate($template);
        $this->parse();
    }

    /**
     * Load the data
     */
    private function loadData()
    {
        $this->item = Model::get((int) $this->data['id']);
    }

    /**
     * Parse into template
     */
    private function parse()
    {
        // assign data
        $this->tpl->assign('slideshow', $this->item);
    }

    /**
     * @return string
     */
    private function getSlideShowTemplate()
    {
        if (Theme::getTheme() != 'code') {
            return FRONTEND_PATH . '/Themes/' . Theme::getTheme() . '/Modules/Slideshows/Layout/Widgets/' . $this->item['template'];
        }

        return FRONTEND_MODULES_PATH . '/Slideshows/Layout/Widgets/' . $this->item['template'];
    }
}
