<?php

class Pagination extends View
{
    private $page;
    private $total_count;
    private $query_link;

    public function __construct($page)
    {
        $this->page = $page;
        $params = $_GET;
        unset($params["page"]);
        $this->query_link = Utils::requestUrl() . "?" . http_build_query(array_filter($params));
    }

    public function setTotalCount($total_count)
    {
        $this->total_count = $total_count;
        return $this;
    }

    public function render()
    { ?>
        <div class="col-12 mt-4" id="pagination">
            <nav aria-label="...">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $this->page == 1 ? "disabled" : ""; ?>">
                        <a class="page-link" href='<?php echo "{$this->query_link}&page=1" ?>'>
                            <i class="fa fa-angle-double-left"></i>
                        </a>
                    </li> 
                    <li class="page-item <?php echo $this->page == 1 ? "disabled" : ""; ?>">
                        <a class="page-link" href='<?php echo "{$this->query_link}&page=" . ($this->page - 1) ?>'>
                        <i class="fa fa-angle-left"></i>
                        </a> 
                    </li> 
                    <?php
                        $page_count = intval($this->total_count / PAGE_SIZE_LIMIT) + 1;
                        for ($i = $this->page > 5 ? $this->page - 4 : 1; $i < $this->page + 5 && $i <= $page_count; $i++) {
                            echo "<li class='page-item " . ($i == $this->page ? "active" : "") . "'><a class='page-link' href='{$this->query_link}&page=$i'>$i</a></li>";
                        } ?> 
                    <li class="page-item <?php echo $this->page >= $page_count ? "disabled" : ""; ?>">
                        <a class="page-link" href='<?php echo "{$this->query_link}&page=" . ($this->page + 1) ?>'>
                        <i class="fa fa-angle-right"></i>
                        </a>
                    </li>
                    <li class="page-item <?php echo $this->page >= $page_count ? "disabled" : ""; ?>">
                        <a class="page-link" href='<?php echo "{$this->query_link}&page=" . $page_count ?>'>
                        <i class="fa fa-angle-double-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
<?php }
}
