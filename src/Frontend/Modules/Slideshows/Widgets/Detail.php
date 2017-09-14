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
    public function execute(): void
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
    private function loadData(): void
    {
        $this->item = Model::get((int) $this->data['id']);
    }

    /**
     * Parse into template
     */
    private function parse(): void
    {
        // assign data
        $this->template->assign('slideshow', $this->item);
    }

    private function getSlideShowTemplate(): string
    {
        // If custom theme
        $filepath = FRONTEND_PATH . '/Themes/' . Theme::getTheme() . '/Modules/' . $this->getModule() . '/Layout/Widgets/' . $this->item['template'];
        if (file_exists($filepath)) {
            return $filepath;
        }

        // If default theme has template
        $filepath = FRONTEND_MODULES_PATH . '/' . $this->getModule() . '/Layout/Widgets/' . $this->item['template'];
        if (file_exists($filepath)) {
            return $filepath;
        }

        // Use default template
        return FRONTEND_MODULES_PATH . '/' . $this->getModule() . '/Layout/Widgets/Detail.html.twig';
    }
}
