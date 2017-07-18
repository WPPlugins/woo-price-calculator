<?php

namespace WSF\Helper;

use WSF\WSF;

class TableHelper {
        var $wsf;
        
        public function __construct(WSF $wsf) {
            $this->wsf = $wsf;
        }
        
        function render_list_page($listTable, $options){

          echo '</pre>'
            . '<div class="wrap"><h2>' .
                  $options['title'] 
            . '<a class="add-new-h2" href="' . $options['add_url'] . '">' .
                  $options['add_label'] .
            '</a>';

          if(isset($options['buttons'])){
            foreach($options['buttons'] as $button){
              echo  '<a class="add-new-h2" href="' . $button['url'] . '">' .
                      $button['label'] .
                    '</a>';
            }
          }
          
          echo '</h2>';
          
          if(isset($options['description'])){
              echo $options['description'];
          }
          
          $listTable->prepare_items(); 
        ?>
          <form method="post">
            <input type="hidden" name="page" value="ttest_list_table">
            <?php
            $listTable->search_box($this->wsf->trans('Search'), 'search_id' );

          $listTable->display(); 
          echo '</form></div>'; 
        }
}