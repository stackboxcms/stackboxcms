<?php
$currentLevel = $view::$_level;

echo $beforeItemSetCallback();

if(!$levelMax || $currentLevel <= $levelMax):
  if(isset($itemData)):
    foreach($itemData as $item):

      if(!$levelMin || $currentLevel >= $levelMin):
        // Item before
        echo $beforeItemCallback($item);

        // Item content
        echo $itemCallback($item);
      endif;

        // Item children (hierarchy)
        if(isset($itemChildrenCallback)):
          $children = $itemChildrenCallback($item);
          if($children):
            // Increment current level
            $view::$_level++;

            // Display treeview recursively
            $sub = clone $view;
            $sub->data($children);
            echo $sub->content();

            // Reset level for remaining items
            $view::$_level = $currentLevel;
          endif;
        endif;


      if(!$levelMin || $currentLevel >= $levelMin):
        // Item after
        echo $afterItemCallback($item);
      endif;
    endforeach;
  
  // noData display
  elseif(isset($noDataCallback)):
    $noDataCallback();
  endif;
endif;

echo $afterItemSetCallback();
