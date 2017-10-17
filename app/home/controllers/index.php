<?php
class IndexController Extends Module_App_Controller
{

    public function init()
    {
        parent::init();
        $this->LoadConfig();
        $this->LoadGlobalConfig('global');
    }
                 
    public function indexAction()
    {
        $this->addCssHtml('main');
        $this->view->title = $this->config['home']['title'].' Framework';
        $this->view->bodyText = $this->config['global']['version'];
    }
}
?>
