<?php if (!defined('APPLICATION')) exit();
/**
 * Original view is /applications/vanilla/views/modules/categoriesmodule.php
 * Has been altered
 *  1. code in view has been transfered to module
 *  2. additional html for  menus was inserted
 */
 
if ($this->Data['Categories'] !== FALSE) {
    $CountDiscussions = 0;
    $UnhideMenu = '';
    foreach ($this->Data['Categories']->Result() as $Category) {
        if (!$Category->Following) {
            $LinkText = sprintf(T('Unhide %s'), $Category->Name);
            $LinkTarget = Gdn::Request()->Url('category/togglefollow?categoryid='.$Category->CategoryID.'&value=1&tkey='.urlencode($this->Data['TKey']).'&target='.$this->Data['Url']);
            $UnhideMenu .= '<li><a href="'.$LinkTarget.'">'.$LinkText.'</a></li>';
            if ($this->Data['ShowAllCategoriesPref']) {
                $CountDiscussions = $CountDiscussions + $Category->CountDiscussions;
            }
        } else {
            $CountDiscussions = $CountDiscussions + $Category->CountDiscussions;
        }
    }
?>
<div class="Box BoxCategories">
    <h4 class="Item"><?php echo T('Categories'); 
    if ($this->Data['ValidSession']) {
    ?>
        <span class="Options">
            <span class="ToggleFlyout OptionsMenu">
                <span title="Optionen" class="OptionsTitle">Optionen</span>
                <span class="SpFlyoutHandle"></span>
                <ul class="Flyout MenuItems">
                    <li>
<?php if ($this->Data['ShowAllCategoriesPref']) { ?>
    <a href="<?php echo $this->Data['ShowFollowedCategoriesUrl']; ?>"><?php echo T('View only followed categories'); ?></a>
<?php } else { ?>
    <a href="<?php echo $this->Data['ShowAllCategoriesUrl']; ?>"><?php echo T('View all categories'); ?></a>
<?php } 
    // show hidden categories including option to unhide them
    if ($UnhideMenu != '') {
        echo '<hr/'.$UnhideMenu;
    }
?>
                    </li>
                </ul>
            </span>
        </span>
<?php
   }
?>      
    </h4>
    <ul class="PanelInfo PanelCategories">
<?php
        echo '<li'.($this->Data['OnCategories'] ? ' class="Active"' : '').'>'.Anchor(T('All Categories').' <span class="Aside"><span class="Count">'.BigPlural($CountDiscussions, '%s discussion').'</span></span>', '/categories', 'ItemLink').'</li>';

        foreach ($this->Data['Categories']->Result() as $Category) {
            if (($this->Data['ShowAllCategoriesPref'] != 1 && !$Category->Following) || $Category->CategoryID < 0 || $this->Data['MaxDepth'] > 0 && $Category->Depth > $this->Data['MaxDepth']) {
                continue;
            }
            if ($this->Data['DoHeadings'] && $Category->Depth == 1) {
                $CssClass = 'Heading '.$Category->CssClass;
            } else {
                $CssClass = 'Depth'.$Category->Depth.($this->Data['CategoryID'] == $Category->CategoryID ? ' Active' : '').' '.$Category->CssClass;
            }
            echo '<li class="Item ClearFix '.$CssClass.'">';

            if ($this->Data['DoHeadings'] && $Category->Depth == 1) {
                echo htmlspecialchars($Category->Name).' <span class="Aside"><span class="Count Hidden">'.BigPlural($Category->CountAllDiscussions, '%s discussion').'</span></span>';
            } else {
                $CountText = ' <span class="Aside"><span class="Count">'.BigPlural($Category->CountAllDiscussions, '%s discussion').'</span></span>';
                echo Anchor(htmlspecialchars($Category->Name).$CountText, CategoryUrl($Category), 'ItemLink');
            }
            if ($this->Data['ValidSession']) {
?>
        <span class="Options">
            <span class="ToggleFlyout OptionsMenu">
                <span title="Optionen" class="OptionsTitle">Optionen</span>
                <span class="SpFlyoutHandle"></span>
                <ul class="Flyout MenuItems">
                    <li>
<?php if (!$Category->Following) { ?>
    <a href="<?php echo Gdn::Request()->Url('category/togglefollow?categoryid='.$Category->CategoryID.'&value=1&tkey='.urlencode($this->Data['TKey']).'&target='.$this->Data['Url']); ?>"><?php echo T('Unhide'); ?></a>
<?php } else { ?>
    <a href="<?php echo Gdn::Request()->Url('category/togglefollow?categoryid='.$Category->CategoryID.'&value=0&tkey='.urlencode($this->Data['TKey']).'&target='.$this->Data['Url']); ?>"><?php echo T('Hide'); ?></a>
<?php }  ?>
                    </li>
                </ul>
            </span>
        </span>
<?php
            }
        echo "</li>\n";
    }
?>
    </ul>
</div>
   <?php
}