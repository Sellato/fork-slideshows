<?php

namespace Frontend\Modules\Slideshows\Widgets;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */
use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Core\Engine\Theme as FrontendTheme;
use Frontend\Modules\Slideshows\Engine\Model as FrontendSlideshowsModel;

/**
 * This is a widget for a slideshow
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 */
class Detail extends FrontendBaseWidget
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
        $template = FrontendTheme::getPath(FRONTEND_MODULES_PATH . '/Slideshows/Layout/Widgets/Detail.tpl');
        $this->loadTemplate($template);
        $this->parse();
    }

    /**
     * Load the data
     */
    private function loadData()
    {
        $this->item = FrontendSlideshowsModel::get((int) $this->data['id']);
    }

    /**
     * Parse into template
     */
    private function parse()
    {
        // assign data
        $this->tpl->assign('slideshow', $this->item);
    }
}
