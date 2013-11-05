<?php

/**
 * This is the detail widget.
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class FrontendSlideshowsWidgetDetail extends FrontendBaseWidget
{
    /**
     * The item.
     *
     * @var	array
     */
    private $item;

    /**
     * Execute the extra
     */
    public function execute()
    {
        parent::execute();
        $this->loadData();
        $template = FrontendTheme::getPath(FRONTEND_MODULES_PATH . '/slideshows/layout/widgets/detail.tpl');
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
        $this->tpl->assign('widgetSlideshow', $this->item);
    }
}
