<?php if (!defined('APPLICATION')) exit();

/**
 * Class CategoriesModulePlus is an extended version of
 * /applications/vanilla/modules/categoriesmodule.php
 * It adds context menues to the module where you can toggle categories hidden status
 */
class CategoriesModulePlusModule extends Gdn_Module  {
    public function __construct($Sender = '', $ApplicationFolder = '') {
        $this->Data = FALSE;
        $NoFollowing = TRUE;
        if (C('Vanilla.Categories.Use') == TRUE) {
            // Load categories with respect to view permissions
            $Categories = CategoryModel::Categories();
            $Categories2 = $Categories;
            foreach ($Categories2 as $i => $Category) {
                if (!$Category['PermsDiscussionsView']) {
                    unset($Categories[$i]);
                } elseif (!$Category['Following']) {
                    $NoFollowing = FALSE;
                }
            }

            // delete view filter if no categories are hidden 
            $Session = Gdn::Session();
            if ($NoFollowing && $Session->IsValid()) {
                $Session->SetPreference('ShowAllCategories', TRUE);
            }

            // set categories to data
            $Data = new Gdn_DataSet($Categories);
            $Data->DatasetType(DATASET_TYPE_ARRAY);
            $Data->DatasetType(DATASET_TYPE_OBJECT);
            $this->Data['Categories'] = $Data;

            // calculate additional needed data 
            $this->Data['CategoryID'] = isset($Sender->CategoryID) ? $Sender->CategoryID : '';
            $this->Data['OnCategories'] = strtolower($Sender->ControllerName) == 'categoriescontroller' && !is_numeric($this->Data['CategoryID']);

            $this->Data['ShowAllCategoriesPref'] = $Session->GetPreference('ShowAllCategories');

            $this->Data['Url'] = Gdn::Request()->Path();
            if ($this->Data['Url'] == '') {
                $this->Data['Url'] = '/';
            }            
            $this->Data['ShowAllCategoriesUrl'] = Gdn::Request()->Url('categories/settoggle?ShowAllCategories=true&Target='.$this->Data['Url']);
            $this->Data['ShowFollowedCategoriesUrl'] = Gdn::Request()->Url('categories/settoggle?ShowAllCategories=false&Target='.$this->Data['Url']);

            $this->Data['TKey'] = urlencode(Gdn::Session()->TransientKey());
            $this->Data['ValidSession'] = ($Session->UserID > 0 && $Session->ValidateTransientKey($this->Data['TKey']));

            $this->Data['MaxDepth'] = C('Vanilla.Categories.MaxDisplayDepth');
            $this->Data['DoHeadings'] = C('Vanilla.Categories.DoHeadings');
        }
        parent::__construct($Sender, $ApplicationFolder);
    }

    public function AssetTarget() {
        return 'Panel';
    }

    public function ToString() {
        if ($this->Data['Categories']) {
            return $this->FetchView();
        }
        return '';
    }
}
