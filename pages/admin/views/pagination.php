<?php
function echo_pagination_view($query_link, $page, $entry_count) { ?>
    <div class="col-sm-12" id="pagination">
        <nav aria-label="...">
          <ul class="pagination">
            <li class="page-item <?php echo $page == 1 ? "disabled" : "";?>">
              <a class="page-link"  href='<?php echo "$query_link&page=1" ?>'><<</a>
            </li>
            <li class="page-item <?php echo $page == 1 ? "disabled" : "";?>">
              <a class="page-link"  href='<?php echo "$query_link&page=".($page-1) ?>'><</a>
            </li>
            <?php 
              $page_count = intval($entry_count/PAGE_SIZE_LIMIT) + 1;

              for($i= $page>5 ? $page-4 : 1; $i < $page+5 && $i<=$page_count; $i++){
              echo "<li class='page-item ".($i == $page ? "active" : "")."'><a class='page-link' href='$query_link&page=$i'>$i</a></li>";
              }
            ?>
            <li class=" <?php echo $page == $page_count ? "disabled" : "";?>">
              <a class="page-link"  href='<?php echo "$query_link&page=".($page+1) ?>'>></a>
            </li>
            <li class=" <?php echo $page == $page_count ? "disabled" : "";?>">
              <a class="page-link"  href='<?php echo "$query_link&page=".$page_count ?>'>>></a>
            </li>
          </ul>
        </nav>
    </div>
<?php }