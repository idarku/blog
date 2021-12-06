<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<?php
    $navbarSize = Mirages::$options->navbarSize;
    $navbarSizeClass = '';
    if ($navbarSize === 'md') {
        $navbarSizeClass = ' navbar-md';
    } elseif ($navbarSize === 'sm') {
        $navbarSizeClass = ' navbar-sm';
    } elseif ($navbarSize === 'lg') {
        $navbarSizeClass = ' navbar-lg';
    } else {
        $navbarSizeClass = ' navbar-md';
    }
?>
<!-- Fixed navbar -->
<nav id="navbar" class="navbar navbar-expand-md navbar-color<?php echo $navbarSizeClass;?> fixed-top no-user-select">
    <div class="container-fluid">
        <?php
            $showHomeItem = false;
            $imageLogo = false;
            $logo = Mirages::$options->navbarLogo;
            if (!Utils::hasValue($logo)) {
                $logo = _mt("首页");
            } else {
                if (Utils::startsWith(trim($logo), 'http://') || Utils::startsWith(trim($logo), 'https://')) {
                    $imageLogo = true;
                    $logo = '<img src="' . $logo . '" alt="Logo" height="40"/>';
                }
            }
            $logoUrl = Mirages::$options->rootUrl;
            if (Mirages::$options->navbarLogoUrl__hasValue) {
                $logoUrl = Mirages::$options->navbarLogoUrl;
                $showHomeItem = true;
            }
        ?>
        <a class="navbar-brand <?php echo $imageLogo ? '' : 'text-brand'?>" href="<?php echo $logoUrl?>"><?php echo $logo ?></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav mr-auto">
                <?php if ($showHomeItem):?>
                <li class="nav-item"><a class="nav-link" href="<?php Mirages::$options->rootUrl()?>"><?php _me('首页')?></a></li>
                <?php endif;?>
                <?php if (Mirages::$options->expandTopCategoryInNavbar__isTrue):?>
                    <?php $this->widget('Mirages_Widget_Metas_Category_List')->listCategories(); ?>
                <?php else:?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="dashboard-dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php _me('分类')?>
                    </a>
                    <?php $this->destory('Widget_Metas_Category_List');?>
                    <?php $this->widget('Widget_Metas_Category_List')->listCategories('wrapClass=dropdown-menu&itemClass=dropdown-item'); ?>
                </li>
                <?php endif;?>
                <?php
                    $topCategorySize = Mirages::$options->lastTopCategorySize;
                    if ($topCategorySize === NULL) {
                        $topCategorySize = 1;
                    }
                    $this->widget('Widget_Contents_Page_List')->to($pages);
                    $moreList = array();
                    $menuIndex = $topCategorySize + 1;
                    $maxMenuNum = intval(Mirages::$options->maxNavbarMenuNum) > 0 ? intval(Mirages::$options->maxNavbarMenuNum) : 7;
                ?>
                <?php
                    $toolbarItems = Content::loadToolbarItems();
                    $hideNightShiftBarItem = false;
                    $toolbarItemsOutput = "";
                    $showRSSInToolBar = false;
                    foreach ($toolbarItems as $item) {
                        $itemIcon = $item[0];
                        $itemName = $item[1];
                        $itemLink = $item[2];
                        if (strtoupper($itemIcon) === 'READ-SETTINGS' && strtoupper($itemLink) === 'HIDE') {
                            $hideNightShiftBarItem = true;
                            Mirages::$options->hideReadSettings = 1;
                            continue;
                        }
                        if (strtoupper($itemIcon) === 'RSS' && strtoupper($itemLink) === 'HIDE') {
                            $showRSSInToolBar = true;
                            continue;
                        }
                        if (strtoupper($itemIcon) === 'RSS' && strtoupper($itemLink) === 'SHOW') {
                            $toolbarItemsOutput .= '<li><a id="nav-side-toolbar-rss" href="' . Mirages::$options->rootUrl. '/' . REWRITE_FIX . 'feed' . '" title="'. _mt('RSS 订阅') .'" target="_blank"><i class="fa fa-rss"></i></a></li>';
                            $showRSSInToolBar = true;
                            continue;
                        }
                        $toolbarItemsOutput .= '<li><a id="nav-side-toolbar-'. $itemIcon .'" href="'. $itemLink .'" title="'. _mt($itemName) .'" target="_blank"><i class="fa fa-'. $itemIcon .'"></i></a></li>';
                    }
                ?>
                <?php while($pages->next()): ?>
                    <?php
                        $menuIndex++;
                        $inMore = Utils::isTrue($pages->fields->menuInMore);
                        if ($inMore) {
                            $menuIndex--;
                        }
                        if ($inMore || $menuIndex >= $maxMenuNum) {
                            $menu = array(
                                    'slug' => $pages->slug,
                                    'permalink' => $pages->permalink,
                                    'title' => _mt($pages->title),
                            );
                            $moreList[] = $menu;
                            continue;
                        }
                    ?>
                    <li class="nav-item"><a class="nav-link<?php if($this->is('page', $pages->slug)): ?> current<?php endif; ?>" href="<?php $pages->permalink(); ?>" title="<?php _me($pages->title) ?>"><?php _me($pages->title) ?></a></li>
                <?php endwhile; ?>
                <?php 
                    if (count($moreList) > 1) {
                        $tagMore = _mt('更多');
                        echo <<<HTML
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="more-menu-dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">${tagMore}</a>
    <ul class="dropdown-menu" aria-labelledby="more-menu-dropdown">
        
HTML;
                        foreach ($moreList as $menu) {
                            echo '<li class="dropdown-item"><a href="' . $menu['permalink'] . '" title="' . $menu['title'] . '">' . $menu['title'] . '</a></li>';
                        }
                        if (!$showRSSInToolBar) {
                            echo '<li class="dropdown-divider"></li>';
                            echo '<li class="dropdown-item"><a href="' . Mirages::$options->rootUrl. '/' . REWRITE_FIX . 'feed' . '" title="' . _mt('RSS 订阅') . '">' . _mt('RSS 订阅') . '</a></li>';
                        }
                        echo "</ul></li>";
                    } elseif (count($moreList) == 1) {
                        echo '<li class="nav-item"><a class="nav-link' . (($this->is('page', $menu['slug'])) ? 'current' : '') . '" href="' . $menu['permalink'] . '" title="' . $menu['title'] . '">' . $menu['title'] . '</a></li>';
                    }
                ?>
            </ul>


            <ul class="navbar-nav side-toolbar-list">
                <li class="navbar-search-container">
                    <a id="navbar-search" class="search-form-input" href="javascript:void(0);" title="<?php _me("搜索...") ?>"><i class="fa fa-search"></i></a>
                    <form class="search-form" action="<?php Mirages::$options->rootUrl()?>/<?php echo REWRITE_FIX ?>" role="search">
                        <input type="text" name="s" required placeholder="<?php _me('搜索...')?>" class="search">
                    </form>
                </li>
                <?php echo $toolbarItemsOutput?>
                <?php if (!$hideNightShiftBarItem):?>
                    <li>
                        <a id="nav-side-toolbar-read-settings"  href="javascript:void(0);" title="<?php _me("阅读设置") ?>"><i class="fa fa-font"></i></a>
                        <div class="read-settings-container animated">
                            <div class="read-settings animated">
                                <div class="font-size-controls animated">
                                    <button type="button" class="font-size-control control-btn-smaller waves-effect waves-button" data-mode="smaller" title="<?php _me("减小字体字号")?>">A</button>
                                    <button type="button" class="font-size-display" disabled>100%</button>
                                    <button type="button" class="font-size-control control-btn-larger waves-effect waves-button" data-mode="larger" title="<?php _me("增大字体字号")?>">A</button>
                                </div>
                                <div class="background-color-controls animated">
                                    <ul>
                                        <li><a href="javascript:void(0)" title="<?php _me("自动模式")?>" class="background-color-control auto <?php echo NIGHT_SHIFT_BTN_CLASS == "auto-mode" ? "selected" : ""?>" data-mode="auto"><i class="fa fa-adjust"></i></a></li>
                                        <li><a href="javascript:void(0)" title="<?php _me("日间模式")?>" class="background-color-control white <?php echo NIGHT_SHIFT_BTN_CLASS == "day-mode" ? "selected" : ""?>" data-mode="white"><i class="fa fa-check-circle"></i></a></li>
                                        <li><a href="javascript:void(0)" title="<?php _me("日落模式")?>" class="background-color-control sunset <?php echo NIGHT_SHIFT_BTN_CLASS == "sunset-mode" ? "selected" : ""?>" data-mode="sunset"><i class="fa fa-check-circle"></i></a></li>
                                        <li><a href="javascript:void(0)" title="<?php _me("夜间模式")?>" class="background-color-control dark <?php echo NIGHT_SHIFT_BTN_CLASS == "night-mode" ? "selected" : ""?>" data-mode="dark"><i class="fa fa-check-circle"></i></a></li>
                                    </ul>
                                </div>
                                <div class="font-family-controls">
                                    <button type="button" class="font-family-control <?php echo USE_SERIF_FONTS ? "selected" : ""?> control-btn-serif" data-mode="serif"><?php _me('宋体') ?></button>
                                    <button type="button" class="font-family-control <?php echo USE_SERIF_FONTS ? "" : "selected"?> control-btn-sans-serif" data-mode="sans-serif"><?php _me('黑体') ?></button>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php endif;?>
            </ul>
        </div>
    </div>
</nav>