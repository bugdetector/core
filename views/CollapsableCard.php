<?php

class CollapsableCard extends View
{
    private $title;
    private $content;
    private $id;
    public function __construct($title)
    {
        $this->title = $title;
    }

    public function setContent($content){
        $this->content = $content;
        return $this;
    }

    public function setId($id){
        $this->id = $id;
        return $this;
    }

    public function render()
    { ?>
        <div class="card shadow mb-4 p-0">
            <!-- Card Header - Accordion -->
            <a href="#<?php echo $this->id; ?>" class="d-block card-header py-3 collapsed <?php echo $this->renderClasses(); ?>" data-toggle="collapse" role="button" aria-expanded="true">
                <h6 class="m-0 font-weight-bold text-primary"><?php echo $this->title; ?></h6>
            </a>
            <!-- Card Content - Collapse -->
            <div class="collapse" id="<?php echo $this->id; ?>">
                <div class="card-body">
                    <?php echo $this->content; ?>
                </div>
            </div>
        </div>
<?php }
}
