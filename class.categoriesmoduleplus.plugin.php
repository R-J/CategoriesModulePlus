<?php if (!defined('APPLICATION')) exit();

$PluginInfo['CategoriesModulePlus'] = array(
    'Name' => 'CategoriesModule+',
    'Description' => 'Extended version of the category module that lets you hide/unhide categories and switch from viewing only followed categories to all categories.',
    'Version' => '0.2',
    'MobileFriendly' => TRUE,
    'RequiredApplications' => array('Vanilla' => '2.1'),
    'Author' => 'Robin',
    'AuthorUrl' => 'http://vanillaforums.org/profile/44046/R_J',
    'License' => 'MIT'
);

class CategoriesModulePlusPlugin extends Gdn_Plugin {
    public function Setup() {
        SaveToConfig('Vanilla.Categories.HideModule', TRUE);
    }
    public function OnDisable() {
        SaveToConfig('Vanilla.Categories.HideModule', FALSE);
    }
    
    // attach module to some controllers
    public function CategoriesController_Render_Before($Sender) {
        $this->AttachModule($Sender);
    }
    public function DiscussionController_Render_Before($Sender) {
        $this->AttachModule($Sender);
    }
    public function DiscussionsController_Render_Before($Sender) {
        $this->AttachModule($Sender);
    }
    public function DraftsController_Render_Before($Sender) {
        $this->AttachModule($Sender);
    }

    /**
     * Main function that 
     *   1. adds CSS file to controller
     *   2. adds new categories module
     */
    private function AttachModule($Sender) {
        if (IsMobile()) {
            return;
        } else {
            include_once(PATH_PLUGINS.DS.'CategoriesModulePlus'.DS.'modules'.DS.'class.categoriesmoduleplusmodule.php');
            // ApplicationFolder is needed for rendering the view
            $ApplicationFolder = 'plugins/CategoriesModulePlus';
            $Sender->AddCssFile('categoriesmoduleplus.css', $ApplicationFolder);
            $CategoriesModulePlusModule = new CategoriesModulePlusModule($Sender, $ApplicationFolder);
            // add module to the panel
            $Sender->AddModule($CategoriesModulePlusModule);
        }
    }

    /**
     * SetToggle toggles the filtered/unfiltered category view
     * 
     * The function has been adopted from 
     * /applications/vanilla/modules/class.categoryfollowmodule.php
     * Only Redirect part had to be altered
     */
    public function CategoriesController_SetToggle_Create($ShowAllCategories = '', $Target = '') {
        $Session = Gdn::Session();
        if (!$Session->IsValid())
            return;
      
        if ($ShowAllCategories != '') {
            $ShowAllCategories = $ShowAllCategories == 'true' ? TRUE : FALSE;
            $ShowAllCategoriesPref = $Session->GetPreference('ShowAllCategories');
            if ($ShowAllCategories != $ShowAllCategoriesPref) {
                $Session->SetPreference('ShowAllCategories', $ShowAllCategories);
            }
            
            Redirect($Target);
        }
    }

    /**
     * ToggleFollow toggles the hidden/unhidden state of categories
     *
     * it has been adopted from function Follow in
     * /applications/vanilla/controllers/class.categorycontroller.php
     * Redirection had to be fixed
     */
    public function CategoryController_ToggleFollow_Create($CategoryID, $Value, $TKey, $Target) {
        if (Gdn::Session()->ValidateTransientKey($TKey)) {
            $CategoryModel = new CategoryModel();
            $CategoryModel->SaveUserTree($CategoryID, array('Unfollow' => !(bool)$Value));
            
            if(!(bool)$Value) {
                // switch to "view only followed" when hiding a category
                $this->CategoriesController_SetToggle_Create(1, $Target);
            } else {
                // and to "view all" when no category is hidden
                $NoFollowing = TRUE;
                $Categories = $CategoryModel::Categories();
                foreach ($Categories as $Category) {
                    if ($Category['Following'] == '') {
                        $NoFollowing = FALSE;
                        break;
                    }
                }
                if ($NoFollowing) {
                    $this->CategoriesController_SetToggle_Create(0, $Target);
                }
            }
        }
        Redirect($Target);
    }

    public function Gdn_PluginManager_CategoryWatch_Handler($Sender) {
        if(Gdn::Session()->GetPreference('ShowAllCategories')) {
            $CategoryModel = new CategoryModel();
            $Categories = $CategoryModel::Categories();
            $AllCount = count($Categories);
            $Watch = array();
            
            foreach ($Categories as $CategoryID => $Category) {
                if ($Category['PermsDiscussionsView'] && !GetValue('HideAllDiscussions', $Category)) {
                    $Watch[] = $CategoryID;
                }
            }
            $Sender->EventArguments['CategoryIDs'] = $Watch;
        }
    }
}
